<?php

namespace blakit\filestorage\storage;

use blakit\filestorage\structures\Thumbnail;

abstract class BaseStorage
{
    /** @var boolean */
    public $saveOriginalFilename = false;

    public function __construct($config)
    {
        foreach ($config as $param => $value) {
            if (!in_array($param, ['class', 'type'])) {
                $this->$param = $value;
            }
        }
    }

    abstract function isFileExists($file_hash, $file_ext, $file_name, Thumbnail $thumbnail = null);

    abstract function upload($src, $file_hash, $file_ext, $file_name, Thumbnail $thumbnail = null);

    abstract function download($file_hash, $file_ext, $dest, Thumbnail $thumbnail = null);

    abstract function delete($file_hash, $file_ext, $file_name, Thumbnail $thumbnail = null);

    abstract function getFileUrl($file_hash, $file_ext, $file_name, Thumbnail $thumbnail = null);

    abstract function getFilePath($file_hash, $file_ext, $file_name, Thumbnail $thumbnail = null);

    abstract function getAbsoluteFilePath($file_hash, $file_ext, $file_name, Thumbnail $thumbnail = null);

    abstract function getFileContent($file_hash, $file_ext, $file_name, Thumbnail $thumbnail = null);
}
