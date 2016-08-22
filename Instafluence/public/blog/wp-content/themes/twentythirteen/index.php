<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme and one of the
 * two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
		<?php if ( have_posts() ) : ?>

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
			<?php endwhile; ?>

			<?php twentythirteen_paging_nav(); ?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>