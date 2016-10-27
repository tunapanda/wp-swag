<div id='swagmapcontainer'>
	<div class='swagmap-mode-link-container'>
		<a href='<?php echo $mylink; ?>'
			class='<?php if ($mode=="my") echo "active" ?>'
		>My Map</a>
		|
		<a href='<?php echo $fulllink; ?>'
			class='<?php if ($mode=="full") echo "active" ?>'
		>Full Map</a>
	</div>
	<script>
		var SWAGMAP_MODE = '<?php echo $mode ?>';
		var PLUGIN_URI = '<?php echo $plugins_uri; ?>';
	</script>
</div>