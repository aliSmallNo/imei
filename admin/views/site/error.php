<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error" style="padding: 20px">
	<p>
		Oops... 页面出错了~~
	</p>
	<div class="alert alert-danger">
		<?= nl2br(Html::encode($ex)) ?>
	</div>

</div>
