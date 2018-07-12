<?php

namespace blakit\filestorage\actions;

use blakit\filestorage\Component;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends Action
{
    public $component = 'media';

    public $scenario = 'default';

    public $field = 'file';

    public $onFailure = null;

    /**
     * @return Component
     */
    private function component()
    {
        return \Yii::$app->{$this->component};
    }

    public function run()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $file = UploadedFile::getInstanceByName($this->field);

        if (!$file) {
            if ($this->onFailure) {
                call_user_func($this->onFailure, null);
            } else {
                throw new BadRequestHttpException('File not found');
            }
        }

        $model = $this->component()->createFileFromUploadedFile($file, $this->scenario);

        if (!$model) {
            $errors = $this->component()->getLastErrors();
            if ($this->onFailure) {
                call_user_func($this->onFailure, $errors);
            } else {
                throw new BadRequestHttpException('Error upload file: ' . (empty($errors) ? 'Unknown error' : $errors[0]));
            }
        }

        return [
            'image' => $model->toFullJSON()
        ];
    }
}