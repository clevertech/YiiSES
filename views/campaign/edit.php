<?php
/**
 * check whether is new record to set appropriate names for page title and default values
 */
if ($model->isNewRecord)
{
	// default values
	$this->pageTitle =  Yii::t('ses', 'Create new campaign');
	$model->utm_source =  Yii::t('ses', 'campaign');
	$model->utm_medium =  Yii::t('ses', 'email');
	$model->body_html =  Yii::t('ses', 'Body HTML');
	$model->body_text =  Yii::t('ses', 'Body Text');
	$model->status = Campaign::STATUS_DRAFT;
	$model->scheduled_for = date('m/d/Y');
} else
{
	$this->pageTitle = 'Edit campaign';
	if (!$model->scheduled_for)
	{
		$model->scheduled_for = $model->status == Campaign::STATUS_DRAFT ? date('m/d/Y') : 'N/A';
	}
}
$userGrid = new User();
$criteriaGrid = new CDbCriteria();
$criteriaGrid->compare('subscribed', '1');
$criteriaGrid->addCondition('email NOT IN (SELECT email FROM campaign_unsubscribed)');

/**
 * display breadcrumbs
 */
$this->widget('bootstrap.widgets.TbBreadcrumbs', array(
	'homeLink' => false,
	'links' => array(
		Yii::t('ses', 'Campaigns') => array('campaign/manage'),
		$this->pageTitle,
	),
	'separator' => '<span class="divider">/</span>',
	'htmlOptions' => array('class' => 'breadcrumb')
));

/**
 * we need current user to send test email
 */
$user = User::model()->findByPk(Yii::app()->user->id);
?>

<?php
$this->widget('bootstrap.widgets.TbAlert', array(
	'block' => true,
	'fade' => true,
	'closeText' => '&times;'
));
?>

<?php
/**
 * Check whether is an editable status
 */
$editableStatus = !in_array($model->status, array(Campaign::STATUS_READY, Campaign::STATUS_SENT));


$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'contact-form',
	'type' => 'horizontal',
	'enableClientValidation' => true,
	'clientOptions' => array(
		'validateOnSubmit' => true,
	)
));
echo $form->hiddenField($model, 'selected_users');
?>

<div class="well">
	<h4><?php echo Yii::t('ses', 'Google UTM variables');?></h4>

	<div class="span5">
		<?php echo $form->textFieldRow($model, 'utm_source', array('class' => 'input-medium')); ?>
		<?php echo $form->textFieldRow($model, 'utm_medium', array('class' => 'input-medium')); ?>
	</div>
	<div class="span5">
		<?php echo $form->textFieldRow($model, 'utm_term', array('class' => 'input-medium')); ?>
		<?php echo $form->textFieldRow($model, 'utm_content', array('class' => 'input-medium'));?>
	</div>
	<div style="clear:both"></div>
</div>

<?php echo $form->textFieldRow($model, 'name'); ?>
<?php echo $form->textFieldRow($model, 'scheduled_for'); ?>
<?php echo $form->dropDownListRow($model, 'status', Campaign::model()->getStatusArray(false), array('disabled' => !$editableStatus ? 'disabled' : '')); ?>
<?php echo $form->textFieldRow($model, 'subject'); ?>

<?php
	echo CHtml::tag('div', array(
		'class' => 'alert alert-block',
		'style' => ($model->isNewRecord ? 'display:none' : 'display:block') . ';min-height:60px',
		'id' => 'campaign-users'),
	(!$model->isNewRecord ? Campaign::model()->getTotalUsers($model->getAttributes()) : 0) . Yii::t('ses', ' users were selected for this campaign'));
?>

<h4><?php echo Yii::t('ses', 'Select Recipients');?></h4>

<?php
	echo $form->checkBoxRow($model, 'to_subscribers', array(
		'hint' =>  Yii::t('ses', '<strong>Note:</strong> Selecting this option will send the email to all subscribed users.')));
?>

