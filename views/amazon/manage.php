<?php
$this->pageTitle = Yii::t('ses', 'Amazon SES Dashboard');

$data = Amazon::model()->getStatsData();
$stats = Amazon::model()->getSendStatistics();
?>
<h1><?php echo Yii::t('ses', 'Amazon Ses Dashboard - Usage Statistics');?></h1>
<div class="row-fluid">

	<div class="span14">
		<div class="span6">
		<h3><?php echo Yii::t('ses', 'Quota');?></h3>
		<table class="table table-bordered table-striped">
			<thead>
			<tr>
				<th><?php echo Yii::t('ses', 'Sent Last 24H');?></th>
				<th><?php echo Yii::t('ses', 'Max 24H Send');?></th>
				<th><?php echo Yii::t('ses', 'Max Send Rate');?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td><?php echo Amazon::model()->getSentLast24Hours(); ?></td>
				<td><?php echo Amazon::model()->getMax24HourSend();?></td>
				<td><?php echo Amazon::model()->getMaxSendRate();?></td>
			</tr>
			</tbody>
		</table>
		</div>
	</div>
</div>
<div class="row-fluid">
<div class="span6 offset6">
	<div class="btn-group pull-right" style="margin-bottom: 5px">
		<button class="btn-display btn active" href="#graph"><?php echo Yii::t('ses', 'graph');?></button>
		<button class="btn-display btn" href="#grid"><?php echo Yii::t('ses', 'grid');?></button>
	</div>
</div>
</div>
<div class="row-fluid panel-display" id="graph" style="display:block">

	<span class="span14">
		<?php
		$this->widget('ses.components.highcharts.HighCharts', array(
			'options' => array(
				'title' => array(
					'text' => Yii::t('ses', 'Simple Email Service Statistics'),
					'x' => -20
				),
				'xAxis' => array(
					'type'=>'datetime'
				),
				'yAxis' => array(
					'title' => array('text' => 'Number'),
					'plotLines' => array(
						array(
							'value' => 0,
							'width' => 1,
							'color' => '#808080'
						)
					),
					'min' => 0
				),
				'series' => array(
					array(
						'name' => Yii::t('ses', 'Delivery Attempts'),
						'data' => $data['attempts'] //$attempts
					),
					array(
						'name' => Yii::t('ses', 'Bounces'),
						'data' => $data['bounces'] //$bounces
					),
					array(
						'name' => Yii::t('ses', 'Rejects'),
						'data' => $data['rejects'] //$rejects
					),
					array(
						'name' => Yii::t('ses', 'Complains'),
						'data' => $data['complaints'] //$complaints
					)
				)
			)
		));
		?>
	</span>
</div>

<div class="row-fluid panel-display" id="grid" style="display:none">
	<div class="span14">
		<div class="well">
			<h3><?php echo Yii::t('ses', 'Usage');?></h3>
			<table class="table table-bordered table-striped">
				<thead>
				<tr>
					<th><?php echo Yii::t('ses', 'Attempts');?></th>
					<th><?php echo Yii::t('ses', 'Time');?></th>
					<th><?php echo Yii::t('ses', 'Rejects');?></th>
					<th><?php echo Yii::t('ses', 'Bounces');?></th>
					<th><?php echo Yii::t('ses', 'Complains');?></th>
				</tr>
				</thead>
				<tbody>

				<?php if (count($stats['member'])) : ?>
					<?php foreach ($stats['member'] as $member): ?>
					<tr>
						<td><?php echo $member['DeliveryAttempts'];?></td>
						<td><?php echo str_replace(array('T','Z'),array(' ',' '),$member['Timestamp']);?></td>
						<td><?php echo $member['Rejects'];?></td>
						<td><?php echo $member['Bounces'];?></td>
						<td><?php echo $member['Complaints'];?></td>
					</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="5"><?php echo Yii::t('ses', 'No usage data found');?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php
Yii::app()->clientScript
	->registerScript('amazon-ses-dashboard','
		$(".btn-display").on("click",function(){
		$(".btn-display").removeClass("active");
		var $that = $(this);
		$(".panel-display").hide();
		$that.addClass("active");
		$($that.attr("href")).show();
		});
	',CClientScript::POS_READY);

