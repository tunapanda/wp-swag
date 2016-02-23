<?php 
    $plugins_uri = WP_Swag_admin::$plugins_uri;
 ?>
<div class="course listing <?php if (!$prepared) echo "unprepared"; ?>">
    <div class="listing-info">
    	<div class="header">
    		<div class="title">
    			<?php echo $page->post_title; ?>

                <?php if ($completed) { ?>
                    <img class="course-completed" src="<?php echo $plugins_uri; ?>/img/completed-logo.png"/>
                <?php } ?>
    		</div>
    	</div>
    
    	<div class="description">
    		<?php echo $page->post_excerpt; ?>
    	</div>
        
        <div class="footer">
            <a href="<?php echo get_page_link($page->ID); ?>">Follow Swagpath</a>
        </div>
    </div>
</div>