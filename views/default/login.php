<div class="row-fluid">

	<div class="span6 offset3" style="text-align: center">
		<?php
		$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
			'id' => 'login-form',
			'type' => 'inline',
			'htmlOptions' => array('class'=>'well')
		));?>
		<p class="pull-left"><?php echo  Yii::t('ses', 'Please enter your password');?></p>
		<?php echo $form->passwordFieldRow($model, 'password', array('placeholder'=>'password', 'prepend'=>'<i class="icon-lock"></i>'));?>
		<?php  $this->widget('bootstrap.widgets.TbButton', array('buttonType'=>'submit', 'label'=> Yii::t('ses', 'Log in')));?>
		<?php $this->endWidget();?>
	</div>
</div>