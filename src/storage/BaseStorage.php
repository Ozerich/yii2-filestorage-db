<?php

namespace ozerich\filestorage\storage;

use ozerich\filestorage\structures\Thumbnail;

abstract class BaseStorage
{
    public function __construct($config)
    {
        foreach ($config as $param => $value) {
            if (!in_array($param, ['class', 'type'])) {
                $this->$param = $value;
            }
        }
    }

    abstract function isFileExists($file_hash, $file_ext, Thumbnail $thumbnail = null);

    abstract function upload($src, $file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false);

    abstract function download($file_hash, $file_ext, $dest, Thumbnail $thumbnail = null);

    abstract function delete($file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false);

    abstract function deleteAllThumbnails($file_hash);

    abstract function getFileUrl($file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false);

    abstract function getFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null);

    abstract function getAbsoluteFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null);

    abstract function getFileContent($file_hash, $file_ext, Thumbnail $thumbnail = null);
}