<div id="recipients-panel" style="display:block">
	<?php $this->widget('bootstrap.widgets.TbTabs', array(
	'type' => 'tabs',
	'tabs' => array(
		array(
			'label' => Yii::t('ses', 'Custom Recipients'),
			'active' => true,
			'content' => $form->textAreaRow($model, 'custom', array(
				'style' => 'width:98%',
				'hint' => Yii::t('ses',  Yii::t('ses', '<span class="badge badge-info">Info</span> Add emails separated by commas or spaces '))))
		),
		array(
			'label' => Yii::t('ses', 'Selected Users'),
			'content' => $this->renderPartial('_users', array(
				'user' => $userGrid,
				'dataProvider' => (new CActiveDataProvider('User', array('criteria' => $criteriaGrid, 'pagination' => array('pageSize' => 25)))),
				'selected_users' => $model->selected_users), true)
		)
	)));?>
</div>

<hr/>

<h5><?php echo Yii::t('ses', 'Templates');?></h5>

<?php
	echo ($editableStatus ?
		(CHtml::tag('div', array('id' => 'dd-thumbnails'), '') .
			CHtml::tag('img', array('id' => 'dd-loader', 'src' => $this->module->assetsUrl . '/images/loading.gif', 'style' => 'float:left;margin:5px;display:none')))
		:
		'<div class="controls"><span class="input-large uneditable-input">' . Template::getTemplateName($model->template_id) . '</span></div>');
?>
<br/><br/>
<?php
	$this->widget('boostrapSES.widgets.TbTabs', array(
		'type' => 'tabs',
		'tabs' => array(
			array(
				'label' => Yii::t('ses', 'Body HTML'),
				'active' => true,
				'content' => $this->renderPartial('_body_html', array('model' => $model), true)
			),
			array(
				'label' => Yii::t('ses', 'Body Text'),
				'content' => $form->textArea($model, 'body_text', array('style' => 'width:98%;height:300px', 'label' => false))
			)
		)
	));
?>

<hr/>

<?php
	/** email on acid required elements */
	echo CHtml::textArea('emailonacidmessage', Email::formatHTMLMessage('{{BODY}}', Template::getView($model->template_id)), array('id' => 'emailonacidmessage', 'style' => 'display:none'));
	echo CHtml::textArea('emailonacidbody', "", array('id' => 'emailonacidbody', 'style' => 'display:none'));
?>

<div class="form-actions">
	<?php if ($model->isNewRecord): ?>
	<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'type' => 'primary', 'label' => Yii::t('ses', 'Create'))); ?>
	<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'link', 'label' => Yii::t('ses', 'Cancel'), 'url' => $this->createUrl('campaign/manage'))); ?>
	<?php else: ?>
	<?php if ($model->status == Campaign::STATUS_TEST) : ?>
		<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'button', 'label' => Yii::t('ses', 'Test Command'), 'htmlOptions' => array('class' => 'btn secondary test-command'))); ?>
		<?php endif; ?>
	<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'button', 'label' => Yii::t('ses', 'Test Campaign'), 'htmlOptions' => array('class' => 'btn secondary test-campaign '))); ?>
	<?php if ($model->total_list): ?>
	<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'link', 'label' => Yii::t('ses', 'Stats'), 'url'=>array('campaign/stats', 'id'=>$model->id))); ?>
	<?php endif;?>
	<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'button', 'label' => Yii::t('ses', 'Validate with EmailOnAcid'), 'htmlOptions' => array('class' => 'btn secondary validate'))); ?>
	<?php $this->widget('bootstrap.widgets.TbButton', array(
		'buttonType' => 'link',
		'label' => Yii::t('ses', 'Preview Message'),
		'url' => $this->createUrl('campaign/preview', array('id' => $model->id)),
		'htmlOptions' => array('class' => 'btn secondary preview-template ', 'data-modal-width' => '820px')));
	?>
	<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'submit', 'type' => 'primary', 'label' => Yii::t('ses', 'Update'), 'htmlOptions'=>array('class'=>(!$editableStatus?'disabled':'')))); ?>
	<?php $this->widget('bootstrap.widgets.TbButton', array('buttonType' => 'link', 'label' => Yii::t('ses', 'Cancel'), 'url' => $this->createUrl('campaign/manage'))) ?>
	<?php endif;?>
</div>
<?php $this->endWidget(); ?>

<div id='eoacapi' style="display:none"></div>

