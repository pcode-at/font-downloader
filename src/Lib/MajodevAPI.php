<?php

namespace PCode\GoogleFontDownloader\Lib;


use PCode\GoogleFontDownloader\Interfaces\APIInterface;
use PCode\GoogleFontDownloader\Interfaces\Service\DownloadServiceInterface;
use PCode\GoogleFontDownloader\Interfaces\Service\FontServiceInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class MajodevGoogleWebFontsHelperService
 * @package PCode\GoogleFontDownloader
 *
 * Github link: https://github.com/majodev/google-webfonts-helper
 *
 * @api https://google-webfonts-helper-production.up.railway.app/api/fonts/<ID>/?subsets=<latin>
 */
final class MajodevAPI implements APIInterface
{
    const API_URL = "google-webfonts-helper-production.up.railway.app";
    const API_PATH = "api/fonts/";

    /**
     * @var UriInterface
     */
    private $apiUrl;
    /**
     * @var FontServiceInterface $fontService
     */
    private $fontService;
    /**
     * @var DownloadServiceInterface
     */
    private $downloadService;

    /**
     * GoogleWebFontsHelperMajodevAPIService constructor.
     * @param FontServiceInterface $fontService
     * @param DownloadServiceInterface $downloadService
     * @param UriInterface $apiUrl
     */
    public function __construct(FontServiceInterface $fontService, DownloadServiceInterface $downloadService, UriInterface $apiUrl)
    {
        $this->fontService = $fontService;
        $this->downloadService = $downloadService;
        $this->apiUrl = $apiUrl;
    }

    /**
     * @param null|string $font
     * @return mixed
     */
    public function getMetadata($font)
    {
        /** @var UriInterface $url */
        $url = $this->apiUrl->withScheme('https')
                            ->withHost(MajodevAPI::API_URL)
                            ->withPath(MajodevAPI::API_PATH . $this->normalizeName($font));

        $metadata = $this->fontService->getContent($this->downloadService->sendRequest($url->__toString(), 'GET'));

        // Add subsets to the url
        $url = $this->setSubsetsUrlArguments($url, $metadata['subsets']);
        $content = $this->fontService->getContent($this->downloadService->sendRequest($url->__toString(), 'GET'));

        return $content;
    }

    /**
     * @param null|string $name
     * @return null|string
     */
    public function normalizeName($name)
    {
        if (!empty($name)) {
            return strtolower(str_replace(' ', '-', $name));
        }

        return $name;
    }

    /**
     * @param UriInterface $url
     * @param array $subsets
     * @return UriInterface
     */
    protected function setSubsetsUrlArguments($url, array $subsets)
    {
        return $url->withQuery('? subsets =' . http_build_query($subsets));
    }
}
