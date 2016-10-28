<div class="swag-page">
	<?php foreach ($tracks as $track) { ?>
		<?php if ($track["name"]) { ?>
			<div class="badge-header">
				<h2><?php echo $track["name"]; ?></h2>
				<div class="badge-score"><?php echo $track["score"]; ?></div>
			</div>
			<hr/>
		<?php } ?>
		<?php foreach ($track["badges"] as $badge) { ?>
			<div class="swag-badge">
				<img src="<?php echo $pluginUrl; ?>/img/badge-opaque.png"/>
				<div class="swag-badge-title">
					<?php echo $badge["name"]; ?>
				</div>
			</div>
		<?php } ?>
	<?php } ?>
</div>