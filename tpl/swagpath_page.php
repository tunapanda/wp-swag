<?php get_header(); ?>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php while (have_posts()) { ?>
				<?php the_post(); ?>

				<?php SwagpathController::instance()->showCurrentSwagpath(); ?>

				<?php
					if ( comments_open() || get_comments_number() ) :
						comments_template();
					endif;
				?>
			<?php } ?>
		</main>
	</div>
<?php get_footer(); ?>


