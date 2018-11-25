<?php
/**
 * Post comments template.
 *
 * @package Reach
 */

/* If comments are closed or a password is required, leave this out completely. */
if ( ! comments_open() || post_password_required() ) :
	return;
endif;

?>
<div id="comments" class="block multi-block comments-section">

	<div class="comment-form-block content-block">

		<div class="title-wrapper">
			<h2 class="block-title with-icon" data-icon="&#xf040;"><?php _e( 'Leave a comment', 'reach' ) ?></h2>
		</div>

		<?php comment_form( array(
			'comment_field' => reach_comment_form_field_comment(),
			'title_reply'   => '',
		) ) ?>
	</div>

	<?php if ( have_comments() ) : ?>
		
		<div class="comments-block content-block cf">           
			<div class="title-wrapper">
				<h2 class="block-title with-icon" data-icon="&#xf086;">
					<?php printf( _n( 'One comment', '%1$s comments', get_comments_number(), 'reach' ),
						number_format_i18n( get_comments_number() )
					) ?>
				</h2>
			</div>

			<?php
			/* Are there comments to navigate through */
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :

				$next_link     = get_next_comments_link( __( 'Newer Comments', 'reach' ) );
				$previous_link = get_previous_comments_link( __( 'Older Comments', 'reach' ) );
				?>
				<nav id="comment-nav-above" class="comment-nav pagination cf">
					<h1 class="assistive-text"><?php _e( 'Comment navigation', 'reach' ); ?></h1>
					<ul>        
						<?php if ( strlen( $previous_link ) ) : ?>
							<li class="nav-previous"><?php echo $previous_link ?></li>
						<?php endif ?>
						<?php if ( strlen( $next_link ) ) : ?>
							<li class="nav-next"><?php echo $next_link ?></li>
						<?php endif ?>
					</ul>
				</nav>
			<?php endif; // check for comment navigation ?>

			<ul class="comments-list">
				<?php
					/* Loop through and list the comments. Tell wp_list_comments()
					 * to use reach_comment() to format the comments.
					 * If you want to overload this in a child theme then you can
					 * define reach_comment() and that will be used instead.
					 * See reach_comment() in reach/functions.php for more.
					 */
					wp_list_comments( array( 'callback' => 'reach_comment' ) );
				?>
			</ul>

			<?php
			/* Are there comments to navigate through */
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
			?>
				<nav id="comment-nav-below" class="comment-nav pagination cf">
					<h1 class="assistive-text"><?php _e( 'Comment navigation', 'reach' ); ?></h1>
					<ul>
						<?php if ( strlen( $previous_link ) ) : ?><li class="nav-previous"><?php echo $previous_link ?></li><?php endif ?>
						<?php if ( strlen( $next_link ) ) : ?><li class="nav-next"><?php echo $next_link ?></li><?php endif ?>
					</ul>
				</nav>
			<?php endif; // check for comment navigation ?>         

		</div><!-- .comments-block -->

	<?php endif ?>

</div><!-- #comments -->
