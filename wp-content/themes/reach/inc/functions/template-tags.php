<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package 	Reach
 * @category 	Functions
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! function_exists( 'reach_paging_nav' ) ) :

	/**
	 * Display navigation to next/previous set of posts when applicable.
	 *
	 * @param 	string $older_posts Text to display for older posts button.
	 * @param 	string $newer_posts Text to display for newer posts button.
	 * @return 	void
	 * @since 	1.0.0
	 */
	function reach_paging_nav( $older_posts = '', $newer_posts = '' ) {

		/* Don't print empty markup if there's only one page. */
		if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
			return;
		}

		if ( empty( $older_posts ) ) {
			$older_posts = __( 'Older Posts', 'reach' );
		}

		if ( empty( $newer_posts ) ) {
			$newer_posts = __( 'Newer Posts', 'reach' );
		}

		?>
		<nav class="navigation paging-navigation pagination" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'reach' ) ?></h1>
			<ul>
				<?php if ( get_next_posts_link() ) : ?>
					<li class="nav-previous"><?php next_posts_link( $older_posts ) ?></li>
				<?php endif; ?>

				<?php if ( get_previous_posts_link() ) : ?>
					<li class="nav-next"><?php previous_posts_link( $newer_posts ) ?></li>
				<?php endif; ?>
			</ul>
		</nav><!-- .navigation -->
		<?php
	}

endif;

if ( ! function_exists( 'reach_post_nav' ) ) :

	/**
	 * Display navigation to next/previous post when applicable.
	 *
	 * @return 	void
	 * @since 	1.0.0
	 */
	function reach_post_nav() {

		/* Don't print empty markup if there's nowhere to navigate. */
		$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous ) {
			return;
		}
		?>
		<nav class="navigation post-navigation" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'reach' ); ?></h1>
			<div class="nav-links">
				<?php
					previous_post_link(
						'<div class="nav-previous">%link</div>',
						_x( '<span class="meta-nav">&larr;</span>&nbsp;%title', 'Previous post link', 'reach' )
					);
					next_post_link(
						'<div class="nav-next">%link</div>',
						_x( '%title&nbsp;<span class="meta-nav">&rarr;</span>', 'Next post link', 'reach' )
					);
				?>
			</div><!-- .nav-links -->
		</nav><!-- .navigation -->
		<?php
	}

endif;

if ( ! function_exists( 'reach_site_identity' ) ) :

	/**
	 * Displays the site identity section.
	 *
	 * This may include the logo, site title and/or site tagline.
	 *
	 * @param 	boolean $echo
	 * @return 	string
	 * @since 	1.0.0
	 */
	function reach_site_identity( $echo = true ) {
		$output 	  = '';
		$home_url 	  = home_url();
		$site_title   = get_bloginfo( 'name' );
		$hide_title   = (bool) get_theme_mod( 'hide_site_title' );
		$hide_tagline = (bool) get_theme_mod( 'hide_site_tagline' );

		if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) {
			$output .= get_custom_logo();
		}

		$tag = is_front_page() ? 'h1' : 'div';

		$output .= apply_filters( 'reach_site_title', sprintf( '<%s class="site-title %s"><a href="%s" title="%s">%s</a></%s>',
			$tag,
			$hide_title ? 'hidden' : '',
			$home_url,
			__( 'Go to homepage', 'reach' ),
			$site_title,
			$tag
		) );

		$output .= apply_filters( 'reach_site_tagline', sprintf( '<div class="site-tagline %s">%s</div>',
			$hide_tagline ? 'hidden' : '',
			get_bloginfo( 'description' )
		) );

		if ( ! $echo ) {
			return $output;
		}

		echo $output;
	}

endif;

if ( ! function_exists( 'reach_post_header' ) ) :

	/**
	 * Displays the post title.
	 *
	 * @param 	bool 		$echo
	 * @return 	string|void
	 * @since 	2.0.0
	 */
	function reach_post_header( $echo = true ) {
		global $post;

		$post_format = get_post_format();

		if ( ! strlen( get_the_title() ) ) {
			return '';
		}

		/* Set up the wrapper */
		if ( is_single() ) {
			$wrapper_start = '<h1 class="post-title">';
			$wrapper_end   = '</h1>';
		} else {
			$wrapper_start = '<h2 class="post-title">';
			$wrapper_end   = '</h2>';
		}

		// Link posts have a different title setup
		if ( 'link' == $post_format ) {
			$title = reach_link_format_title( false );
		} elseif ( 'status' == $post_format ) {
			$title = get_the_content();
		} else {
			$title = sprintf( '<a href="%s" title="%s">%s</a>',
				get_permalink(),
				the_title_attribute( array( 'echo' => false ) ),
				get_the_title()
			);
		}

		$output = $wrapper_start . $title . $wrapper_end;

		if ( false === $echo ) {
			return $output;
		}

		echo $output;
	}

endif;

if ( ! function_exists( 'reach_link_format_the_content' ) ) :

	/**
	 * Prints the content for a link post.
	 *
	 * @param 	string $more_link_text
	 * @param 	string $stripteaser
	 * @param 	bool   $echo
	 * @return 	void|string
	 * @since 	1.0.0
	 */
	function reach_link_format_the_content( $more_link_text = null, $stripteaser = false, $echo = true ) {
		$content = get_the_content( $more_link_text, $stripteaser );
		$content = reach_strip_anchors( $content, 1 );
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		if ( false === $echo ) {
			return $content;
		}

		echo $content;
	}

endif;

if ( ! function_exists( 'reach_link_format_title' ) ) :

	/**
	 * Returns or prints the title for a link post.
	 *
	 * @uses 	reach_link_format_title
	 *
	 * @param 	bool $echo
	 * @return 	string
	 * @since 	1.0.0
	 */
	function reach_link_format_title( $echo = true ) {
		global $post;

		$anchors = reach_get_first_anchor( get_post_field( 'post_content', $post ) );

		/* If there are no anchors, just return the normal title. */
		if ( empty( $anchors ) ) {

			return sprintf( '<a href="%s" title="%s">%s</a>',
				get_permalink(),
				the_title_attribute( array( 'post' => $post ) ),
				get_the_title( $post )
			);

		}

		$anchor = apply_filters( 'reach_link_format_title', $anchors[0] );

		if ( false === $echo ) {
			return $anchor;
		}

		echo $anchor;
	}

endif;

if ( ! function_exists( 'reach_fullwidth_video' ) ) :

	/**
	 * Wraps the video in the fit-video class to ensure it is displayed at fullwidth.
	 *
	 * @param 	string $video
	 * @return 	string
	 * @since 	1.0.0
	 */
	function reach_fullwidth_video( $video ) {
		return sprintf( '<div class="fit-video">%s</div>', $video );
	}

endif;

if ( ! function_exists( 'reach_author_edit_profile_link' ) ) :

	/**
	 * Display a link to edit your profile when you are logged in and viewing your author profile.
	 *
	 * @param 	int $author_id
	 * @return 	string
	 * @since 	1.0.0
	 */
	function reach_author_edit_profile_link( $author_id ) {
		if ( ! reach_has_charitable() ) {
			return '';
		}

		if ( get_current_user_id() != $author_id ) {
			return '';
		}

		$profile_page = charitable_get_permalink( 'profile_page' );

		if ( ! $profile_page ) {
			return '';
		}

		return sprintf( '<a href="%s" title="%s" class="button">%s</a>',
			esc_url( $profile_page ),
			esc_attr__( 'Edit your profile', 'reach' ),
			__( 'Edit Profile', 'reach' )
		);
	}

endif;
