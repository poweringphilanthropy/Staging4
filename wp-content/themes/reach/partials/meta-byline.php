<?php
/**
 * Display the byline meta field.
 *
 * @package Reach
 */

/* Hide meta if this is a sticky post */
if ( is_sticky() && ! is_singular() ) :
	return;
endif;

/* Hide post meta if that setting is ticked */
if ( reach_hide_post_meta() ) :
	return;
endif;

$byline = sprintf( _x( 'Posted on %s by %s.', 'posted on date by author', 'reach' ),
	'<time><a href="' . get_permalink() . '">' . get_the_time( get_option( 'date_format' ) ) . '</a></time>',
	'<a href="' . get_author_posts_url( get_the_author_meta( 'ID' ) ) . '">' . get_the_author() . '</a>'
);

if ( comments_open() ) :
	$comment_count = get_comments_number();
	$comments_msg = sprintf( _n( '%s comment', '%s comments', $comment_count, 'reach' ), $comment_count );
	$byline .= '&nbsp;<a href="' . get_comments_link() . '">' . $comments_msg . '</a>';
endif;

?>
<p class="meta meta-byline center">
	<?php echo $byline ?>
</p>
