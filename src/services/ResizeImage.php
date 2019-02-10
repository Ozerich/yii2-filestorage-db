<?php

namespace ozerich\filestorage\services;

class ResizeImage
{
    // *** Class variables
    private $image;
    private $width;
    private $height;
    private $imageResized;

    private $image_type;
    private $exif;

    function __construct($fileName)
    {
        $image_info = getimagesize($fileName);
        $this->image_type = $image_info[2];

        $imageType = exif_imagetype($fileName);
        if (in_array($imageType, array(IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM))) {
            if ($exifData = @exif_read_data($fileName, null, true, false)) {
                $this->exif = $exifData;
            }
        }

        // *** Open up the file
        $this->image = $this->openImage($fileName);

        if (!$this->image) {
            return;
        }

        // *** Get width and height
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    ## --------------------------------------------------------

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

    ## --------------------------------------------------------

    public function resizeImage($newWidth, $newHeight, $option = "auto")
    {
        if (!$this->image) {
            return;
        }

        // *** Get optimal width and height - based on $option
        $optionArray = $this->getDimensions($newWidth, $newHeight, $option);

        $optimalWidth = $optionArray['optimalWidth'];
        $optimalHeight = $optionArray['optimalHeight'];

        // *** Resample - create image canvas of x, y size
        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);


        // *** if option is 'crop', then crop too
        if ($option == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }
    }

    ## --------------------------------------------------------

    private function getDimensions($newWidth, $newHeight, $option)
    {
        if ($newWidth == 0) {
            $option = 'portrait';
        }
        if ($newHeight == 0) {
            $option = 'landscape';
        }

        switch ($option) {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    ## --------------------------------------------------------

    private function getSizeByFixedHeight($newHeight)
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    private function getSizeByFixedWidth($newWidth)
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    private function getSizeByAuto($newWidth, $newHeight)
    {
        if ($this->height < $this->width) // *** Image to be resized is wider (landscape)
        {
            $optimalWidth = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        } elseif ($this->height > $this->width) // *** Image to be resized is taller (portrait)
        {
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;
        } else // *** Image to be resizerd is a square
        {
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    ## --------------------------------------------------------

    private function getOptimalCrop($newWidth, $newHeight)
    {

        $heightRatio = $this->height / $newHeight;
        $widthRatio = $this->width / $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth = $this->width / $optimalRatio;

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    ## --------------------------------------------------------

    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
        // *** Find center - this will be used for the crop
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->imageResized;

        // *** Now crop from center to exact requested size
        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
    }

    public function saveImage($savePath, $imageQuality = "100")
    {
        if (!$this->image) {
            return;
        }

        switch ($this->image_type) {
            case IMAGETYPE_JPEG:
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->imageResized, $savePath, $imageQuality);
                }
                break;

            case IMAGETYPE_GIF:
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->imageResized, $savePath);
                }
                break;

            case IMAGETYPE_PNG:
                $scaleQuality = round(($imageQuality / 100) * 9);
                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->imageResized, $savePath, $invertScaleQuality);
                }
                break;
        }

        imagedestroy($this->imageResized);
    }

    private function getExifRotateAngle()
    {
        $orientation = isset($this->exif['Orientation']) ? $this->exif['Orientation'] : null;

        switch ($orientation) {
            case 3:
                return 180;
            case 6:
                return 270;
            case 8:
                return 90;
            default:
                return 0;
        }
    }

    public function fixExifOrientation()
    {
        $this->imageResized = imagerotate($this->imageResized, $this->getExifRotateAngle(), 0);
    }
}

?>