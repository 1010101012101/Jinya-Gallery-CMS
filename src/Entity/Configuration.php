<?php
/**
 * Created by PhpStorm.
 * User: imanu
 * Date: 05.01.2018
 * Time: 17:59
 */

namespace Jinya\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="configuration")
 */
class Configuration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;
    /**
     * @ORM\OneToOne(targetEntity="Jinya\Entity\Theme")
     * @ORM\JoinColumn(name="current_frontend_theme_id", referencedColumnName="id")
     * @var Theme
     */
    private $currentFrontendTheme;
    /**
     * @ORM\OneToOne(targetEntity="Jinya\Entity\Theme")
     * @ORM\JoinColumn(name="current_designer_theme_id", referencedColumnName="id")
     * @var Theme
     */
    private $currentDesignerTheme;

    /**
     * @return Theme
     */
    public function getCurrentDesignerTheme(): Theme
    {
        return $this->currentDesignerTheme;
    }

    /**
     * @param Theme $currentDesignerTheme
     */
    public function setCurrentDesignerTheme(Theme $currentDesignerTheme): void
    {
        $this->currentDesignerTheme = $currentDesignerTheme;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Theme
     */
    public function getCurrentFrontendTheme(): Theme
    {
        return $this->currentFrontendTheme;
    }

    /**
     * @param Theme $currentFrontendTheme
     */
    public function setCurrentFrontendTheme(Theme $currentFrontendTheme): void
    {
        $this->currentFrontendTheme = $currentFrontendTheme;
    }
}