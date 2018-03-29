<?php

namespace blakit\filestorage\models;

use blakit\filestorage\structures\Scenario;
use Yii;
use blakit\filestorage\Component;
use blakit\filestorage\services\ImageService;
use blakit\filestorage\helpers\TempFile;

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
        if ($insert && Yii::$app->has('user') && !Yii::$app->user->isGuest) {
            $this->user_id = Yii::$app->user->id;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param string|null $thumbnail_alias
     * @return string
     */
    public function getUrl($thumbnail_alias = null)
    {
        $scenario = Component::getScenario($this->scenario);

        if ($thumbnail_alias) {
            $thumbnail = $scenario->getThumbnailByAlias($thumbnail_alias);
            if ($thumbnail) {
                return $scenario->getStorage()->getFileUrl($this->hash, $this->ext, $thumbnail);
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
        $scenario = Component::getScenario($this->scenario);

        if ($thumbnail_alias) {
            $thumbnail = $scenario->getThumbnailByAlias($thumbnail_alias);
            if ($thumbnail) {
                return $scenario->getStorage()->getFilePath($this->hash, $this->ext, $thumbnail);
            }
        }

        return $scenario->getStorage()->getFilePath($this->hash, $this->ext);
    }

    /**
     * @return Scenario
     */
    public function getScenario()
    {
        return Component::getScenario($this->scenario);
    }

    /**
     * Full file info in JSON format
     * @return array
     */
    public function toJSON()
    {
        $scenario = Component::getScenario($this->scenario);

        $result = [
            'id' => $this->id,
            'url' => $this->getUrl(),
            'name' => $this->name,
            'ext' => $this->ext,
            'mime' => $this->mime,
            'size' => $this->size,
        ];

        if ($scenario->hasThumnbails()) {
            $thumbs = [
                [
                    'id' => $this->id . '_ORIGINAL',
                    'thumb' => 'ORIGINAL',
                    'width' => $this->width,
                    'height' => $this->height,
                    'url' => $this->getUrl()
                ]
            ];

            Component::staticPrepareThumbnails($this);

            foreach ($scenario->getThumbnails() as $thumbnail) {
                if ($scenario->getStorage()->isFileExists($this->hash, $this->ext, $thumbnail) == false) {
                    continue;
                }

                $url = $scenario->getStorage()->getFileUrl($this->hash, $this->ext, $thumbnail);

                $temp = new TempFile();
                $scenario->getStorage()->download($this->hash, $this->ext, $temp->getPath(), $thumbnail);
                $image_info = ImageService::getImageInfo($temp->getPath());

                $thumbs[] = [
                    'id' => $this->id . '_' . $thumbnail->getThumbId(),
                    'thumb' => $thumbnail->getThumbId(),
                    'width' => $image_info['width'],
                    'height' => $image_info['height'],
                    'url' => $url
                ];
            }

            $result['thumbnails'] = $thumbs;
        }

        return $result;
    }

}