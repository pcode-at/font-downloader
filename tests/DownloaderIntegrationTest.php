<?php

namespace PCode\GoogleFontDownloader;

use PCode\GoogleFontDownloader\Lib\FontExtension;
use PCode\GoogleFontDownloader\Lib\Models\FontDTO;

class DownloaderIntegrationTest extends FontDownloaderTestCase
{
    /** @test */
    function it_downloads_latest_font_if_specific_version_is_not_available()
    {
        $downloader = $this->getDownloader();

        /** @var FontDTO $downloadedFont */
        $downloadedFont = $downloader->download('Average', 'v1');

        $this->assertInstanceOf(FontDTO::class, $downloadedFont);

        $this->assertNotEquals('v1', $downloadedFont->getVersion());
    }

    /**
     * @test
     * @dataProvider fontsWithVersionsProvider
     * @param $fontName
     * @param $fontVersion
     */
    function it_downloads_font_with_specific_version_and_default_extension($fontName, $fontVersion)
    {
        $downloader = $this->getDownloader();

        /** @var FontDTO $downloadedFont */
        $downloadedFont = $downloader->download($fontName, $fontVersion);

        $this->assertInstanceOf(FontDTO::class, $downloadedFont);

        foreach ($downloadedFont->getVariants() as $variant) {
            $this->assertFileExists("./tests/data/{$variant->getSrc()}");
        }
    }

    /**
     * @test
     * @dataProvider fontsWithExtensionAndVersionProvider
     * @param $fontName
     * @param $fontVersion
     * @param $extension
     */
    function it_downloads_font_with_specific_extension_and_version($fontName, $fontVersion, $extension)
    {
        $downloader = $this->getDownloader();

        /** @var FontDTO $downloadedFont */
        $downloadedFont = $downloader->download($fontName, $fontVersion, $extension);

        $this->assertInstanceOf(FontDTO::class, $downloadedFont);

        foreach ($downloadedFont->getVariants() as $variant) {
            $this->assertFileExists("./tests/data/{$variant->getSrc()}");
        }
    }

    /** @test */
    function it_downloads_font_with_latest_version()
    {
        $downloader = $this->getDownloader();

        /** @var FontDTO $downloadedFont */
        $downloadedFont = $downloader->downloadLatest("Bokor", FontExtension::TTF);

        $this->assertInstanceOf(FontDTO::class, $downloadedFont);

        foreach ($downloadedFont->getVariants() as $variant) {
            $this->assertFileExists("./tests/data/{$variant->getSrc()}");
        }
    }

    /** @test */
    function it_checks_if_font_with_provided_version_exists()
    {
        $downloader = $this->getDownloader();

        $this->assertFalse($downloader->isFontAvailableForDownload("Raleway", "vXX"));

        //Checks latest version
        $this->assertTrue($downloader->isFontAvailableForDownload("Raleway"));

        $this->assertTrue($downloader->isFontAvailableForDownload("Open Sans", "v43"));
    }

    function fontsWithVersionsProvider()
    {
        return [
            "Inconsolata v16" => [
                "name" => "Inconsolata",
                "version" => "v16"
            ],
            "Open Sans v15" => [
                "name" => "Open Sans",
                "version" => "v15",
            ],
        ];
    }

    function fontsWithExtensionAndVersionProvider()
    {
        return [
            "Inconsolata v16 TTF" => [
                "name" => "Inconsolata",
                "version" => "v16",
                "extension" => FontExtension::TTF
            ],
            "Open Sans v15 WOFF" => [
                "name" => "Open Sans",
                "version" => "v15",
                "extension" => FontExtension::WOFF
            ],
        ];
    }
}
