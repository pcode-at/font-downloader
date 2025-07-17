<?php

namespace PCode\GoogleFontDownloader\Lib\Service;

use League\Flysystem\Filesystem;
use PCode\GoogleFontDownloader\Interfaces\Service\FileServiceInterface;

class FileService implements FileServiceInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * FileService constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $path
     * @param $content
     */
    public function write($path, $content)
    {
        if (!$this->filesystem->has($path)) {
            $this->filesystem->write($path, $content);
        }
    }

    /**
     * @param string $id
     * @param string $extension
     * @param string $family
     * @param string $version
     * @param string $storeID
     * 
     * @return string
     */
    public function getPath($id, $extension, $family, $version, $storeID)
    {
        return $family.'/'.$family.'-'.$version.'-'.$storeID.'-'.$id.'.'.$extension;
    }
}
