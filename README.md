# Font downloader

![Build](https://github.com/pcode-at/font-downloader/workflows/Build/badge.svg?branch=master&event=push)

## Description

This package will download specified font from self hosted google fonts repository.    

### Basic usage

```PHP
use PCode\GoogleFontDownloader\Lib\DownloaderFactory;
use PCode\GoogleFontDownloader\Lib\FontExtension;

//Create using factory
$downloader = DownloaderFactory::create(__DIR__ . '/fonts');

//Optionally pass the base url of your own google-webfonts-helper instance
//Default is MajodevAPI::DEFAULT_BASE_URL
$downloader = DownloaderFactory::create(__DIR__ . '/fonts', 'https://fonts.example.com');

//Download specific version of font
$downloadedFont = $downloader->download("Open Sans", "v12");

//Download font latest version
$downloadedFont = $downloader->downloadLatest("Open Sans");

//You can also provide font extension, use constant from PCode\GoogleFontDownloader\Lib\FontExtension
//Default font extension is WOFF
$downloadedFont = $downloader->downloadLatest("Open Sans", FontExtension::WOFF22);

//Check if font can be downloaded
$downloader->isFontAvailableForDownload("Raleway");

//Check if with specific version is avalible for download
$downloader->isFontAvailableForDownload("Raleway", "v12");
```

### Symfony usage

```PHP
# - create services for Downloader
# - one for local one for export
# - inject this services in your services
# - instantiate in the controller DownloaderInterface $downloader
# - work with interface object $downloader

#Here is one example of Downloader services

sity.guzzle_http.uri:
        class: GuzzleHttp\Psr7\Uri
        
sity.font_local.flysystem_adapter:
    class: League\Flysystem\Adapter\Local
    arguments:
        - '%kernel.root_dir%/../%sity.fonts.web_path%'

sity.font_local.flysystem:
    class: League\Flysystem\Filesystem
    arguments: ['@sity.font_local.flysystem_adapter']

sity.font_export.flysystem_adapter:
    class: League\Flysystem\Adapter\Local
    arguments: ['%kernel.root_dir%/../%sity.export.dir%', true]

sity.downloader.local_file:
    class: PCode\GoogleFontDownloader\Lib\Service\FileService
    arguments:
        - '@sity.font_local.flysystem'

sity.downloader.export_file:
    class: PCode\GoogleFontDownloader\Lib\Service\FileService
    arguments:
        - '@sity.font_export.flysystem'

sity.downloader.local_font:
    class: PCode\GoogleFontDownloader\Lib\Service\FontService
    arguments:
        - '@sity.downloader.local_file'
        - '%sity.fonts.path%'

sity.downloader.export_font:
    class: PCode\GoogleFontDownloader\Lib\Service\FontService
    arguments:
        - '@sity.downloader.export_file'
        - '%sity.fonts.path%'

sity.downloader.local_download:
    class: PCode\GoogleFontDownloader\Lib\Service\DownloadService
    arguments:
        - '@sity.client.guzzle_http'
        - '@sity.downloader.local_file'
        - '@sity.downloader.local_font'

sity.downloader.export_download:
    class: PCode\GoogleFontDownloader\Lib\Service\DownloadService
    arguments:
        - '@sity.client.guzzle_http'
        - '@sity.downloader.export_file'
        - '@sity.downloader.export_font'

# The last argument (base url of the font API) is optional
sity.downloader.local_majodev_api:
    class: PCode\GoogleFontDownloader\Lib\MajodevAPI
    arguments:
        - '@sity.downloader.local_font'
        - '@sity.downloader.local_download'
        - '@sity.guzzle_http.uri'
        - '%sity.fonts.api_url%'

sity.downloader.export_majodev_api:
    class: PCode\GoogleFontDownloader\Lib\MajodevAPI
    arguments:
        - '@sity.downloader.export_font'
        - '@sity.downloader.export_download'
        - '@sity.guzzle_http.uri'
        - '%sity.fonts.api_url%'

sity.downloader.local:
    class: PCode\GoogleFontDownloader\Lib\Downloader
    arguments:
        - '@sity.downloader.local_file'
        - '@sity.downloader.local_font'
        - '@sity.downloader.local_download'
        - '@sity.downloader.local_majodev_api'

sity.downloader.export:
    class: PCode\GoogleFontDownloader\Lib\Downloader
    arguments:
        - '@sity.downloader.export_file'
        - '@sity.downloader.export_font'
        - '@sity.downloader.export_download'
        - '@sity.downloader.export_majodev_api'
        
## Use service by injecting it in the constructor

public function __construct(DownloaderInterface $downloadFonts , [...])
{
    $this->downloadFonts = $downloadFonts;
    ...        
}
```

## Available APIs
- https://google-webfonts-helper-production.up.railway.app (default)
- Any self hosted [google-webfonts-helper](https://github.com/majodev/google-webfonts-helper) instance, passed as base url
    
## Third-party libraries

 - GuzzleHttp
 - League\Flysystem
    
## Constraints

- Code is written in php 5.6


## Licence

MIT
