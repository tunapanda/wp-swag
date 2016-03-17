<div id="breadcrumbs">
	<ul id="breadcrumb">
		<?php foreach ($trail as $item) { ?>
			<li><a href="<?php echo $item["url"]; ?>"><?php echo $item["title"]; ?></a></li>
		<?php } ?>
	</ul>
</div>
<?php if ($showHintInfo) { ?>
	<div class='course-info'>
		In order to get the most out of this swagpath, it is recommended that you
		first collect these swag:
		<?php echo $uncollectedSwag; ?>.<br/>
		You can collect them by following these swagpaths:
		<?php echo $uncollectedSwagpaths; ?>
	</div>
<?php } ?>
<?php
	$plugins_uri = WP_Swag_admin::$plugins_uri;
 ?>
<div class='content-tab-wrapper'>
	<ul class='content-tab-list'>
		<script>
		var PLUGIN_URI = '<?php echo $plugins_uri; ?>';
		</script>
		<?php foreach ($swagPost->getSwagPostItems() as $swagPostItem) { ?>
			<li
				<?php if ($swagPostItem->isSelected()) echo "class='selected'"; ?>
			>
				<a href="<?php echo $swagPostItem->getUrl(); ?>">
					<?php if ($swagPostItem->isCompleted($swagUser)) { ?>
						<img
							class='coursepresentation'
							src="<?php echo $plugins_uri;?>/img/completed-logo.png"
						/>
					<?php } else {?>
						<img
							class='coursepresentation'
							src="<?php echo $plugins_uri; ?>/img/coursepresentation-logo.png"
						/>
					<?php } ?>
				</a>
			</li>
		<?php } ?>
	</ul>
	<div class='content-tab-content'>
		<h1><?php echo $swagPost->getSelectedItem()->getTitle(); ?></h1>
		<?php if($showLessonPlan and $lessonplanAvailable) : ?>
		<a href="<?php echo $lessonPlan; ?>" class="button-lessonplan" style="text-align:right;float:right">Download Lesson Plan</a>
	<?php elseif ($showLessonPlan and !$lessonplanAvailable) : ?>
		<button style="text-align:right;float:right" title="Please complete swag to download the lesson plan" class="button-lessonplan disabled" disabled>Download Lesson Plan</button>
		<?php endif; ?>
		<?php echo $swagPost->getSelectedItem()->getContent(); ?>
	</div>
</div>
