<?php

if ( ! function_exists( 'reach_comment_form_default_fields' ) ) :

	/**
	 * Customize comment form default fields
	 *
	 * @uses 	comment_form_field_comment filter
	 *
	 * @return 	string
	 * @since 	1.0.0
	 */
	function reach_comment_form_default_fields( $fields ) {
		ob_start();
?>		
		<p class="comment-text-input required" tabindex="1">
			<label class="screen-reader-text" for="author"><?php _e( 'Name', 'reach' ) ?></label>
			<input type="text" name="author" id="commenter_name" placeholder="<?php esc_attr_e( 'Name', 'reach' ) ?> *" required />
		</p>		
		<p class="comment-text-input last" tabindex="2">
			<label class="screen-reader-text" for="url"><?php _e( 'Website', 'reach' ) ?></label>
			<input type="text" name="url" id="commenter_url" placeholder="<?php esc_attr_e( 'Website', 'reach' ) ?>" />
		</p>
		<p class="comment-text-input fullwidth required" tabindex="3">
			<label class="screen-reader-text" for="email"><?php _e( 'Email', 'reach' ) ?></label>
			<input type="email" name="email" id="commenter_email" placeholder="<?php esc_attr_e( 'Email', 'reach' ) ?> *" required />
		</p>
<?php
		return ob_get_clean();
	}

endif;

add_filter( 'comment_form_default_fields', 'reach_comment_form_default_fields', 10, 2 );

if ( ! function_exists( 'reach_comment_form_field_comment' ) ) :

	/**
	 * The comment field.
	 *
	 * @return 	string
	 * @since 	1.0.0
	 */
	function reach_comment_form_field_comment() {
		ob_start();
?>		
		<p class="comment-form-comment">
			<label class="screen-reader-text" for="comment"><?php _e( 'Leave your comment', 'reach' ) ?></label>
			<textarea id="comment" name="comment" cols="45" rows="8" aria-required="true" placeholder="<?php esc_attr_e( 'Leave your comment', 'reach' ) ?> *"></textarea>
		</p>
<?php
		return ob_get_clean();
	}

endif;

if ( ! function_exists( 'reach_cancel_comment_reply_link' ) ) :

	/**
	 * Filters the comment reply close link.
	 *
	 * @param 	string $html
	 * @return 	string
	 * @since 	1.0
	 */
	function reach_cancel_comment_reply_link( $html ) {
		return substr_replace( $html, 'class="with-icon" data-icon="&#xf057;"', 3, 0 );
	}

endif;

add_filter( 'cancel_comment_reply_link', 'reach_cancel_comment_reply_link' );

if ( ! function_exists( 'reach_comment' ) ) :

	/**
	 * Customize comment output.
	 *
	 * @param 	stdClass 	$comment
	 * @param 	array 		$args
	 * @param 	int 		$depth
	 * @return 	string
	 * @since 	1.0.0
	 */
	function reach_comment( $comment, $args, $depth ) {

		$GLOBALS['comment'] = $comment;

		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
		?>

		<li class="pingback">
			<p><?php _e( 'Pingback:', 'reach' ); ?> <?php comment_author_link() ?></p>
			<?php edit_comment_link( __( 'Edit', 'reach' ), '<p class="comment_meta">', '</p>' ); ?>
		
		<?php
				break;
			default :

				$comment_reply_args = array(
					'reply_text' => sprintf( '<i class="icon-pencil"></i> %s', _x( 'Reply', 'reply to comment' , 'reach' ) ),
					'depth' 	 => $depth,
					'max_depth'  => $args['max_depth'],
				);

				$comment_reply_args = array_merge( $args, $comment_reply_args );
		?>

		<li <?php comment_class( get_option( 'show_avatars' ) ? 'avatars' : 'no-avatars' ) ?> id="li-comment-<?php comment_ID(); ?>">
			<?php echo get_avatar( $comment, 50 ) ?>
			<div class="comment-details">
				<?php if ( reach_comment_is_by_author( $comment ) ) : ?>
					<small class="post-author with-icon alignright"><i class="icon-star"></i><?php _e( 'Author', 'reach' ) ?></small>
				<?php endif ?>
				<h6 class="comment-author vcard"><?php comment_author_link() ?></h6>				
				<div class="comment-text"><?php comment_text() ?></div>
				<p class="comment-meta">
					<span class="comment-date"><i class="icon-comment"></i><?php printf(
						_x( '%s at %s', 'date at time', 'reach' ),
						get_comment_date(),
						get_comment_time()
					) ?></span>
					<span class="comment-reply floatright"><?php comment_reply_link( $comment_reply_args ) ?></span>
				</p><!-- .comment-meta -->
			</div><!-- .comment-details -->

		<?php
				break;
		endswitch;
	}

endif;

if ( ! function_exists( 'reach_comment_is_by_author' ) ) :

	/**
	 * Return whether the comment was created by the post author.
	 *
	 * @param 	object $comment
	 * @return 	bool
	 * @since 	1.0
	 */
	function reach_comment_is_by_author( $comment ) {
		global $post;

		return isset( $comment->user_id ) && $comment->user_id == $post->post_author;
	}

endif;
