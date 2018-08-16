<?php

namespace blakit\filestorage\models;

use blakit\filestorage\Component;
use blakit\filestorage\helpers\TempFile;
use blakit\filestorage\services\ImageService;
use Yii;
use yii\web\IdentityInterface;

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
        if ($insert && Yii::$app->has('user') && !Yii::$app->user->isGuest && empty($this->user_id)) {
            $this->user_id = Yii::$app->user->id;
        }

        return parent::beforeSave($insert);
    }

    public function setUser(IdentityInterface $user)
    {
        $this->user_id = $user->getId();
        if (!$this->isNewRecord) {
            $this->save(false, ['user_id']);
        }
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
     * @param string|null $thumbnail_alias
     * @return string
     */
    public function getAbsolutePath($thumbnail_alias = null)
    {
        $scenario = Component::getScenario($this->scenario);

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
        $scenario = Component::getScenario($this->scenario);

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
        $scenario = Component::getScenario($this->scenario);

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
            $thumbs = [
                [
                    'id' => $this->id . '_ORIGINAL',
                    'url' => $this->getUrl()
                ]
            ];

            if ($full) {
                $thumbs[0] = array_merge($thumbs[0], [
                    'thumb' => 'ORIGINAL',
                    'width' => $this->width,
                    'height' => $this->height,
                ]);
            }

            Component::staticPrepareThumbnails($this);

            foreach ($scenario->getThumbnails() as $thumbnail) {
                if ($scenario->getStorage()->isFileExists($this->hash, $this->ext, $thumbnail) == false) {
                    continue;
                }

                $url = $scenario->getStorage()->getFileUrl($this->hash, $this->ext, $thumbnail);

                $temp = new TempFile();
                $scenario->getStorage()->download($this->hash, $this->ext, $temp->getPath(), $thumbnail);

                $item = [
                    'id' => $this->id . '_' . $thumbnail->getThumbId(),
                    'thumb' => $thumbnail->getThumbId(),
                    'url' => $url
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


}