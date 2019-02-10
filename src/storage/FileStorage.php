<?php

namespace ozerich\filestorage\storage;

use ozerich\filestorage\structures\Thumbnail;
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
    public function getAbsoluteFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        return $this->uploadDirPath . $this->getFilePath($file_hash, $file_ext, $thumbnail);
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return string
     */
    public function getFileContent($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        $file_path = $this->getAbsoluteFilePath($file_hash, $file_ext, $thumbnail);

        if (!is_file($file_path)) {
            return null;
        }

        $f = fopen($file_path, 'r');
        $data = fread($f, filesize($file_path));
        fclose($f);

        return $data;
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return bool
     */
    public function isFileExists($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        return is_file($this->getAbsoluteFilePath($file_hash, $file_ext, $thumbnail));
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

        $dest = $this->getAbsoluteFilePath($file_hash, $file_ext, $thumbnail);

        if (is_uploaded_file($src)) {
            return @move_uploaded_file($src, $dest);
        } else {
            return @rename($src, $dest);
        }
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param $dest
     * @param Thumbnail|null $thumbnail
     * @return bool
     */
    public function download($file_hash, $file_ext, $dest, Thumbnail $thumbnail = null)
    {
        return copy($this->getAbsoluteFilePath($file_hash, $file_ext, $thumbnail), $dest);
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     */
    public function delete($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        @unlink($this->getAbsoluteFilePath($file_hash, $file_ext, $thumbnail));
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @return string
     */
    public function getFileUrl($file_hash, $file_ext, Thumbnail $thumbnail = null)
    {
        return Url::to($this->uploadDirUrl . $this->getFilePath($file_hash, $file_ext, $thumbnail), true);
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
