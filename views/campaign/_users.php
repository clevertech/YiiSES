<?php
$this->widget('bootstrap.widgets.TbGridView', array(
	'id'=>'campaign-users-grid',
	'dataProvider' => $dataProvider,
	'filter' => $user,
	'type' => 'striped bordered condensed',
	'summaryText'=>false,
	'afterAjaxUpdate'=>'function(id, data){
		$("#"+id).find("input[type=\'checkbox\']").on("click",campaign.selectRecipient);
	}',
	'columns' => array(
		array(
			'id'=>'selectedItems',
			'name'=>'email',
			'class'=>'CCheckBoxColumn',
			'selectableRows' => 2,
			'checked'=>'strpos("'.$selected_users.'",$data->email)!==false'
		),
		'username',
		'email'
	),
));
?>