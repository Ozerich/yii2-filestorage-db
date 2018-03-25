<?php

namespace blakit\filestorage\services;

use blakit\filestorage\models\File;
use blakit\filestorage\structures\Scenario;
use blakit\filestorage\structures\Thumbnail;
use blakit\filestorage\helpers\TempFile;

class ImageService
{
    /**
     * @param File $image
     * @param Scenario $scenario
     */
    public static function prepareThumbnails(File $image, Scenario $scenario)
    {
        if ($scenario->getStorage()->isFileExists($image->hash, $image->ext) == false) {
            return;
        }

        $temp_file = new TempFile();
        $scenario->getStorage()->download($image->hash, $image->ext, $temp_file->getPath());

        $thumbnails = $scenario->getThumbnails();
        foreach ($thumbnails as $thumbnail) {
            if ($scenario->getStorage()->isFileExists($image->hash, $image->ext, $thumbnail)) {
                continue;
            }

            $temp_thumbnail = new TempFile();
            self::prepareThumbnailBySize($temp_file->getPath(), $thumbnail, $temp_thumbnail->getPath());

            $scenario->getStorage()->upload($temp_thumbnail->getPath(), $image->hash, $image->ext, $thumbnail);
        }
    }

    /**
     * @param $file_path
     * @param Thumbnail $thumbnail
     * @param $thumbnail_file_path
     */
    private static function prepareThumbnailBySize($file_path, Thumbnail $thumbnail, $thumbnail_file_path)
    {
        $image = new ResizeImage($file_path);

        if (!$image) {
            return;
        }

        if ($thumbnail->getCrop()) {
            $image->resizeImage($thumbnail->getWidth(), $thumbnail->getHeight(), 'crop');
        } else if ($thumbnail->getExact()) {
            $image->resizeImage($thumbnail->getWidth(), $thumbnail->getHeight(), 'exact');
        } else {
            $image->resizeImage($thumbnail->getWidth(), $thumbnail->getHeight(), 'auto');
        }

        $image->saveImage($thumbnail_file_path);
    }

    /**
     * @param $filepath
     * @return array|null
     */
    public static function getImageInfo($filepath)
    {
        if (!is_file($filepath)) {
            return null;
        }

        $thumbnail = getimagesize($filepath);

        return [
            'size' => filesize($filepath),
            'width' => $thumbnail[0],
            'height' => $thumbnail[1],
            'mime' => $thumbnail['mime']
        ];
    }
}