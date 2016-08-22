<?php
/**
 * The template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', get_post_format() ); ?>

				<ul class="social-share">
					<li class="facebook">
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink(); ?>" alt="Facebook" title="Share on Facebook" target="_blank"></a>
					</li>
					<li class="twitter">
						<a href="https://twitter.com/home?status=<?php echo urlencode( get_the_title() ); ?>%20%23instafluence%20<?php the_permalink(); ?>" alt="Twitter" title="Share on Twitter" target="_blank"></a>
					</li>
					<li class="gplus">
						<a href="https://plus.google.com/share?url=<?php the_permalink(); ?>" alt="Google+" title="Share on Google+" target="_blank"></a>
					</li>
				</ul>

				<?php twentythirteen_post_nav(); ?>
				<?php comments_template(); ?>

			<?php endwhile; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>