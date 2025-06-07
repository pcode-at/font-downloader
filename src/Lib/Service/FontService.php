<?php

namespace PCode\GoogleFontDownloader\Lib\Service;


use GuzzleHttp\Psr7\Response;
use PCode\GoogleFontDownloader\Exception\UnsupportedExtension;
use PCode\GoogleFontDownloader\Interfaces\Service\FileServiceInterface;
use PCode\GoogleFontDownloader\Interfaces\Service\FontServiceInterface;
use PCode\GoogleFontDownloader\Lib\FontExtension;
use PCode\GoogleFontDownloader\Lib\Models\FontDTO;
use PCode\GoogleFontDownloader\Lib\Models\FontVariantsDTO;

class FontService implements FontServiceInterface
{
    const UNICODE_RANGE_LATIN = 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD';
    const UNICODE_RANGE_LATIN_EXT = 'U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF';
    const UNICODE_RANGE_VIETNAMESE = 'U+0102-0103, U+0110-0111, U+1EA0-1EF9, U+20AB';
    const UNICODE_RANGE_HEBREW = 'U+0590-05FF, U+20AA, U+25CC, U+FB1D-FB4F';
    const UNICODE_RANGE_GREEK = 'U+0370-03FF';
    const UNICODE_RANGE_GREEK_EXT = 'U+1F00-1FFF';
    const UNICODE_RANGE_CYRILLIC = 'U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116';
    const UNICODE_RANGE_CYRILLIC_EXT = 'U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F';
    const UNICODE_RANGE_DEVANAGARI = 'U+0900-097F, U+1CD0-1CF6, U+1CF8-1CF9, U+200C-200D, U+20A8, U+20B9, U+25CC, U+A830-A839, U+A8E0-A8FB';
    const UNICODE_RANGE_TELUGU = 'U+0951-0952, U+0964-0965, U+0C00-0C7F, U+1CDA, U+200C-200D, U+25CC';

    /**
     * @var string
     */
    private $localSrcDirectory;

    /**
     * @var FileServiceInterface
     */
    private $fileService;

    /**
     * FontService constructor.
     * @param FileServiceInterface $fileService
     * @param string $localSrcDirectory
     */
    public function __construct(FileServiceInterface $fileService, $localSrcDirectory)
    {
        $this->fileService = $fileService;
        $this->localSrcDirectory = $localSrcDirectory;
    }

    /**
     * @param $content
     * @param array $options
     * @return FontDTO
     */
    public function createDTO($content, $options = [])
    {
        return FontDTO::fromAPI(
            $content['id'],
            $content['family'],
            $content['family'],
            $this->createVariants($content, $options),
            $content['subsets'],
            $content['version'],
            $this->createUnicodeRanges($content), $content['storeID']
        );
    }

    /**
     * @param Response $response
     * @param bool $inJSONFormat
     * @return mixed
     */
    public function getContent(Response $response, $inJSONFormat = true)
    {
        if ($inJSONFormat === true) {
            return json_decode($response->getBody()->getContents(), true);
        }
        return $response->getBody()->getContents();
    }

    /**
     * @param $content
     * @param array $options
     * @return FontVariantsDTO[]
     */
    protected function createVariants($content, $options = [])
    {
        return array_map(function ($variant) use ($content, $options) {
            $extension = isset($options['extension']) ? $options['extension'] : FontExtension::DEFAULT_EXTENSION;

            $url = $this->getDownloadUrlForVariantExtension(
                $variant,
                $extension
            );

            $filePath = $this->fileService->getPath(
                $variant['id'],
                $extension,
                $content['family'],
                $content['version'],
                $content['storeID']
            );

            $localSrc = '/' . $this->localSrcDirectory . $filePath;

            return FontVariantsDTO::fromAPI(
                $variant['id'],
                $variant['fontFamily'],
                $variant['fontStyle'],
                $variant['fontWeight'],
                isset($variant['local']) ? $variant['local'] : null,
                $url,
                $localSrc,
                $filePath,
                $extension
            );
        }, $content['variants']);
    }

    /**
     * @param $content
     * @return array
     */
    protected function createUnicodeRanges($content)
    {
        $unicodeRangesWithSubsetsKeys = [
            "latin" => FontService::UNICODE_RANGE_LATIN,
            "latin-ext" => FontService::UNICODE_RANGE_LATIN_EXT,
            "vietnamese" => FontService::UNICODE_RANGE_VIETNAMESE,
            "hebrew" => FontService::UNICODE_RANGE_HEBREW,
            "cyrillic" => FontService::UNICODE_RANGE_CYRILLIC,
            "cyrillic-ext" => FontService::UNICODE_RANGE_CYRILLIC_EXT,
            "greek" => FontService::UNICODE_RANGE_GREEK,
            "greek-ext" => FontService::UNICODE_RANGE_GREEK_EXT,
            "devanagari" => FontService::UNICODE_RANGE_DEVANAGARI,
            "telugu" => FontService::UNICODE_RANGE_TELUGU
        ];

        return array_map(function ($subset) use ($unicodeRangesWithSubsetsKeys) {
            if (!empty($unicodeRangesWithSubsetsKeys[$subset])) {
                return [$subset => $unicodeRangesWithSubsetsKeys[$subset]];
            }
        }, $content['subsets']);
    }

    /**
     * @param array $variant
     * @param string $extension
     * @return string
     * @throws UnsupportedExtension
     */
    protected function getDownloadUrlForVariantExtension($variant, $extension)
    {
        if (!isset($variant[$extension])) {
            throw new UnsupportedExtension(
                "Font extension {$extension} is not available for {$$variant['fontFamily']}"
            );
        }

        return $variant[$extension];
    }
}
