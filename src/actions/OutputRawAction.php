<?php

namespace ozerich\filestorage\actions;

use ozerich\filestorage\models\File;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class OutputRawAction extends Action
{
    public function run($id)
    {
        \Yii::$app->response->format = Response::FORMAT_RAW;

        /** @var File $model */
        $model = File::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException(\Yii::t('errors', 'File not found'));
        }

        header('Content-Type: ' . $model->mime);

        return $model->getFileContent();
    }
}