<?php
$this->pageTitle =  Yii::t('ses', 'Campaign Statistics');

/**
 * display breadcrumbs
 */
$this->widget('bootstrap.widgets.TbBreadcrumbs', array(
	'homeLink' => false,
	'links' => array(
		Yii::t('ses', 'Campaigns') => array('campaign/manage'),
		$model->name => array('campaign/update', 'id' => $model->id),
		$this->pageTitle,
	),
	'separator' => '<span class="divider">/</span>',
	'htmlOptions' => array('class' => 'breadcrumb')
));

$opened = $model->getTotalOpened();
?>

<div class="row-fluid">
	<div class="span6">
		<h3><?php echo $model->name;?></h3>
		<table class="table table-bordered table-striped">
			<thead>
			<tr>
				<th><?php echo Yii::t('ses', 'Sent');?></th>
				<th><?php echo Yii::t('ses', 'Failed');?></th>
				<th><?php echo Yii::t('ses', 'Succeeded');?></th>
				<th><?php echo Yii::t('ses', 'Opened');?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php echo $model->total_list;?></td>
				<td><?php echo $model->total_failed;?></td>
				<td><?php echo $model->total_sent;?></td>
				<td><?php echo $opened;?></td>
			</tr>
			<tr>
				<td colspan="2"><strong><?php echo Yii::t('ses', 'Sent on:');?></strong></td>
				<td colspan="2" nowrap="nowrap"><?php echo $model->sent_at;?></td>
			</tr>
			</tbody>
		</table>

	</div>
	<div class="span6">
		<div class="well">
			<h3>Failed <?php echo intval(($model->total_failed / $model->total_list) * 100);?>%</h3>

			<div class="progress progress-striped progress-danger">
				<div class="bar"
				     style="width: <?php echo intval(($model->total_failed / $model->total_list) * 100);?>%;"></div>
			</div>
			<h3>Successful <?php echo intval(($model->total_sent / $model->total_list) * 100);?>%</h3>

			<div class="progress progress-striped progress-success">
				<div class="bar"
				     style="width: <?php echo intval(($model->total_sent / $model->total_list) * 100);?>%;"></div>
			</div>
			<h3>Opened <?php echo intval(($opened / $model->total_list) * 100);?>%</h3>

			<div class="progress progress-striped progress-important">
				<div class="bar"
				     style="width: <?php echo intval(($opened / $model->total_list) * 100);?>%;"></div>
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<span class="span12">
	<?php

		$this->widget('bootstrap.widgets.TbGridView', array(
			'dataProvider' => $email->search(),
			'filter' => $email,
			'type' => 'striped bordered condensed',
			'columns' => array(
				array(
					'header' => Yii::t('ses', 'To'),
					'name' => 'to_address',
					'type' => 'email'
				),
				array(
					'filter' => array(
						Email::STATUS_SENT => Yii::t('ses', 'Sent'),
						Email::STATUS_DRAFT => Yii::t('ses', 'Draft'),
						Email::STATUS_FAILED => Yii::t('ses', 'Failed'),
						Email::STATUS_PREPARE => Yii::t('ses', 'Processing')
					),
					'name' => 'status',
					'type' => 'raw',
					'value' => '$data->getLabelledStatus()'
				),
				array(
					'header' => Yii::t('ses', 'Opened'),
					'filter' => false,
					'name' => 'opened',
					'type' => 'raw',
					'value' => '$data->opened? Yii::t("ses", "YES"):Yii::t("ses","NO")'
				),
				array(
					'header' => Yii::t('ses', 'Sent Time'),
					'filter' => false,
					'name' => 'send_time',
					'type' => 'datetime'
				),
			),
		));
		?>
	</span>
</div>