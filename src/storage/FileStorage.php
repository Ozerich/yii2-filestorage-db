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
            mb_strtolower(mb_substr($file_hash, 0, 2)),
            mb_strtolower(mb_substr($file_hash, 2, 2))
        ]);
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @param boolean $is_2x
     * @return string
     */
    protected function getFileName($file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false)
    {
        $result = $file_hash . ($thumbnail ? '_' . $thumbnail->getFilenamePrefix() . ($is_2x ? '@2x' : '') : '') . '.' . $file_ext;

        return $result;
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @param boolean $is_2x
     * @return string
     */
    public function getAbsoluteFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false)
    {
        return $this->uploadDirPath . $this->getFilePath($file_hash, $file_ext, $thumbnail, null, $is_2x);
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
     * @param bool $is_2x
     * @return bool
     */
    public function upload($src, $file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false)
    {
        $directory = $this->uploadDirPath . DIRECTORY_SEPARATOR . $this->getInnerDirectory($file_hash);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }


        $dest = $this->getAbsoluteFilePath($file_hash, $file_ext, $thumbnail, $is_2x);

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
    public function delete($file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false)
    {
        $this->deleteAllThumbnails($file_hash);

        @unlink($this->getAbsoluteFilePath($file_hash, $file_ext, $thumbnail, $is_2x));
    }

    public function deleteAllThumbnails($file_hash)
    {
        $path = $this->uploadDirPath . DIRECTORY_SEPARATOR . $this->getInnerDirectory($file_hash);

        if (!is_dir($path)) {
            return;
        }

        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                if (mb_substr($entry, 0, mb_strlen($file_hash)) != $file_hash) {
                    continue;
                }

                $p = mb_strrpos($entry, '.');
                if ($p === false) {
                    continue;
                }

                $filename = mb_substr($entry, 0, $p);
                if ($filename != $file_hash) {
                    @unlink($path . DIRECTORY_SEPARATOR . $entry);
                }
            }
        }
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @param boolean $is_2x
     * @return string
     */
    public function getFileUrl($file_hash, $file_ext, Thumbnail $thumbnail = null, $is_2x = false)
    {
        return Url::to($this->uploadDirUrl . $this->getFilePath($file_hash, $file_ext, $thumbnail, '/', $is_2x), true);
    }

    /**
     * @param $file_hash
     * @param $file_ext
     * @param Thumbnail|null $thumbnail
     * @param $sep
     * @param bool $is_2x
     * @return string
     */
    public function getFilePath($file_hash, $file_ext, Thumbnail $thumbnail = null, $sep = null, $is_2x = false)
    {
        if ($sep == null) {
            $sep = DIRECTORY_SEPARATOR;
        }

        return $sep . $this->getInnerDirectory($file_hash) . $sep . $this->getFileName($file_hash, $file_ext, $thumbnail, $is_2x);
    }
}
