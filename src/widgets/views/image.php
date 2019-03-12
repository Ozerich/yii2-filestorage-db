<?
/**
 * @var string $inputId
 * @var string $inputName
 * @var string $uploadUrl
 * @var ozerich\filestorage\models\File $model
 * @var boolean $multiple
 * @var string $value
 */
?>
<div class="widget-image">
  <input type="hidden" name="<?= $inputName ?>" value="<?= $value ?>">
  <input id="<?= $inputId ?>" <?= $multiple ? 'multiple' : '' ?> type="file">

    <? if ($multiple): ?>
        <?= $this->render('_image_multiple', [
            'models' => $models,
            'inputId' => $inputId
        ]); ?>
    <? else: ?>
        <?= $this->render('_image_single', [
            'model' => $model,
            'inputId' => $inputId
        ]); ?>
    <? endif; ?>
</div>