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
    const DEFAULT_BASE_URL = "https://" . self::API_URL;
    const API_PATH = "api/fonts/";

    /**
     * @var UriInterface
     */
    private $apiUrl;
    /**
     * @var array Parsed parts (scheme, host, port, path) of the API base url
     */
    private $baseUrl;
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
     * @param null|string $baseUrl Base url of the google-webfonts-helper instance, e.g. "https://fonts.example.com"
     */
    public function __construct(FontServiceInterface $fontService, DownloadServiceInterface $downloadService, UriInterface $apiUrl, $baseUrl = null)
    {
        $this->fontService = $fontService;
        $this->downloadService = $downloadService;
        $this->apiUrl = $apiUrl;
        $this->baseUrl = $this->parseBaseUrl(empty($baseUrl) ? self::DEFAULT_BASE_URL : $baseUrl);
    }

    /**
     * @param null|string $font
     * @return mixed
     */
    public function getMetadata($font)
    {
        /** @var UriInterface $url */
        $url = $this->apiUrl->withScheme($this->baseUrl['scheme'])
                            ->withHost($this->baseUrl['host'])
                            ->withPort($this->baseUrl['port'])
                            ->withPath($this->baseUrl['path'] . MajodevAPI::API_PATH . $this->normalizeName($font));

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
     * @param string $baseUrl
     * @return array
     */
    private function parseBaseUrl($baseUrl)
    {
        $parts = parse_url($baseUrl);

        if ($parts === false || empty($parts['host'])) {
            throw new \InvalidArgumentException(sprintf('Invalid font API base url "%s".', $baseUrl));
        }

        return [
            'scheme' => isset($parts['scheme']) ? $parts['scheme'] : 'https',
            'host' => $parts['host'],
            'port' => isset($parts['port']) ? $parts['port'] : null,
            'path' => isset($parts['path']) ? rtrim($parts['path'], '/') . '/' : '/',
        ];
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
