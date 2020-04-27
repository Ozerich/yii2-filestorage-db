<?php

namespace ozerich\filestorage\console;

use ozerich\filestorage\FileStorage;
use ozerich\filestorage\models\File;
use yii\console\Controller;

class FilestorageController extends Controller
{
    protected $modelClass = 'ozerich\filestorage\models\File';

    private function log($message)
    {
        echo date('d.m.Y H:i:s') . ' - ' . $message . "\n";
    }

    public function actionDeleteThumbnails()
    {
        $className = $this->modelClass;

        /** @var File[] $items */
        $items = $className::find()->all();

        $this->log('Found ' . count($items) . ' items');

        foreach ($items as $ind => $item) {
            FileStorage::staticDeleteThumbnails($item, null);

            $this->log('Item ' . ($ind + 1) . ' / ' . count($items) . ' (ID ' . $item->id . ') - Success');
        }
    }

    public function actionFixThumbnails($id = null)
    {
        $className = $this->modelClass;

        /** @var File[] $items */
        $items = $id ? [$className::findOne($id)] : $className::find()->all();

        $this->log('Found ' . count($items) . ' items');

        $successCount = 0;
        $failureCount = 0;

        foreach ($items as $ind => $item) {
            if(!$item){
                $this->log('Item ' . ($ind + 1) . ' / ' . count($items) . ' (ID ' . $item->id . ') - Failure (Item Not Found)');
                $failureCount++;
                continue;
            }

            $hasError = false;
            try {
                if (!FileStorage::staticPrepareThumbnails($item, null, true)) {
                    $hasError = true;
                }

            } catch (\Exception $exception) {
            }

            if ($hasError) {
                $this->log('Item ' . ($ind + 1) . ' / ' . count($items) . ' (ID ' . $item->id . ') - Failure (File Not Found)');
                $failureCount++;
            } else {
                $this->log('Item ' . ($ind + 1) . ' / ' . count($items) . ' (ID ' . $item->id . ') - Success');
                $successCount++;
            }
        }

        $this->log('Finish. Success: ' . $successCount . ', Failure: ' . $failureCount);
    }
}