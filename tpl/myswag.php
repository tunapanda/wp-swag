<div class="member-page">
	<?php echo $avatar; ?>
	<h1><?php echo $username; ?></h1>
	<div class="member-info">
		Total Swag: <?php echo $swagCount; ?>
	</div>
	<div class="swag-container">
		<div class="swag-page">
			<?php foreach ($tracks as $track) { ?>
				<?php if ($track["name"]) { ?>
					<div class="badge-header">
						<h2 style="color: <?php echo $track["color"]; ?>"><?php echo $track["name"]; ?></h2>
						<div class="badge-score"><?php echo $track["score"]; ?></div>
					</div>
					<hr style="background-color: <?php echo $track["color"]; ?>"/>
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
	</div>
</div>


