<?php
/**
 * Created by PhpStorm.
 * User: imanu
 * Date: 09.11.2017
 * Time: 16:29
 */

namespace DataBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * Class HistoryEnabledEntity
 * @package DataBundle\Entity
 * @ORM\MappedSuperclass
 */
abstract class HistoryEnabledEntity implements JsonSerializable
{
    /**
     * @var array[]
     * @ORM\Column(type="json")
     */
    private $history;
    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $createdAt;
    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $lastUpdatedAt;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="DataBundle\Entity\User")
     */
    private $creator;
    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="DataBundle\Entity\User")
     */
    private $updatedBy;

    /**
     * @return array[]
     */
    public function getHistory(): ?array
    {
        return $this->history;
    }

    /**
     * @param array[] $history
     */
    function setHistory(array $history)
    {
        $this->history = $history;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return User
     */
    public function getCreator(): ?User
    {
        return $this->creator;
    }

    /**
     * @param User $creator
     */
    function setCreator(User $creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return DateTime
     */
    public function getLastUpdatedAt(): ?DateTime
    {
        return $this->lastUpdatedAt;
    }

    /**
     * @param DateTime $lastUpdatedAt
     */
    function setLastUpdatedAt(DateTime $lastUpdatedAt)
    {
        $this->lastUpdatedAt = $lastUpdatedAt;
    }

    /**
     * @return User
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * @param User $updatedBy
     */
    function setUpdatedBy($updatedBy)
    {
        if ($updatedBy instanceof User) {
            $this->updatedBy = $updatedBy;
        }
    }
}