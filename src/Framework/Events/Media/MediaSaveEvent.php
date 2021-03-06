<?php
/**
 * Created by PhpStorm.
 * User: imanuel
 * Date: 24.08.18
 * Time: 18:50
 */

namespace Jinya\Framework\Events\Media;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaSaveEvent extends Event
{
    public const PRE_SAVE = 'MediaPreSave';

    public const POST_SAVE = 'MediaPostSave';

    /** @var string */
    private $location;

    /** @var resource|UploadedFile */
    private $file;

    /** @var string */
    private $type;

    /**
     * SaveMediaEvent constructor.
     * @param resource|UploadedFile $file
     * @param string $type
     */
    public function __construct($file, string $type)
    {
        $this->file = $file;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return resource|UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
