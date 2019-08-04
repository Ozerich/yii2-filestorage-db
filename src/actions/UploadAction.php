<?php

namespace ozerich\filestorage\actions;

use ozerich\filestorage\FileStorage;
use yii\base\Action;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends Action
{
    public $component = 'media';

    public $format = 'file';

    public $field = 'file';

    public $responseFormatter = null;

    public $scenario;

    /**
     * @return FileStorage
     */
    private function storage()
    {
        return \Yii::$app->{$this->component};
    }

    private function error($message)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->response->setStatusCode(400);

        \Yii::$app->response->content = json_encode([
            'success' => false,
            'error' => $message
        ]);

        \Yii::$app->response->send();
        \Yii::$app->end();
    }

    private function getScenario()
    {
        return empty($this->scenario) ? \Yii::$app->request->getQueryParam('scenario') : $this->scenario;
    }

    private function getModelFromFileRequest()
    {
        $file = UploadedFile::getInstanceByName($this->field);

        if (!$file) {
            return $this->error('Файл не найден');
        }

        $model = $this->storage()->createFileFromUploadedFile($file, $this->getScenario());

        return $model;
    }

    private function getModelFromBase64Request()
    {
        $file = \Yii::$app->request->post($this->field);
        $filename = \Yii::$app->request->post('filename');

        if (!$file) {
            return $this->error('Файл не найден');
        }

        $model = $this->storage()->createFileFromBase64($file, $filename, $this->getScenario());

        return $model;
    }

    private function getModelFromRequest()
    {
        return $this->format == 'file' ? $this->getModelFromFileRequest() : $this->getModelFromBase64Request();
    }

    public function run()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $model = $this->getModelFromRequest();

        if (!$model) {
            $errors = $this->storage()->getLastErrors();
            if (!empty($errors)) {
                return $this->error('Ошибка загрузки файла: ' . array_shift($errors));
            } else {
                return $this->error('Ошибка загрузки файла: Неизвестная ошибка');
            }
        }

        if ($this->responseFormatter && is_callable($this->responseFormatter)) {
            return call_user_func($this->responseFormatter, $model);
        }

        return [
            'success' => true,
            'image' => $model->toFullJSON()
        ];
    }
}