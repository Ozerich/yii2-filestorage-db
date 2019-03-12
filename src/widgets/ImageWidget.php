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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
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

    public function getViewPath()
    {
        return __DIR__ . '/views';
    }

    public function run()
    {
        $inputId = BaseHtml::getInputId($this->model, $this->attribute);

        $view = $this->getView();
        ImageWidgetAsset::register($view);

        $view->registerJs("$('#" . $inputId . "').imageInput({
            uploadUrl: '" . $this->getUploadUrl() . "'
        });");

        return $this->render('image', [
            'model' => $this->getImageModel(),
            'inputName' => BaseHtml::getInputName($this->model, $this->attribute),
            'inputId' => $inputId,
        ]);
    }
}