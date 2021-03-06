<?php
/**
 * Created by PhpStorm.
 * User: imanuel
 * Date: 27.06.18
 * Time: 09:15
 */

namespace Jinya\Services\Videos;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Jinya\Entity\Video\Video;
use Jinya\Entity\Video\YoutubeVideo;
use Jinya\Framework\Events\Common\CountEvent;
use Jinya\Framework\Events\Common\ListEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AllVideoService implements AllVideoServiceInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * AllVideoService constructor.
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Lists all videos of all types
     *
     * @param int $offset
     * @param int $count
     * @param string $keyword
     * @return array
     */
    public function getAll(int $offset = 0, int $count = 10, string $keyword = ''): array
    {
        $this->eventDispatcher->dispatch(ListEvent::ALL_VIDEOS_PRE_GET_ALL, new ListEvent($offset, $count, $keyword, []));

        $videos = $this->createQueryBuilder($keyword)
            ->select('youtube_video, video')
            ->setMaxResults($count)
            ->setFirstResult($offset)
            ->getQuery()
            ->getArrayResult();

        $this->eventDispatcher->dispatch(ListEvent::ALL_VIDEOS_POST_GET_ALL, new ListEvent($offset, $count, $keyword, $videos));

        return $videos;
    }

    private function createQueryBuilder(string $keyword): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->from(Video::class, 'video')
            ->from(YoutubeVideo::class, 'youtube_video')
            ->where('video.description LIKE :keyword')
            ->orWhere('video.name LIKE :keyword')
            ->orWhere('youtube_video.description LIKE :keyword')
            ->orWhere('youtube_video.name LIKE :keyword')
            ->setParameter('keyword', "%$keyword%");
    }

    /**
     * Counts all videos of all types
     *
     * @param string $keyword
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countAll(string $keyword = ''): int
    {
        $this->eventDispatcher->dispatch(CountEvent::ALL_VIDEOS_POST_COUNT, new CountEvent($keyword, -1));

        $count = array_sum($this->createQueryBuilder($keyword)
            ->select('count(video), count(youtube_video)')
            ->getQuery()
            ->getSingleResult());

        $this->eventDispatcher->dispatch(CountEvent::ALL_VIDEOS_PRE_COUNT, new CountEvent($keyword, $count));

        return $count;
    }
}
