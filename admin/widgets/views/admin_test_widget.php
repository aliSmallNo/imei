<?php
/**
 * Created by PhpStorm.
 * User: b_tt
 * Date: 2019/4/26
 * Time: 15:46
 */
?>

<h3><?= $prop1 ?></h3>
<ul>
	<?php foreach ($data as $k => $v) { ?>
		<li>key:<?= $k; ?> val:<?= $v; ?></li>
	<?php } ?>
</ul>



