<?php
$this->pageTitle = 'Campaign Module Command Tester';

$this->widget('zii.widgets.CBreadcrumbs', array(
	'homeLink' => false,
	'links' => array(
		'Campaigns' => array('campaign/manage'),
		$this->pageTitle,
	),
	'separator' => '<span class="divider">/</span>',
	'htmlOptions' => array('class' => 'breadcrumb')
));

$js = '';

$js = <<<EOD
$('.link-send').on('click',function(){
	var href = $(this).prop('href');
	var title = $(this).prop('title');
	simplePrompt({
		message: "Are you really sure to execute "+title+"?",
		buttons: [
			{ label: "Yes, proceed", "callback":function(){
				document.location.href = href;
				return true;
			}},
			{ label: "Cancel", "default": true }
		]
	});
	return false;
});
$('.link-reset').on('click', function(){
	var href = $(this).prop('href');
	var title = $(this).prop('title');
	simplePrompt({
		messageHtml: "Please, enter the id of the campaign to reset: <input type='text' id='c-reset' style='width:80px'/>",
		buttons: [
			{ label: "Reset", "callback":function(){
				var id = $("#c-reset").val();
				var url = href.indexOf("?")>=0?href+"&id=":href+"?id=";
				document.location.href = url + id;
				return true;
			}},
			{ label: "Cancel", "default": true }
		]
	});
	return false;
});
$('.link-queued').on('click', function(){
	var href = $(this).prop('href');
	var title = $(this).prop('title');
	simplePrompt({
		messageHtml: "Enter the id of the campaign to delete queued emails: <input type='text' id='c-delete' style='width:80px'/>",
		buttons: [
			{ label: "Remove Queued", "callback":function(){
				var id = $("#c-delete").val();
				var url = href.indexOf("?")>=0?href+"&id=":href+"?id=";
				document.location.href = url + id;
				return true;
			}},
			{ label: "Cancel", "default": true }
		]
	});
	return false;
});
$('.status-reset').on('click',function(){

	var id = $(this).data('id');
	if(id)
	{
		var url = "{$this->createUrl('campaign/testCommand',array('secret_key' => '3b831b03f132f6c86ee93538b7f2e677f8c45f8e', 'action' => 'r'))}";
		url += url.indexOf("?")>=0 ? "&id=" : "?id=";
		document.location.href = url + id;
		return true;
	}
});
EOD;

?>
<div class="row" id="step-1">
	<div class="span14">
		<div class="alert alert-block">
		<?php
		switch ($action) {
			case 'q': // queue
				CampaignTest::testEnqueueCommand();
				break;
			case 'd': // delete
				CampaignTest::deleteQueued(Yii::app()->getRequest()->getParam('id'));
				break;
			case 's': // send
				CampaignTest::testSendCommand();
				break;
			case 'f': // send with force SES email sending (use with caution as it will send queued emails)
				CampaignTest::testSendCommand(true);
				break;
			case 'r': // reset
				CampaignTest::resetToDraft(Yii::app()->getRequest()->getParam('id'));
				break;
			default:
				echo 'Please, select the test option you wish to execute.<br/><br/><strong>Important</strong>: "Test Send Command (Force SES Delivery)" will actually <strong>deliver</strong> queued emails. Use with CAUTION.';
		}
		?>
		</div>
		<div class="form-actions">
			<?php echo CampaignTest::displayTestLinks();?>
		</div>
		<div class="page-header">
			<h4>Legend <small>What every button does</small></h4>
		</div>
		<table class="table table-bordered table-striped">
			<thead>
			<tr>
				<th>Labels</th>
				<th>Description</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>
					<span class="label label-info">Reset a Campaign to DRAFT Status</span>
				</td>
				<td>
					Sometimes it may happen that you get stuck on one type of <span class="badge">STATUS</span>,
					or wish to return from <strong>TEST</strong> to <strong>DRAFT</strong>. This button
					allows you specifically this. <strong>DRAFT</strong> status is the only one that allows you to edit a campaign again.
				</td>
			</tr>
			<tr>
				<td>
					<span class="label label-info">Delete Queued Emails of a Campaign</span>
				</td>
				<td>
					This is the so called <strong>PANIC BUTTON</strong>. You can delete a queued emails with this button. It is very useful,
					when you have dealt with the test processes and wish to remove those queued.
				</td>
			</tr>
			<tr>
				<td>
					<span class="label label-info">Test Queue Command</span>
				</td>
				<td>
					The <span class="badge badge-important">QUEUE COMMAND</span> is one of the console commands that exist in
					<strong>CampaignCommand</strong> file and that compound the set of functions to be executed with CRON JOBS.
					 This button, will allow you to test if the campaign is correctly set and parsed.
					<strong>Note: </strong>The queued emails are set on <strong>TEST</strong> status, so you don't need to worry about
					if the CRON JOB will process them or not. They won't be processed at all.
				</td>
			</tr>
			<tr>
				<td>
					<span class="label label-info">Test Send Command</span>
				</td>
				<td>
					This is the button that will test the second console command. Queued emails on <strong>TEST</strong> status will be
					processed as if they were going to be sent. Just make sure you don't test this option with thousand+ emails.
				</td>
			</tr>
			<tr>
				<td>
					<span class="label label-info">Test Send Command (Force SES Delivery)</span>
				</td>
				<td>
					Is exactly as the <strong>Test Send Command</strong> with the difference that this one will send the emails processed
					via the SES delivery service.
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<strong>Regular Test process flow:</strong><br/>
					<ol>
						<li>User sets a campaign to TEST mode that has a few selected users</li>
						<li>User clicks button <strong>Test Command</strong></li>
						<li>User clicks button <strong>Test Queue Command</strong></li>
						<li>User clicks button <strong>Test Send Command</strong></li>
						<li>User after clears test queues by clicking <strong>Delete Queued Emails of a Campaign (remember the ID of the campaign as it is needed)</strong></li>
						<li>User resets a campaign to its DRAFT status so it can continue editing.</li>
					</ol>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<?php
Yii::app()->clientScript
	->registerScriptFile( $this->module->assetsUrl . '/js/jquery.popups.js', CClientScript::POS_END)
	->registerScript('campaign-test', $js, CClientScript::POS_READY);