<? /**
 * @var \app\models\Image[] $models
 * @var string $inputId
 * @var string $textInputsAttribute
 * @var string $textInputsName
 * @var string[] $textInputsValue
 * @var boolean $draggable
 */
?>

<div <?=$draggable ? 'id="sortable-images"' : ''?> class="widget-image__grid <?= empty($models) ? 'widget-image__grid--empty' : '' ?>">
    <div class="widget-image__grid-cell js-template" style="display: none">
        <div class="widget-image__grid-item widget-image__grid-item--loading">
            <div class="widget-image__grid-item_image">
                <img src="">
            </div>
            <? if ($textInputsName): ?>
                <div class="widget-image__grid-item_text">
                    <textarea></textarea>
                </div>
            <? endif; ?>
            <div class="widget-image__grid-item-actions">
                <button class="js-widget-image-delete"><?= Yii::t('filestorage', 'Remove') ?></button>
            </div>
        </div>
    </div>
    <? foreach ($models as $model): ?>
        <div class="widget-image__grid-cell" data-file-id="<?= $model->id ?>">
            <div class="widget-image__grid-item">
                <div class="widget-image__grid-item_image">
                    <img src="<?= $model->getUrl() ?>">
                </div>
                <? if ($textInputsName): ?>
                    <div class="widget-image__grid-item_text">
                <textarea
                        name="<?= $textInputsName ?>[<?= $model->id ?>]"><?= isset($textInputsValue[$model->id]) ? $textInputsValue[$model->id] : '' ?></textarea>
                    </div>
                <? endif; ?>
                <div class="widget-image__grid-item-actions">
                    <button class="js-widget-image-delete"><?= Yii::t('filestorage', 'Remove') ?></button>
                </div>
            </div>
        </div>
    <? endforeach; ?>
</div>

<div class="widget-image__empty">
    <label for="<?= $inputId ?>" class="widget-image__inner">
        <svg class="widget-image__icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43">
            <path
                    d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"></path>
        </svg>
        <div class="widget-image__text">
            <strong><?= Yii::t('filestorage', 'Select files') ?></strong> <?= Yii::t('filestorage', 'or drop them here') ?>
        </div>
    </label>
</div>