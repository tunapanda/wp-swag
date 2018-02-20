<div class="member-page">
	<div class="member-page-header">
		<?php echo $avatar; ?>
		<h1><?php echo $username; ?></h1>
		<div class="member-info">
			Total Swag: <?php echo $swagCount; ?>
		</div>
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
				<div class="swag-badges">
					<?php foreach ($track["badges"] as $badge) { ?>
						<div class="swag-badge">
							<a href="<?= $badge['permalink'] ?>">
							<img src="<?= $badge['image'] ?>" alt="<?= $badge['name'] ?>">
							<div class="sw-badge-info">
								<div class="badge-name"><h3><?= $badge['name'] ?></h3></div>
								<div class="badge-description"><?= $badge['description'] ?></div>
								<div class="badge-date">Awarded <?= $badge['date_issued'] ?></div>
							</div>
							</a>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>


