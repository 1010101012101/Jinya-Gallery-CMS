<?php
/**
 * Created by PhpStorm.
 * User: imanuel
 * Date: 01.06.18
 * Time: 19:04
 */

namespace Jinya\Services\Videos;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Jinya\Entity\Video\YoutubeVideo;
use Jinya\Exceptions\EmptySlugException;
use Jinya\Framework\Events\Common\CountEvent;
use Jinya\Framework\Events\Common\ListEvent;
use Jinya\Framework\Events\Videos\YoutubeVideoEvent;
use Jinya\Services\Base\BaseSlugEntityService;
use Jinya\Services\Slug\SlugServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class YoutubeVideoService implements YoutubeVideoServiceInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var BaseSlugEntityService */
    private $baseService;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * YoutubeVideoService constructor.
     * @param EntityManagerInterface $entityManager
     * @param SlugServiceInterface $slugService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManagerInterface $entityManager, SlugServiceInterface $slugService, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->baseService = new BaseSlugEntityService($entityManager, $slugService, YoutubeVideo::class);
    }

    /**
     * Gets a list of videos in the given range and filtered by the given keyword
     *
     * @param int $offset
     * @param int $count
     * @param string $keyword
     * @return YoutubeVideo[]
     */
    public function getAll(int $offset = 0, int $count = 10, string $keyword = ''): array
    {
        $this->eventDispatcher->dispatch(ListEvent::YOUTUBE_VIDEOS_PRE_GET_ALL, new ListEvent($offset, $count, $keyword, []));

        $items = $this->createQueryBuilder($keyword)
            ->select('youtube_video')
            ->setMaxResults($count)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();

        $this->eventDispatcher->dispatch(ListEvent::YOUTUBE_VIDEOS_POST_GET_ALL, new ListEvent($offset, $count, $keyword, $items));

        return $items;
    }

    /**
     * @param string $keyword
     * @return QueryBuilder
     */
    private function createQueryBuilder(string $keyword): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->from(YoutubeVideo::class, 'youtube_video')
            ->where('youtube_video.name LIKE :keyword')
            ->andWhere('youtube_video.description LIKE :keyword')
            ->setParameter('keyword', "%$keyword%");
    }

    /**
     * Counts all videos filtered by the given keyword
     *
     * @param string $keyword
     * @return int
     * @throws NonUniqueResultException
     */
    public function countAll(string $keyword = ''): int
    {
        $this->eventDispatcher->dispatch(CountEvent::YOUTUBE_VIDEOS_PRE_COUNT, new CountEvent($keyword, -1));

        $count = $this->createQueryBuilder($keyword)
            ->select('count(youtube_video)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->eventDispatcher->dispatch(CountEvent::YOUTUBE_VIDEOS_POST_COUNT, new CountEvent($keyword, $count));

        return $count;
    }

    /**
     * Saves or update the given video
     *
     * @param YoutubeVideo $video
     * @return YoutubeVideo
     * @throws EmptySlugException
     */
    public function saveOrUpdate(YoutubeVideo $video): YoutubeVideo
    {
        $pre = $this->eventDispatcher->dispatch(YoutubeVideoEvent::PRE_SAVE, new YoutubeVideoEvent($video, $video->getSlug()));

        if (!$pre->isCancel()) {
            $this->baseService->saveOrUpdate($video);
            $this->eventDispatcher->dispatch(YoutubeVideoEvent::POST_SAVE, new YoutubeVideoEvent($video, $video->getSlug()));
        }

        return $video;
    }

    /**
     * Deletes the given video
     *
     * @param YoutubeVideo $video
     */
    public function delete(YoutubeVideo $video): void
    {
        $pre = $this->eventDispatcher->dispatch(YoutubeVideoEvent::PRE_DELETE, new YoutubeVideoEvent($video, $video->getSlug()));

        if (!$pre->isCancel()) {
            $this->baseService->delete($video);
            $this->eventDispatcher->dispatch(YoutubeVideoEvent::POST_DELETE, new YoutubeVideoEvent($video, $video->getSlug()));
        }
    }

    /**
     * Gets the artwork by slug or id
     *
     * @param string $slug
     * @return YoutubeVideo
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function get(string $slug): ?YoutubeVideo
    {
        $this->eventDispatcher->dispatch(YoutubeVideoEvent::PRE_GET, new YoutubeVideoEvent(null, $slug));

        $video = $this->baseService->get($slug);
        $this->eventDispatcher->dispatch(YoutubeVideoEvent::POST_GET, new YoutubeVideoEvent($video, $slug));

        return $video;
    }
}
