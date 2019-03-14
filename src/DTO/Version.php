<?php
declare(strict_types=1);

namespace WernerDweight\ImageManagerBundle\DTO;

use WernerDweight\ImageManager\Image\ProcessedImageBag;

class Version
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $type;

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /** @var int */
    private $quality;

    /** @var bool */
    private $crop;

    /** @var bool */
    private $encrypted;

    /** @var Watermark|null */
    private $watermark;

    /**
     * Version constructor.
     *
     * @param string $versionName
     * @param array  $versionData
     */
    public function __construct(string $versionName, array $versionData)
    {
        $this->name = $versionName;
        $this->type = $versionData['type'];
        $this->width = $versionData['width'];
        $this->height = $versionData['height'];
        $this->quality = $versionData['quality'];
        $this->crop = $versionData['crop'];
        $this->encrypted = $versionData['encrypted'];
        $this->watermark = isset($versionData['watermark']) ? new Watermark($versionData['watermark']) : null;
    }

    /**
     * @return bool
     */
    public function shouldBeResized(): bool
    {
        return $this->width > 0 && $this->height > 0;
    }

    /**
     * @param ProcessedImageBag $processedImageBag
     *
     * @return bool
     */
    public function isSmallerThan(ProcessedImageBag $processedImageBag): bool
    {
        return
            $this->width <= $processedImageBag->getOriginalWidth() ||
            $this->height <= $processedImageBag->getOriginalHeight();
    }

    /**
     * @return bool
     */
    public function shouldBeCropped(): bool
    {
        return $this->crop;
    }

    /**
     * @return bool
     */
    public function shouldBeEncrypted(): bool
    {
        return $this->encrypted;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return null|Watermark
     */
    public function getWatermark(): ?Watermark
    {
        return $this->watermark;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getQuality(): int
    {
        return $this->quality;
    }
}
