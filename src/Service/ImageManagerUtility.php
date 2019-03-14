<?php
declare(strict_types=1);

namespace WernerDweight\ImageManagerBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use WernerDweight\ImageManager\Image\ProcessedImageBag;
use WernerDweight\ImageManager\Manager\ImageManager;
use WernerDweight\ImageManagerBundle\DTO\Version;

class ImageManagerUtility
{
    /** @var string */
    private $uploadRoot;

    /** @var string */
    private $uploadPath;

    /** @var string */
    private $secret;

    /** @var bool */
    private $autorotate;

    /** @var array */
    private $versionsConfiguration = [];

    /** @var Version[]|null */
    private $versions;

    /** @var ImageManager|null */
    private $imageManager;

    /**
     * ImageManagerUtility constructor.
     *
     * @param array  $versions
     * @param string $uploadRoot
     * @param string $uploadPath
     * @param string $secret
     * @param bool   $autorotate
     */
    public function __construct(
        array $versions,
        string $uploadRoot,
        string $uploadPath,
        string $secret,
        bool $autorotate
    ) {
        $this->versionsConfiguration = $versions;
        $this->uploadRoot = $uploadRoot;
        $this->uploadPath = $uploadPath;
        $this->secret = $secret;
        $this->autorotate = $autorotate;
    }

    /**
     * @return Version[]
     */
    private function prepareVersions(): array
    {
        $versions = [];
        /** @var array $versionData */
        foreach ($this->versionsConfiguration as $versionName => $versionData) {
            $versions[$versionName] = new Version($versionName, $versionData);
        }
        return $versions;
    }

    /**
     * @return ImageManager
     */
    private function getImageManager(): ImageManager
    {
        if (null === $this->imageManager) {
            $this->imageManager = new ImageManager($this->secret, $this->autorotate);
        }
        return $this->imageManager;
    }

    /**
     * @return Version[]
     */
    private function getVersions(): array
    {
        if (null === $this->versions) {
            $this->versions = $this->prepareVersions();
        }
        return $this->versions;
    }

    /**
     * @param ProcessedImageBag $processedImageBag
     * @param string            $assetPath
     * @param string            $customPath
     * @param string            $destinationFilename
     *
     * @return ImageManagerUtility
     */
    private function createVersions(
        ProcessedImageBag $processedImageBag,
        string $assetPath,
        string $customPath,
        string $destinationFilename
    ): self {
        $imageManager = $this->getImageManager();

        foreach ($this->getVersions() as $version) {
            // load image data from file as resource data had changed
            $imageManager->loadImage($assetPath);
            // if resize dimensions are specified resize image
            if (true === $version->shouldBeResized()) {
                if (true === $version->isSmallerThan($processedImageBag)) {
                    // if resize dimensions are smaller (or equal) than original dimensions use resize dimensions
                    $imageManager->resize($version->getWidth(), $version->getHeight(), $version->shouldBeCropped());
                } elseif (true === $version->shouldBeCropped()) {
                    // if resize dimensions are larger than original dimensions and crop is set use original dimensions and adjust their ratio to fit the resize dimensions ratio
                    $resizeRatio = $version->getWidth() / $version->getHeight();
                    $originalRatio = $processedImageBag->getOriginalWidth() / $processedImageBag->getOriginalHeight();
                    // if resize dimensions are taller crop original width
                    $newWidth = (int)($processedImageBag->getOriginalWidth() * ($resizeRatio / $originalRatio));
                    $newHeight = $processedImageBag->getOriginalHeight();
                    if ($resizeRatio > $originalRatio) {
                        // if resize dimensions are wider crop original height
                        $newWidth = $processedImageBag->getOriginalWidth();
                        $newHeight = (int)($processedImageBag->getOriginalHeight() * ($originalRatio / $resizeRatio));
                    }
                    $imageManager->crop($newWidth, $newHeight);
                }
                // if resize dimensions are larger and crop is not set take no action just save the image as is (in order to prevent upscaling)
            }
            // if version is set to be encrypted encrypt it
            if (true === $version->shouldBeEncrypted()) {
                $imageManager->encrypt();
            }
            $watermark = $version->getWatermark();
            // if version should be watermarked create watermark
            if (null !== $watermark && true === is_file($watermark->getFile())) {
                $imageManager->addWatermark($watermark->toArray());
            }
            // save the newly created image version to its destination
            $imageManager->saveImage(
                $this->uploadPath . $customPath . '/' . $version->getName() . '/',
                $destinationFilename,
                $version->getType(),
                $version->getQuality()
            );
        }

        return $this;
    }

    /**
     * @param string $assetPath
     *
     * @return ImageManagerUtility
     */
    private function unlinkOriginalFile(string $assetPath): self
    {
        unlink($assetPath);
        return $this;
    }

    /**
     * @param string $filename
     * @param string $extension
     * @param string $customPath
     *
     * @return string
     */
    private function createUniqueFilename(string $filename, string $extension, string $customPath): string
    {
        // chceck that this file does not yet exist
        $uniquePart = '';       // string to be appended if filename not unique
        $counter = 0;           // unique title iteration counter

        // check for each image version
        foreach ($this->getVersions() as $version) {
            // while file exists iterate counter to be appended to filename (filename -> filename-1 -> filename-2 -> ...)
            $path = $this->uploadRoot . '/' . $this->uploadPath . $customPath . '/' . $version->getName() . '/' . $filename;
            while (file_exists($path . $uniquePart . '.' . ($version->getType() ?: $extension))) {
                $uniquePart = '-' . ++$counter;
            }
        }

        // append unique string (empty if no conflict)
        return $filename . $uniquePart;
    }

    /**
     * @param UploadedFile $photoFile
     * @param string       $destinationFilename
     * @param string       $customPath
     *
     * @return ProcessedImageBag
     */
    public function processImage(
        UploadedFile $photoFile,
        string $destinationFilename,
        string $customPath = ''
    ): ProcessedImageBag {
        $extension = (string)$photoFile->guessExtension();
        $destinationFilename = $this->createUniqueFilename($destinationFilename, $extension, $customPath);
        $assetPath = $this->uploadPath . $customPath . '/' . $destinationFilename . '.' . $extension;

        // move file to temporary destination
        $photoFile->move(
            $this->uploadRoot . '/' . $this->uploadPath . $customPath,
            $destinationFilename . '.' . $extension
        );

        $processedImageBag = new ProcessedImageBag(
            $assetPath,
            (string)$photoFile->getClientOriginalName(),
            $this->autorotate
        );
        // create versions according to the configuration
        $this->createVersions($processedImageBag, $assetPath, $customPath, $destinationFilename);
        // delete original file as we won't need it anymore
        $this->unlinkOriginalFile($assetPath);

        // return bag of data helpful for persisting image info
        return $processedImageBag;
    }
}
