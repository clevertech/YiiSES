<!DOCTYPE html><html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo strip_tags($this->pageTitle) ?></title>
	<style type="text/css">html, body { background-color: #eee }</style>
</head>
<body class="admin-main">

<div class="container">

	<div class="unsubscribe-content">
		<div class="row-fluid">
			<div class="span14">
				<?php echo $content ?>
			</div>
		</div>
	</div>

	<footer>
		<p>&copy; <?php echo Yii::app()->name;?> <?php echo date('Y') ?></p>
	</footer>

</div>
<!-- /container -->
<div id="modal" class="modal hide fade"></div>
</body>
</html>
