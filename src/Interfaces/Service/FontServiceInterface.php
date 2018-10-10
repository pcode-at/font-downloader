<?php

namespace PCode\GoogleFontDownloader\Interfaces\Service;


use GuzzleHttp\Psr7\Response;
use PCode\GoogleFontDownloader\Lib\Models\FontDTO;

interface FontServiceInterface
{
    /**
     * @param $content
     * @return FontDTO
     */
    public function createDTO($content);

    /**
     * @param Response $response
     * @param bool $inJSONFormat
     * @return mixed
     */
    public function getContent(Response $response, bool $inJSONFormat = true);
}