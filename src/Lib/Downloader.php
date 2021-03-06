<?php

namespace PCode\GoogleFontDownloader\Lib;

use PCode\GoogleFontDownloader\Interfaces\APIInterface;
use PCode\GoogleFontDownloader\Interfaces\DownloaderInterface;
use PCode\GoogleFontDownloader\Interfaces\Service\DownloadServiceInterface;
use PCode\GoogleFontDownloader\Interfaces\Service\FileServiceInterface;
use PCode\GoogleFontDownloader\Interfaces\Service\FontServiceInterface;
use PCode\GoogleFontDownloader\Lib\Models\FontDTO;

class Downloader implements DownloaderInterface
{
    /**
     * @var DownloadServiceInterface
     */
    private $downloadService;

    /**
     * @var FontServiceInterface
     */
    private $fontService;

    /**
     * @var FileServiceInterface
     */
    private $fileService;

    /**
     * @var APIInterface
     */
    private $api;

    /**
     * DownloadFont constructor.
     * @param FileServiceInterface $fileService
     * @param FontServiceInterface $fontService
     * @param DownloadServiceInterface $downloadService
     * @param APIInterface $api
     */
    public function __construct(
        FileServiceInterface $fileService,
        FontServiceInterface $fontService,
        DownloadServiceInterface $downloadService,
        APIInterface $api
    ) {
        $this->fileService = $fileService;
        $this->fontService = $fontService;
        $this->downloadService = $downloadService;
        $this->api = $api;
    }

    /**
     * @param string $fontName
     * @param null|string $fontVersion
     * @param string $fontExtension
     * @return FontDTO
     * @example download('Open Sans', 'v12', 'woff2')
     */
    public function download($fontName, $fontVersion = null, $fontExtension = FontExtension::WOFF22)
    {
        $isFontAvailable = $this->isFontAvailableForDownload($fontName, $fontVersion);

        if (empty($fontVersion) || !$isFontAvailable) {
            return $this->downloadLatest($fontName, $fontExtension);
        }

        return $this->downloadService->downloadFont(
            $this->getFontDTO($fontName, $fontVersion, $fontExtension)
        );
    }

    /**
     * @param string $fontName
     * @param string $fontExtension
     * @return FontDTO
     * @example download('Open Sans', 'woff2')
     */
    public function downloadLatest($fontName, $fontExtension = FontExtension::DEFAULT_EXTENSION)
    {
        return $this->downloadService->downloadFont(
            $this->getFontDTO($fontName, null, $fontExtension)
        );
    }

    /**
     * @param string $fontName
     * @param string $version
     * @return bool
     */
    public function isFontAvailableForDownload($fontName, $version = null)
    {
        $apiData = $this->api->getMetadata($fontName);
        $fontDto = $this->fontService->createDTO($apiData);

        if ($version) {
            $fontDto->changeVersion($version);
        }

        return $this->downloadService->isAvailableForDownload($fontDto->getVariants()[0]);
    }

    /**
     * @param string $font
     * @param string|null $version
     * @param string $fontExtension
     * @return FontDTO
     */
    public function getFontDTO($font, $version = null, $fontExtension = FontExtension::DEFAULT_EXTENSION)
    {
        $apiData = $this->api->getMetadata($font);
        $fontDto = $this->fontService->createDTO($apiData, [
            'extension' => $fontExtension,
        ]);

        if ($version) {
            $fontDto->changeVersion($version);
        }

        return $fontDto;
    }
}
