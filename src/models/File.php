<?php

namespace ozerich\filestorage\models;

use ozerich\filestorage\FileStorage;
use ozerich\filestorage\helpers\TempFile;
use ozerich\filestorage\services\ImageService;
use Yii;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property integer $id
 * @property string $scenario
 * @property string $hash
 * @property string $name
 * @property string $ext
 * @property integer $size
 * @property string $thumbnail
 * @property string $mime
 * @property string $width
 * @property string $height
 * @property integer $created_at
 * @property integer $user_id
 *
 */
class File extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%files}}';
    }


    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        try {
            if ($insert && Yii::$app->has('user') && !Yii::$app->user->isGuest) {
                $this->user_id = Yii::$app->user->id;
            }
        } catch (InvalidConfigException $exception) {

        }

        return parent::beforeSave($insert);
    }

    /**
     * @param string|null $thumbnail_alias
     * @param boolean $is_2x
     * @param boolean $is_webp
     * @return string
     */
    public function getUrl($thumbnail_alias = null, $is_2x = false, $is_webp = false)
    {
        if ($is_webp && !function_exists('imagewebp')) {
            return null;
        }

        $scenario = FileStorage::getScenario($this->scenario);

        if ($thumbnail_alias && $scenario->hasThumnbails()) {
            $thumbnail = $scenario->getThumbnailByAlias($thumbnail_alias);

            if ($thumbnail) {
                FileStorage::staticPrepareThumbnails($this, $thumbnail);
                return $scenario->getStorage()->getFileUrl($this->hash, $is_webp ? 'webp' : $this->ext, $thumbnail, $is_2x);
            }
        }

        return $scenario->getStorage()->getFileUrl($this->hash, $this->ext);
    }

    /**
     * @param string|null $thumbnail_alias
     * @return string
     */
    public function getPath($thumbnail_alias = null)
    {
        $scenario = FileStorage::getScenario($this->scenario);

        if ($thumbnail_alias) {
            $thumbnail = $scenario->getThumbnailByAlias($thumbnail_alias);
            if ($thumbnail) {
                return $scenario->getStorage()->getFilePath($this->hash, $this->ext, $thumbnail);
            }
        }

        return $scenario->getStorage()->getFilePath($this->hash, $this->ext);
    }

    /**
     * @param string|null $thumbnail_alias
     * @return string
     */
    public function getAbsolutePath($thumbnail_alias = null)
    {
        $scenario = FileStorage::getScenario($this->scenario);

        if ($thumbnail_alias) {
            $thumbnail = $scenario->getThumbnailByAlias($thumbnail_alias);
            if ($thumbnail) {
                return $scenario->getStorage()->getFilePath($this->hash, $this->ext, $thumbnail);
            }
        }

        return $scenario->getStorage()->getAbsoluteFilePath($this->hash, $this->ext);
    }

    /**
     * @param string|null $thumbnail_alias
     * @return string
     */
    public function getFileContent($thumbnail_alias = null)
    {
        $scenario = FileStorage::getScenario($this->scenario);

        if ($thumbnail_alias) {
            $thumbnail = $scenario->getThumbnailByAlias($thumbnail_alias);
            if ($thumbnail) {
                return $scenario->getStorage()->getFileContent($this->hash, $this->ext, $thumbnail);
            }
        }

        return $scenario->getStorage()->getFileContent($this->hash, $this->ext);
    }

    private function prepareJSON($full = false)
    {
        $scenario = FileStorage::getScenario($this->scenario);

        $result = [
            'id' => $this->id,
            'url' => $this->getUrl()
        ];

        if ($full) {
            $result = array_merge($result, [
                'name' => $this->name,
                'ext' => $this->ext,
                'mime' => $this->mime,
                'size' => $this->size,
            ]);
        }

        if ($scenario->hasThumnbails()) {
            FileStorage::staticPrepareThumbnails($this);

            $thumbs = [
                [
                    'id' => $this->id . '_ORIGINAL',
                    'url' => $this->getUrl(),
                    'url@2x' => null
                ]
            ];

            if ($full) {
                $thumbs[0] = array_merge($thumbs[0], [
                    'thumb' => 'ORIGINAL',
                    'width' => $this->width,
                    'height' => $this->height,
                ]);
            }

            foreach ($scenario->getThumbnails() as $thumbnail) {
                if ($scenario->getStorage()->isFileExists($this->hash, $this->ext, $thumbnail) == false) {
                    continue;
                }

                $url = $scenario->getStorage()->getFileUrl($this->hash, $this->ext, $thumbnail);
                $url2x = $thumbnail->is2xSupport() ? $scenario->getStorage()->getFileUrl($this->hash, $this->ext, $thumbnail, true) : null;
                $url_webp = $thumbnail->isWebpSupport() ? $scenario->getStorage()->getFileUrl($this->hash, 'webp', $thumbnail, false) : null;
                $url_webp2x = $thumbnail->isWebpSupport() ? $scenario->getStorage()->getFileUrl($this->hash, 'webp', $thumbnail, true) : null;

                $temp = new TempFile();
                $scenario->getStorage()->download($this->hash, $this->ext, $temp->getPath(), $thumbnail);

                $item = [
                    'id' => $this->id . '_' . $thumbnail->getThumbId(),
                    'thumb' => $thumbnail->getThumbId(),
                    'url' => $url,
                    'url@2x' => null,
                    'url_webp' => $url_webp,
                    'url_webp@2x' => $url_webp2x
                ];

                if ($full) {
                    $image_info = ImageService::getImageInfo($temp->getPath());

                    $item = array_merge($item, [
                        'width' => $image_info['width'],
                        'height' => $image_info['height'],
                    ]);
                }

                $thumbs[] = $item;
            }

            $result['thumbnails'] = $thumbs;
        }

        return $result;
    }

    /**
     * Full file info in JSON format
     * @return array
     */
    public function toJson()
    {
        return $this->prepareJSON(false);
    }

    /**
     * Full file info in JSON format
     * @return array
     */
    public function toFullJSON()
    {
        return $this->prepareJSON(true);
    }

    /**
     * @param array $thumbnails
     * @return array
     */
    public function getUrlsJSON($thumbnails)
    {
        $scenario = FileStorage::getScenario($this->scenario);
        $thumbnails = is_array($thumbnails) ? $thumbnails : func_get_args();

        if (array_keys($thumbnails) !== range(0, count($thumbnails) - 1)) {
            $thumbnailsArray = $thumbnails;
        } else {
            $thumbnailsArray = [];
            foreach ($thumbnails as $item) {
                $thumbnailsArray[$item] = $item;
            }
        }

        $result = [];
        foreach ($thumbnailsArray as $resultAlias => $thumbnailAlias) {
            $thumbnailModel = $scenario->getThumbnailByAlias($thumbnailAlias);

            if (!$thumbnailModel) {
                continue;
            }

            $result[$resultAlias] = $this->getUrl($thumbnailAlias);
            if ($thumbnailModel->is2xSupport()) {
                $result[$resultAlias . '@2x'] = $this->getUrl($thumbnailAlias, true);
            }

            if ($thumbnailModel->isWebpSupport()) {
                $result[$resultAlias . '_webp'] = $this->getUrl($thumbnailAlias, false, true);
                if ($thumbnailModel->is2xSupport()) {
                    $result[$resultAlias . '@2x_webp'] = $this->getUrl($thumbnailAlias, true, true);
                }
            }
        }

        return $result;
    }
}