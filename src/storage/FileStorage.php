<?php

namespace blakit\filestorage\storage;

use blakit\filestorage\structures\Thumbnail;
use yii\helpers\Url;

class FileStorage extends BaseStorage
{
    /** @var string */
    public $uploadDirPath;

    /** @var string */
    public $uploadDirUrl;

    /**
     * @param $file_hash
     * @return string
     */
    protected function getInnerDirectory($file_hash)
    {
        return implode(DIRECTORY_SEPARATOR, [
            substr($file_hash, 0, 2),
            substr($file_hash, 2, 2)
        ]);
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return string
     */
    protected function getFileName($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        return $file_hash . ($thumbnail ? '_' . $thumbnail->getFilenamePrefix() : '') . '.' . $file_ext;
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return string
     */
    public function getFullFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        return $this->uploadDirPath . $this->getFilePath($file_hash, $file_ext, $thumbnail);
    }

    /**
     * @param $file_id
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return bool
     */
    public function isFileExists($file_id, $file_ext, Thumbnail $thumbnail = null)
    {
        return is_file($this->getFilePath($file_id, $file_ext, $thumbnail));
    }

    /**
     * @param $src
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return bool
     */
    public function upload($src, $file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        $directory = $this->uploadDirPath . DIRECTORY_SEPARATOR . $this->getInnerDirectory($file_hash);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $dest = $this->getFilePath($file_hash, $file_ext, $thumbnail);

        if (is_uploaded_file($src)) {
            return @move_uploaded_file($src, $dest);
        } else {
            return @rename($src, $dest);
        }
    }

    /**
     * @param $file_id
     * @param $file_ext
     * @param $dest
     * @param Thumbnail|null $thumbnail
     * @return bool
     */
    public function download($file_id, $file_ext, $dest, Thumbnail $thumbnail = null)
    {
        return copy($this->getFilePath($file_id, $file_ext, $thumbnail), $dest);
    }

    /**
     * @param $file_id
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     */
    public function delete($file_id, $file_ext, Thumbnail $thumbnail = null)
    {
        @unlink($this->getFilePath($file_id, $file_ext, $thumbnail));
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return string
     */
    public function getFileUrl($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        return Url::to($this->getFilePath($file_hash, $file_ext, $thumbnail));
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return string
     */
    public function getFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        return DIRECTORY_SEPARATOR . $this->getInnerDirectory($file_hash) . DIRECTORY_SEPARATOR . $this->getFileName($file_hash, $file_ext, $thumbnail);
    }
}
