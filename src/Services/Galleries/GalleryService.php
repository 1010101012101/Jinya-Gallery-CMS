<?php
/**
 * Created by PhpStorm.
 * User: imanu
 * Date: 08.11.2017
 * Time: 17:06
 */

namespace Jinya\Services\Galleries;


use Doctrine\ORM\EntityManagerInterface;
use Jinya\Entity\Gallery;
use Jinya\Services\Base\BaseArtService;
use Jinya\Services\Labels\LabelServiceInterface;
use Jinya\Services\Media\MediaServiceInterface;
use Jinya\Services\Slug\SlugServiceInterface;

class GalleryService extends BaseArtService implements GalleryServiceInterface
{

    /** @var MediaServiceInterface */
    private $mediaService;

    /**
     * GalleryService constructor.
     * @param EntityManagerInterface $entityManager
     * @param MediaServiceInterface $mediaService
     * @param SlugServiceInterface $slugService
     * @param LabelServiceInterface $labelService
     */
    public function __construct(EntityManagerInterface $entityManager, MediaServiceInterface $mediaService, SlugServiceInterface $slugService, LabelServiceInterface $labelService)
    {
        parent::__construct($entityManager, $slugService, $labelService, Gallery::class);
        $this->mediaService = $mediaService;
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $gallery = $this->get($id);
        if ($gallery->getBackground()) {
            $this->mediaService->deleteMedia($gallery->getBackground());
        }
        parent::delete($id);
    }

    /**
     * @inheritdoc
     */
    public function get($idOrSlug): ?Gallery
    {
        return parent::get($idOrSlug);
    }

    /**
     * @inheritdoc
     */
    public function getBySlug(string $slug): ?Gallery
    {
        return parent::getBySlug($slug);
    }


    /**
     * @inheritdoc
     */
    public function getById(int $id): ?Gallery
    {
        return parent::getById($id);
    }

    /**
     * @inheritdoc
     */
    public function saveOrUpdate(Gallery $gallery): Gallery
    {
        $background = $gallery->getBackgroundResource();
        if ($background !== null) {
            $gallery->setBackground($this->mediaService->saveMedia($background, MediaServiceInterface::GALLERY_BACKGROUND));
        }

        return parent::save($gallery);
    }

}