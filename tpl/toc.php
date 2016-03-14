<div class="masonry-loop">
	<?php foreach ($tracks as $track) { ?>
		<div class="track listing">
		    <div class="listing-info">
		    	<div class="header">
		    		<div class="title">
		    			<?php echo $track["title"]; ?>
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
		    	    <span class="list-link"><a href="<?php echo $track["url"]; ?>">Visit Track</a></span>
		        </div>
		    </div>
		</div>
	<?php } ?>

	<?php foreach ($swagpaths as $swagpath) { ?>
		<div class="course listing <?php /*if (!$prepared) echo "unprepared"; */?>">
		    <div class="listing-info">
		    	<div class="header">
		    		<div class="title">
		    			<?php echo $swagpath["title"]; ?>

		                <?php if ($completed) { ?>
		                    <img class="course-completed" src="<?php echo $plugins_uri; ?>/img/completed-logo.png"/>
		                <?php } ?>
		    		</div>
		    	</div>

		    	<div class="description">
		    		<?php foreach ($swagpath["swag"] as $swag) { ?>
		    			<div class="swag">
		    				<?php if ($swag->isCompletedByCurrentUser()) { ?>
								<img src="<?php echo plugins_url()."/wp-swag/img/badge.png" ?>">
							<?php } else { ?>
								<img src="<?php echo plugins_url()."/wp-swag/img/badge-gray.png" ?>">
							<?php } ?>
		    				<?php echo $swag->getString(); ?>
		    			</div>
		    		<?php } ?>
		    		<?php echo $swagpath["description"]; ?>
		    	</div>

		        <div class="footer">
		            <a href="<?php echo $swagpath["url"]; ?>">Follow Swagpath</a>
		        </div>
		    </div>
		</div>
	<?php } ?>
</div>