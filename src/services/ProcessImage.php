<?php

namespace ozerich\filestorage\services;

class ProcessImage
{
    private $file_name;

    private $image_type;

    function __construct($fileName)
    {
        $this->file_name = $fileName;
        $image_info = getimagesize($fileName);
        $this->image_type = $image_info[2];
    }

    private function openImage($file)
    {
        if ($this->image_type == IMAGETYPE_JPEG) {
            return imagecreatefromjpeg($file);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            return imagecreatefromgif($file);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            return imagecreatefrompng($file);
        }

        return null;
    }

    private function saveImage($image, $filename)
    {
        if ($this->image_type == IMAGETYPE_JPEG) {
            return imagejpeg($image, $filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            return imagejpeg($image, $filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            return imagejpeg($image, $filename);
        }
    }


    public function fixOrientation()
    {
        $image = $this->openImage($this->file_name);
        if (!$image) {
            return;
        }

        try {
            $exif = exif_read_data($this->file_name);

            if (isset($exif['Orientation']) && !empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;

                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;

                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;

                    default:
                        return;
                }
            }

            $this->saveImage($image, $this->file_name);
        } catch (\Exception $exception) {

        }
    }
}

?>