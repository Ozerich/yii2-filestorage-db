<?php

namespace ozerich\filestorage\widgets;

use ozerich\filestorage\assets\ImageWidgetAsset;
use ozerich\filestorage\models\File;
use yii\helpers\BaseHtml;
use yii\widgets\InputWidget;

class ImageWidget extends InputWidget
{
    /**
     * @var string
     */
    public $scenario;

    /**
     * @var string
     */
    public $uploadUrl;

    /**
     * @var bool
     */
    public $multiple = false;


    /**
     * @return string
     */
    public function getViewPath()
    {
        return __DIR__ . '/views';
    }

    /**
     * @return mixed
     */
    private function getImageId()
    {
        return $this->model->{$this->attribute};
    }

    /**
     * @return File|null
     */
    public function getImageModel()
    {
        $image_id = $this->getImageId();

        return File::findOne($image_id);
    }

    /**
     * @return File[]
     */
    public function getImageModels()
    {
        $result = [];

        $ids = $this->getImageId();

        foreach ($ids as $id) {
            $file = File::findOne($id);
            if ($file) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getUploadUrl()
    {
        if (strrpos($this->uploadUrl, '?') !== false) {
            return $this->uploadUrl . '&scenario=' . $this->scenario;
        } else {
            return $this->uploadUrl . '?scenario=' . $this->scenario;
        }
    }

    /**
     * @return int|null|string
     */
    private function getValue()
    {
        $imageId = $this->getImageId();
        if (empty($imageId)) {
            return null;
        }

        if ($this->multiple) {
            return implode(',', array_map(function (File $file) {
                return $file->id;
            }, $this->getImageModels()));
        }

        $model = $this->getImageModel();

        return $model ? $model->id : null;
    }

    public function run()
    {
        $inputId = BaseHtml::getInputId($this->model, $this->attribute);

        $view = $this->getView();
        ImageWidgetAsset::register($view);

        $view->registerJs("$('#" . $inputId . "').imageInput({
            uploadUrl: '" . $this->getUploadUrl() . "',
            multiple: " . ($this->multiple ? 'true' : 'false') . "
        });");

        return $this->render('image', [
            'value' => $this->getValue(),
            'model' => $this->multiple ? null : $this->getImageModel(),
            'models' => $this->multiple ? $this->getImageModels() : [],
            'inputName' => BaseHtml::getInputName($this->model, $this->attribute),
            'inputId' => $inputId,
            'multiple' => $this->multiple
        ]);
    }
}