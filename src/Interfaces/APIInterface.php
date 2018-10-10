<?php

namespace PCode\GoogleFontDownloader\Interfaces;


/**
 * Interface APIInterface
 * @package PCode\GoogleFontDownloader\Interfaces
 */
interface APIInterface
{
    /**
     * @param string $url
     * @return mixed
     */
    public function getMetadata(string $url);

    /**
     * @param null|string $name
     * @return mixed
     */
    public function normalizeName(?string $name);
}