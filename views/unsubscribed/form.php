<?php
$this->pageTitle = Yii::app()->name . ' ' . Yii::t('ses', 'Unsubscribe Form');
?>

<div id="unsubscribe-page" class="row-fluid">
	<div class="span12">
		<div class="row-fluid">
			<h3>&nbsp;</h3>
			<div class="span12">
				<img src="http://placehold.it/250&text=your%20logo" alt=""/>
			</div>
			<div class="span12">
				<h1><?php echo Yii::t('ses', 'Unsubscribe'); ?></h1>

				<div class="alert alert-info">
					<?php echo Yii::t('ses', 'If you would prefer not to receive emails from us, simply click below:') ?>
				</div>

				<form method="post">
					<div id="notifier" class="alert" style="display:none"></div>
					<div class="clearfix text">
						<?php echo CHtml::activeLabelEx($model, 'email');?>
						<div class="input">
							<?php echo CHtml::activeTextField($model, 'email', array('maxlength' => 254, 'placeholder' => 'Email'));?>
						</div>
					</div>
					<div>
						<?php echo CHtml::checkbox('unsubscribe', true) ?>
						<span class="help-block"><?php echo Yii::t('ses', 'Unsubscribe from all our emails'); ?></span>
					</div>
					<div class="actions submit">
						<button name="yt0" type="submit"
						        class="btn g-button"><?php echo Yii::t('ses', 'Unsubscribe')?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
/**
 * Translations not included in JS scripts
 */
Yii::app()->clientScript
	->registerScript('unsubscribe-form', '
	var unsubscribe = (function($){
		var $notify = $("#notifier");
		return {
			notify: function(msg,type)
			{
				type = type || "";
				$notify.hide().html("").removeClass("alert-error alert-success").addClass(type).html(msg).show();
			},
			init: function()
			{
				$("button.g-button").click(function(){
					if(!$("#unsubscribe").is(":checked"))
					{
						unsubscribe.notify("Please, to continue you must select checkbox");
						return false;
					}
					var $input = $("#' . CHtml::activeId($model, 'email') . '");
					$.ajax({
						url: "' . $this->createUrl('unsubscribed/form') . '",
						data: $input.serialize(),
						type: "post",
						dataType: "json",
						success: function(r)
						{
							if(r.status==200){
								$input.val("");
								type = "success";
							}
							unsubscribe.notify(r.message, r.type);
						}
					});
					return false;
				});
			}
		}
	})(jQuery);
	unsubscribe.init();
	', CClientScript::POS_READY);
?>
