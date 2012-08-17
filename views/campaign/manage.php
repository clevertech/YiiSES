<div id="notifier">
	<?php
	$this->widget('bootstrap.widgets.TbAlert', array(
		'block'=>true,
		'fade'=>true,
		'closeText'=>'&times;'
	));
	?>
</div>

<?php
$this->pageTitle =  Yii::t('ses', 'Campaigns');
/**
 * display breadcrumbs
 */
$this->widget('bootstrap.widgets.TbBreadcrumbs', array(
	'homeLink' => false,
	'links' => array(
		$this->pageTitle,
	),
	'separator' => '<span class="divider">/</span>',
	'htmlOptions' => array('class' => 'breadcrumb')
));

$this->widget('bootstrap.widgets.TbGridView', array(
	'dataProvider' => $campaign->search(),
	'filter' => $campaign,
	'type' => 'striped bordered condensed',
	'summaryText'=>false,
	'columns' => array(
		'name',
		'subject',
		array(
			'filter' => Campaign::model()->getStatusArray(),
			'name' => 'status',
			'type' => 'raw',
			'value' => '$data->getLabelledStatus()'
		),
		array(
			'filter' => false,
			'header' => Yii::t('ses', 'Open Rate'),
			'type' => 'raw',
			'value' => 'CHtml::tag("div",array("class"=>"progress progress-striped progress-important"),
					CHtml::tag("div",
						array("class"=>"bar","style"=>"text-align:center;color:#333;width:".($data->total_list? intval(($data->getTotalOpened() / $data->total_list) * 100) : 0)."%"),
						($data->total_list? intval(($data->getTotalOpened() / $data->total_list) * 100) : 0)."%"))'
		),
		array(
			'filter' => false,
			'header' => 'Created',
			'name' => 'create_time',
			'type' => 'datetime'
		),
		array(
			'header' => Yii::t('ses', 'Edit'),
			'class' => 'bootstrap.widgets.TbButtonColumn',
			'template' => '{stats} {update} {delete}',
			'afterDelete'=>'function(link,success,data){ if(success) $("#notifier").html(data); }',
			'buttons' => array(
				'stats' => array(
					'label' => 'stats',
					'imageUrl' => $this->module->assetsUrl . '/images/stats.png',
					'visible' => '$data->status==' . Campaign::STATUS_SENT . ' && $data->total_list',
					'url' => 'Yii::app()->createUrl("ses/campaign/stats",array("id"=>$data->id))'
				),
				'delete' => array(
					'visible' => '$data->status==' . Campaign::STATUS_DRAFT,

				)
			)
		),
	),
));