<?php
/**
 * Scripts not minimized so you can easily modify them to suite your needs.
 * NOTE: Translations are not done in js Scripts.
 */
$nonce = Campaign::nonce();
$assetsUrl = $this->module->assetsUrl;
$jsTestCommand = $model->status == Campaign::STATUS_TEST ? '$(".test-command").on("click", function(){
	window.location.href = "' . $this->createUrl('campaign/testCommand', array('secret_key' => CampaignTest::SECRET_KEY)) . '";});' : '';
Yii::app()->clientScript
	->registerCssFile($assetsUrl . '/css/token-field.css')
	->registerCssFile($assetsUrl . '/css/datepicker.css')
	->registerCoreScript('jquery')
	->registerScriptFile($assetsUrl . '/js/jquery.token-field.js', CClientScript::POS_END)
	->registerScriptFile($assetsUrl . '/js/jquery.popups.js', CClientScript::POS_END)
	->registerScriptFile($assetsUrl . '/js/jquery.ddslick.js', CClientScript::POS_END)
	->registerScriptFile($assetsUrl . '/js/jquery.blockui.js', CClientScript::POS_END)
	->registerScriptFile($assetsUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_END)
	->registerScript('emailonacid-button', '
var _eoa = {
        key:"' . Yii::app()->params['ses.emailonacid.key'] . '",
		nonce:"' . $nonce . '",
		hash:"' . sha1($nonce . Yii::app()->params['ses.emailonacid.pwd']) . '",
		user_guid:"12345",
		test_id:"6789",
		auto_select:true,
		code_analysis:false,
		submit_img:"",
		subject_fld:"' . CHtml::activeId($model, 'subject') . '",
		spam_fld:"myspam",
		html_fld:"emailonacidbody",
		url_fld:"",
		hide_inputs:true,
		hide_subject:true,
		hide_url:true,
		hide_html:true,
		hide_spam:true,
		hide_clients:true,
		hide_all_inputs:true,
		result_new_window:true};

(function(){
	var _eoajs = document.createElement("script");
	_eoajs.type = "text/javascript";
	_eoajs.async = true;
	_eoajs.src = ("https:" == document.location.protocol ? "https://" : "http://") + "capi.emailonacid.com/s1/";
	(document.getElementsByTagName("head")[0]||
	document.getElementsByTagName("body")[0]).appendChild(_eoajs);
	})();'
	, CClientScript::POS_BEGIN)
	->registerScript('campaign-module', '
var campaign = (function($){
	var status = ' . ($model->isNewRecord ? Campaign::STATUS_DRAFT : $model->status) . ';
	var blockUIOptions = {message:null, overlayCSS:{opacity:0.3}};
	var xhr = null;
	return {
		init: function()
		{
			' . $jsTestCommand . '
			$(".disabled").on("click",function(){
				return false;
			});
			$(".preview-template").on("click",function(){
				var w = $(this).data("modal-width"), iw = w.substring(0,w.length-2);
				simplePrompt({
					headerHtml: "<h3>Preview</h3>",
					messageHtml: "<iframe src=\""+this.href+"\" width=\""+(iw-20)+"\" height=\"420\" style=\"margin:5px;border:0\"/>",
					width: w,
					height: "540px",
					buttons:[{ label: "Ok", "default": true}]
				});
				return false;
			});
			if(!$("#modal").length)
			{
				$modal = $("<div/>").prop("id","modal");
				$("body").append($modal);
			}
			$("#' . CHtml::activeId($model, 'custom') . '").tokenField({added:this.displayTotals,removed:this.displayTotals, duplicated:function(text){
				simplePrompt({
					messageHtml: "<strong>" + text + "</strong> is already on the list!",
                    buttons: [{
                        label: "Close", "default":true
                    }]
				});
			}});
			var scDate = $("#' . CHtml::activeId($model, 'scheduled_for') . '");
			var date = new Date(scDate.val()||"");

	        scDate.datepicker().on("changeDate",function(ev){
	            var cDate = new Date();
	            if(ev.date.valueOf()<cDate.valueOf())
	            {
	                simplePrompt({
                        messageHtml: "You cannot scheduled a campaign to sent in the past. Minimum is today, which means it will be send ASAP",
                        buttons: [{
                            label: "Close", "default":true
                        }]
                    });
	                scDate.data("datepicker").date = cDate;
	            }
	            scDate.datepicker("hide");
	        });

            $("a[rel=tooltip]").tooltip();

			$(".cancel").click(function(){
				window.location.href = "' . $this->createUrl('campaign/manage') . '";
			});

			$("#' . CHtml::activeId($model, 'status') . '").change(function(){
		        var $this = $(this);
		        var def = ' . ($model->isNewRecord ? Campaign::STATUS_DRAFT : $model->status) . ';

		        if(parseInt($this.val())===' . Campaign::STATUS_READY . ')
		        {
		            simplePrompt({
						message: "Are you sure you are ready to send this campaign? Once saved, you cannot undo its status.",
						buttons: [
							{ label: "Yes, proceed"},
							{ label: "Cancel", "default": true, "callback":function(){
							    var $opt = $this.find("option[value="+def+"]");
							    if ($opt.length){
							        $("ul.customSelect").find("a").each(function(){
							            var $a = $(this);
							            if($a.text()==$opt.text())
							            {
							                $a.trigger("click");
							            }
							        });
							    }
							}}
						]
					});
				}
				else
				    def = $this.val();
			});

			$(".validate").on("click", function(){
			    var message = $("#' . CHtml::activeId($model, 'body_html') . '").val();
		        message = message.replace("{{username}}","JOHNDOE")
		                .replace("{{email}}","JOHNDOE@EMAIL.COM");
		        message = $("#emailonacidmessage").val().replace("{{BODY}}",message);
		        $("#emailonacidbody").text(message);
		        simplePrompt({
		            message: "This will popup emailonacid window to test HTML format against different clients. \
		            Please, remember that if you have changed the template on edition, it won\'t display \
		            until you save the campaign again.",
		            buttons: [
		                { label: "Understood, proceed", "callback":function(){
		                    $("#eoacapi").find("input[type=submit]").click();
		                    }
		                },
		                { label: "Cancel", "default": true}
		            ]
		        });
			});
			$(".stats").on("click", function(){
				window.location.href = "' . $this->createUrl('campaign/stats', array('id' => $model->id)) . '";
			});
			$(".test-campaign").on("click", function(){

				var email = "' . Yii::app()->params['ses.aws.test.email'] . '";
				simplePrompt({
		            messageHtml: "<h3>Test Message</h3>"
								+ "Test message will be send to the following address: <strong>" + email + "</strong>",
		            buttons: [
		                {
		                    label: "Send", "callback":function(){
		                        campaign.showProgress();
		                        $.ajax({
		                            url: "' . $this->createUrl("campaign/test", array("id" => $model->id)) . '",
		                            success: function(r){
		                                $("#modal").hide();
		                                $(".modal-backdrop").hide();
		                                simplePrompt({
		                                    messageHtml: r + "<br/>Selected address was <strong>" + email + "</strong>",
		                                    buttons: [{
		                                        label: "Close", "default":true
		                                    }]
		                                });
		                            }
		                        });
		                    }
		                },
		                { label: "Cancel", "default": true}
		            ]
		        });
			});
			$("input[type=checkbox]").on("click",this.displayTotals);
		},
		checkToken: function(token){
			var emails = $("#' . CHtml::activeId($model, 'custom') . '").val();
		},
		displayTotals: function(){

			var $that = $(this);
			if($that.parent().hasClass("checkbox-column")) // user grid
			{
                var selected = $("#' . CHtml::activeId($model, 'selected_users') . '");
	            var emails = selected.val().length? selected.val().split(","):[];
	            var email = $that.val();

	            if($that.is(":checked"))
	            {
	                if($that.prop("id")=="selectedItems_all")
					{
						$("#campaign-users-grid input[name=\"selectedItems[]\"]").each(function(){
                            var v = $(this).val();
							if($.inArray(v, emails)<0)
							{
								emails.push(v);
							}
						});
					}
					else if($.inArray(email, emails)<0)
	                {
	                    emails.push(email);
	                }
	            }
	            else
	            {
	                if($that.prop("id")=="selectedItems_all")
					{
						$("#campaign-users-grid input[name=\"selectedItems[]\"]").each(function(){
							emails.splice($.inArray($(this).val(), emails),1);
						});
					} else  {
		                emails.splice($.inArray($(this).val(),emails),1);
		                console.log(emails);
	                }
	            }
	            selected.val(emails.join(","));
			}

	        $("#campaign-users").html("<img src=\"' . $this->module->assetsUrl . '/images/loading.gif\" />").show();


			var custom = $("#' . CHtml::activeId($model, 'custom') . '").find("input[type=hidden]").serialize();
            var selected =  $("#' . CHtml::activeId($model, 'selected_users') . '").serialize();
            var subscribers = $("#' . CHtml::activeId($model, 'to_subscribers') . ':checked").serialize();
            var data = [custom, selected, subscribers];

            if(xhr){xhr.abort();}

            xhr = $.ajax({
                url: "' . $this->createUrl("campaign/users") . '",
                data: data.join("&"),
                success: function(r){
					$("#campaign-users").html(r);
                },
                error: function(jqXHR, textStatus, errorThrown){
                    if (jqXHR.status === 0 || jqXHR.readyState === 0) {
				        return;
				    }
                    $("#campaign-users").html("Unexpected error occurred!");
                }
            });

		},
		showProgress: function(){
			$("#modal").hide();
			$(".modal-backdrop").hide();
			var w = $(this).data("modal-width"), m = $("#modal");
			if (w) {
				m.css("width", w);
				var n = Number(/\d+/.exec(w));
				if (n) m.css("margin-left", w.replace(n, -n/2));
			} else m.css({ width: "", "margin-left": "" });
			m.html("<div class=\"modal-header\"><h3>Loading</h3></div>"
			+ "<div class=\"modal-body\"><img src=\"' . $this->module->assetsUrl . '/images/loading.gif\" /></div>").modal("show");
			return false;
		}
	}
})(jQuery);
	', CClientScript::POS_END)
	->registerScript('init-dropdown', (!$editableStatus ? '' : '
var tchanged = false;
var tdefault = ' . $model->template_id . ';
var tindex = 0;
$("#dd-thumbnails").ddslick({
    data: ' . Template::getJSData($this->module->assetsUrl, $model->template_id) . ',
    width: 450,
    imagePosition: "left",
    background: "#FFF",
    selectText: "Select the template, blank is default",
    hiddenInputName: "' . CHtml::activeName($model, 'template_id') . '",
    onSelected: function (data) {
        if(data.selectedData.value==tdefault){
            tindex = data.selectedIndex;
            return;
        }
        simplePrompt({
            message: "This will override your HTML content with default template body layout. Please, remember that changes needs to be updated before previewing message!",
            buttons: [
                { label: "Ok, proceed", "default": true, "callback":function(){
                    $("#dd-loader").show();
                    $.ajax({
                        url: "' . $this->createUrl('campaign/template', array('id' => $model->isNewRecord ? 0 : $model->id)) . '",
                        data: {template:data.selectedData.value},
                        type: "post",
						success: function(r){
							$("#' . CHtml::activeId($model, 'body_html') . '").setCode(r);
							tdefault = data.selectedData.value ;
							var uri = $(".preview-template");
							if(uri.length)
							{
								href = uri.prop("href");
								if(href.indexOf("?")>0)
								{
									href = href.substring(0, href.length-1)+data.selectedData.value;
								}
								else
								{
									href += "?template="+data.selectedData.value;
								}
								uri.prop("href",href);
							}
						},
						error: function(){
							simplePrompt({
								message: "An unexpected error has occurred!",
								buttons:[{ label: "Ok", "default": true}]
							});
							$("#dd-thumbnails").ddslick("select",{index:tindex});
						},
						complete: function() {
						    $("#dd-loader").hide();
						}
                    });
                }},
                { label: "Cancel", "default": true, "callback":function(){
				    $("#dd-thumbnails").ddslick("select",{index:tindex});
				}}
            ]
        });
}});'), CClientScript::POS_READY)
	->registerScript('campaign-edit-ready', 'campaign.init();', CClientScript::POS_READY);

?>