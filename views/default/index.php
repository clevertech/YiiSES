<?php
$this->breadcrumbs = array(
	$this->module->id,
	'Packages'
);
?>
<div class="alert alert-info">
	<a class="close" data-dismiss="alert" href="#">×</a>
	<h4 class="alert-heading">Clevertech Booster</h4>
	<?php echo Yii::t('ses', 'Clevertech proudly offers you a selected group of extensions that are proven to be highly useful for ourselves.');?>
</div>
<?php
$this->beginWidget('bootstrap.widgets.TbHeroUnit', array(
	'heading' =>  Yii::t('ses', 'Amazon SES Bulk Mailer'),
)); ?>

<p><?php echo  Yii::t('ses', 'Amazon SES Bulk Mailer Module, an easy way to integrate Amazon Simple Email Services to your Yii applications.');?></p>
<p><?php $this->widget('bootstrap.widgets.TbButton', array(
	'type' => 'primary',
	'size' => 'large',
	'label' =>  Yii::t('ses', 'Get Started'),
	'url' => array('campaign/manage')
)); ?></p>

<?php $this->endWidget(); ?>
