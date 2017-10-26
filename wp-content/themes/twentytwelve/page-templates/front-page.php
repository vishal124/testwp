<?php
/**
 * Template Name: Front Page Template
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Twenty Twelve consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php 
// the query
$wpb_all_query = new WP_Query(array('post_type'=>'post', 'post_status'=>'publish', 'posts_per_page'=>-1)); ?>
 
<?php 
$totalpost = count($wpb_all_query->posts);
global $post;


$count=1;
?>

<ul>
  <li> <?php echo $count;?>
    
      <?php while ( $wpb_all_query->have_posts() ) : $wpb_all_query->the_post(); 



      ?>
       	   <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
      <?php if($count%2==0) : ?>  </li><li> <?php echo $count;?> <?php endif; 

   $count++;
    endwhile; ?>

    </li>
    <!-- end of the loop -->
 
</ul>
 
    <?php wp_reset_postdata(); ?>
 


				<?php //get_template_part( 'content', 'page' ); ?>

			
		</div><!-- #content -->

		<div class="rml_contents"></div>

		<div class="button">this is button</div>
	</div><!-- #primary -->

<?php get_sidebar( 'front' ); ?>
<?php get_footer(); ?>