<?php
/**
 * Created by PhpStorm.
 * User: imanu
 * Date: 07.01.2018
 * Time: 20:10
 */

namespace Jinya\Services\Labels;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Jinya\Entity\Artwork\Artwork;
use Jinya\Entity\Gallery\ArtGallery;
use Jinya\Entity\Label\Label;
use function array_map;

class LabelService implements LabelServiceInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * LabelService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllLabels(): array
    {
        return $this->entityManager->getRepository(Label::class)->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllLabelsWithArtworks(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->from(Artwork::class, 'artwork')
            ->select('labels')
            ->join('artwork.labels', 'labels')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllLabelsWithGalleries(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->from(ArtGallery::class, 'art_gallery')
            ->select('labels')
            ->join('art_gallery.labels', 'labels')
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteLabel(string $name): void
    {
        $this->entityManager->remove($this->getLabel($name));
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(string $name): Label
    {
        return $this->entityManager->getRepository(Label::class)->findOneBy(['name' => $name]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateLabel(Label $label): Label
    {
        $this->entityManager->flush();

        return $label;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllLabelNames(): array
    {
        return array_map('current', $this->createQueryBuilder()
            ->select('label.name')
            ->getQuery()
            ->getScalarResult());
    }

    private function createQueryBuilder()
    {
        return $this->entityManager->createQueryBuilder()->from(Label::class, 'label');
    }

    /**
     * {@inheritdoc}
     * @throws NonUniqueResultException
     */
    public function createMissingLabels(array $labels): array
    {
        $newLabels = [];
        foreach ($labels as $label) {
            $newLabel = new Label();
            $newLabel->setName($label);
            if (!$this->labelExists($newLabel)) {
                $newLabels[] = $this->addLabel($label);
            }
        }

        return $newLabels;
    }

    /**
     * @param Label $label
     * @return bool
     * @throws NonUniqueResultException
     */
    private function labelExists(Label $label): bool
    {
        return $this->createQueryBuilder()
            ->select('COUNT(label.name)')
            ->where('label.id = :id')
            ->orWhere('label.name = :name')
            ->setParameter('id', $label->getId())
            ->setParameter('name', $label->getName())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function addLabel(string $name): Label
    {
        $label = new Label();
        $label->setName($name);
        $this->entityManager->persist($label);
        $this->entityManager->flush();

        return $label;
    }

    /**
     * Renames the given label
     *
     * @param string $name
     * @param string $newName
     * @return Label
     */
    public function rename(string $name, string $newName): Label
    {
        $label = $this->getLabel($name);
        $label->setName($newName);

        $this->entityManager->flush();

        return $label;
    }
}
