<?php

namespace blakit\filestorage\actions;

use blakit\filestorage\models\File;
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
            throw new NotFoundHttpException(\Yii::t('errors', 'Файл не найден'));
        }

        header('Content-Type: ' . $model->mime);

        return $model->getFileContent();
    }
}