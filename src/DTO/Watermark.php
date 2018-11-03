<?php
declare(strict_types=1);

namespace WernerDweight\ImageManagerBundle\DTO;

class Watermark
{
    /** @var string */
    private $file;

    /** @var string */
    private $size;

    /** @var Position */
    private $position;

    /**
     * Watermark constructor.
     * @param array $watermarkData
     */
    public function __construct(array $watermarkData)
    {
        $this->file = $watermarkData['file'];
        $this->size = $watermarkData['size'];
        $this->position = new Position($watermarkData['position']);
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'size' => $this->size,
            'position' => $this->position->toArray(),
        ];
    }
}
