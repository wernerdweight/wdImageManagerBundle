<?php
declare(strict_types=1);

namespace WernerDweight\ImageManagerBundle\DTO;

class Position
{
    /** @var int */
    private $top;

    /** @var int */
    private $left;

    /**
     * Position constructor.
     * @param array $positionData
     */
    public function __construct(array $positionData)
    {
        $this->top = $positionData['top'];
        $this->left = $positionData['left'];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'top' => $this->top,
            'left' => $this->left,
        ];
    }
}
