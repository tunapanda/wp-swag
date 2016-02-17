<div class="track listing">
    <div class="listing-info">
    	<div class="header">
    		<div class="title">
    			<?php echo $page->post_title; ?>
    		</div>
    	</div>
    
    	<div class="description">
    		<?php echo $page->post_excerpt; ?>
    	</div>
        
        <div class="footer">
            <span class="list-count">
            <?php 
                echo $page->swagpaths; 
                if( $page->swagpaths == 1 ){
                    echo " Swagpath";
                } else {
                    echo " Swagpaths";
                }
            ?>
            </span>
    	    <span class="list-link"><a href="<?php echo get_page_link($page->ID); ?>">Visit Track</a></span>
        </div>
    </div>
</div>