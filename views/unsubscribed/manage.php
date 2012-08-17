<div id="notifier">
	<?php $this->widget('bootstrap.widgets.TbAlert', array(
		'block' => true,
		'fade' => true,
		'closeText' => '&times;'
	)); ?>
</div>

<form class="well form-inline pull-right" method="post" action="<?php echo $this->createUrl("unsubscribed/create");?>">
	<input type="text" class="span-4" placeholder="<?php echo  Yii::t('ses', 'Email');?>"
	       name="<?php echo CHtml::activeName(Unsubscribed::model(), 'email');?>"/>
	<input type="button" class="btn add-new" value="<?php echo  Yii::t('ses', 'Add new');?>"/>
	<p class="help-block"></p>
</form>
<?php
$this->pageTitle =  Yii::t('ses', 'UnSubscribed Emails');

/**
 * Scripts not minimized and translations not included
 */
$this->widget('bootstrap.widgets.TbGridView', array(
	'dataProvider' => $unsubscribed->search(),
	'filter' => $unsubscribed,
	'summaryText' => false,
	'type' => 'striped bordered condensed',
	'columns' => array(
		array(
			'name' => 'email',
			'type' => 'email'
		),
		array(
			'filter' => false,
			'name' => 'create_time',
			'type' => 'datetime'
		),
		array(
			'header' =>  Yii::t('ses', 'Edit'),
			'class' => 'CButtonColumn',
			'afterDelete' => 'function(link,success,data){ if(success) $("#notifier").html(data); }',
			'template' => '{delete}'
		),
	),
));

Yii::app()->clientScript
	->registerScript('unsubscribed-manage-head', '
		var unsubscribe = (function($){
			return {
				init:function(){
					$(".add-new").on("click",function(){
						var input = $(this).prev("input");
						var notifier = $(this).next("p.help-block");
						if(!unsubscribe.isEmail(input.val()))
						{
							notifier.addClass("error").html("Email doens\'t seem correct.");
							hideDelayed(notifier);
							return false;
						}
						this.form.submit();
						return true;
					});
					this.hideDelayed($("#notifier"));
				},
				isEmail:function(text){
					return text.match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i);
				},
				hideDelayed:function(el,ms)
				{
					ms = typeof ms==="undefined"?5000:ms;
					setTimeout(function(){el.fadeOut();},ms);
				}
			}
		})(jQuery);
	', CClientScript::POS_END)
	->registerScript('unsubscribed-manage-ready', 'unsubscribe.init();', CClientScript::POS_READY);