<p>
    This page lists plugins that we use and what we use them for.
</p>

<?php add_thickbox(); ?>

<div>
    <?php foreach ($dependencies as $dependency) { ?>
        <div class="swag-admin-dependency-row">
        	<div class="swag-admin-dependency-status">
                <?php echo $dependency["link"]; ?><br/>

        		<?php if ($dependency["status"]=="ok") { ?>
	        		<span class="swag-admin-dependency-ok"><i>Active</i></span>
	        	<?php } else { ?>
	        		<span class="swag-admin-dependency-missing"><i>Missing</i></span>
	        	<?php } ?>
        	</div>
        	<div class="swag-admin-dependency-description">
        		<?php echo $dependency["description"]; ?>
        	</div>
        </div>
    <?php } ?>
</div>