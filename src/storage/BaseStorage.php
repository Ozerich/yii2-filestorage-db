<?php

namespace blakit\filestorage\storage;

use blakit\filestorage\structures\Thumbnail;

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

    abstract function isFileExists($file_id, $file_ext, Thumbnail $thumbnail = null);

    abstract function upload($src, $file_hash, $file_ext, Thumbnail $thumbnail = null);

    abstract function download($file_id, $file_ext, $dest, Thumbnail $thumbnail = null);

    abstract function delete($file_id, $file_ext, Thumbnail $thumbnail = null);

    abstract function getFileUrl($file_hash, $file_ext, Thumbnail $thumbnail = null);

    abstract function getFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null);
}
