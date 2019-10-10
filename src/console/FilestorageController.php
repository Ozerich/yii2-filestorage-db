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

    public function actionFix()
    {
        /** @var File $items */
        $items = $this->modelClass::find()->andWhere('id=2998')->all();

        $this->log('Found ' . count($items) . ' items');

        $successCount = 0;
        $failureCount = 0;

        foreach ($items as $ind => $item) {
            $hasError = false;
            try {
                if (!FileStorage::staticPrepareThumbnails($item)) {
                    $hasError = true;
                }

            } catch (\Exception $exception) {
            }

            if ($hasError) {
                $this->log('Item ' . ($ind + 1) . ' / ' . count($items) . ' (ID ' . $item->id . ') - Failure');
                $failureCount++;
            } else {
                $this->log('Item ' . ($ind + 1) . ' / ' . count($items) . ' (ID ' . $item->id . ') - Success');
                $successCount++;
            }
        }

        $this->log('Finish. Success: ' . $successCount . ', Failure: ' . $failureCount);
    }
}