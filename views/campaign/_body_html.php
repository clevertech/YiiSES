<?php
/**
 * _body_html.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 8/13/12
 * Time: 8:44 PM
 */
$images = array();

if(!isset(Yii::app()->params['ses.files.path']))
{
	// You can display a message
	// Yii::app()->user->setFlash('warning', '<strong>Warning!</strong> You haven\'t set your images upload path!');
	// or set it to runtime
	Yii::app()->params['ses.files.path'] = Yii::app()->getRuntimePath();
}
else
{
	$images['imageUpload'] = $this->createUrl('campaign/upload', array('id'=>$model->id));
	$images['imageGetJson'] = $this->createUrl('campaign/gallery');
}
$this->widget('bootstrap.widgets.TbAlert', array(
	'block'=>true,
	'fade'=>true,
	'closeText'=>'&times;'
));
$this->widget('ses.widgets.redactorjs.RedactorJS',
	array('model'=>$model,
		'attribute'=>'body_html',
		'width'=>'80%',
		'height'=>'300px',
		'editorOptions'=>CMap::mergeArray($images, array(
			'buttons' => array('html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|',
						'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
						'image', 'video', 'table', 'link', '|',
						'fontcolor', 'backcolor', '|',
						'alignleft', 'aligncenter', 'alignright', 'justify', '|',
						'horizontalrule')
		))));