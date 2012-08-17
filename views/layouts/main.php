<?php
//	Yii::app()->clientscript
//		->registerCssFile( $this->module->getAssetsUrl() . '/css/bootstrap.css' )
//		->registerCssFile( $this->module->getAssetsUrl() . '/css/bootstrap-responsive.css' )
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $this->pageTitle; ?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le styles -->
	<style>
		body {
			padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
		}

		@media (max-width: 980px) {
			body {
				padding-top: 0px;
			}
		}
	</style>

	<!-- Le fav and touch icons -->
	<link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/favicon.ico">
	<!--Uncomment when required-->
	<!--<link rel="apple-touch-icon" href="images/apple-touch-icon.png">-->
	<!--<link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">-->
	<!--<link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">-->
</head>

<body>
<?php $this->widget('bootstrap.widgets.TbNavbar', array(
	'brand' => 'Booster SES Module',
	'brandUrl' => 'http://www.clevertech.biz',
	'collapse' => true,
	'items' => array(
		array(
			'class' => 'bootstrap.widgets.TbMenu',
			'items' => array(
				/** change this link according to your backend dashboard **/
				array('label' =>  Yii::t('ses', 'Dashboard'), 'url' => array('/site/index')),
				array('label' =>  Yii::t('ses', 'Campaigns'), 'url' => '#', 'items' => array(
					array('label' =>  Yii::t('ses', 'Manage'), 'url' => array('campaign/manage')),
					array('label' =>  Yii::t('ses', 'Create'), 'url' => array('campaign/create')),
					'-------',
					array('label' =>  Yii::t('ses', 'Amazon Stats'), 'url' => array('amazon/manage'))
				)),
				array('label' =>  Yii::t('ses', 'Unsubscribed'), 'url' => array('unsubscribed/manage')),
				array('label' =>  Yii::t('ses', 'Login'), 'url' => array('/site/login'), 'visible' => $this->module->useOwnLogin && Yii::app()->user->isGuest),
				array('label' =>  Yii::t('ses', 'Logout'), 'url' => array('/site/logout'), 'visible' => $this->module->useOwnLogin &&  !Yii::app()->user->isGuest)
			)
		)
	),
)); ?>

<!-- / starts container -->
<div class="container">
	<?php echo $content ?>
</div>
<!-- /container -->

<hr/>

<!-- /footer -->
<footer>
	<div class="container">
		<p>
			<a href="http://www.clevertech.biz" data-bitly-type="bitly_hover_card">
				<img src="<?php echo $this->module->getAssetsUrl() . '/images/ctlogo.png';?>" alt="Clevertech" style="max-width:100%;">
			</a>
			<br>
			well-built beautifully designed web applications<br>
			<a href="http://www.clevertech.biz" data-bitly-type="bitly_hover_card">www.clevertech.biz</a>
		</p>
	</div>
</footer>
<!-- /footer -->

</body>
</html>
