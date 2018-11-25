<?php
/**
 * The template used to display notices.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.3.0
 */

if ( ! isset( $view_args[ 'notices' ] ) ) {
    return;
}

$notices = array_filter( $view_args['notices'] );
$autoclose = (isset($view_args['autoclose'])) ? $view_args['autoclose'] : false;

if ( empty( $notices ) ) {
    return;
}

?>
<div class="pp-notice-wrapper <?php echo ($autoclose) ? 'autoclose' : ''; ?>">
<?php foreach ( $notices as $type => $messages ) : ?>
    <ul class="pp-notices <?php echo esc_attr( $type ) ?>">
        <?php foreach ( $messages as $message ) : ?>
            <li><?php echo $message ?></li>
        <?php endforeach ?>
    </ul><!-- charitable-notice-<?php esc_attr( $type ) ?> -->
<?php endforeach ?>
</div><!-- .charitable-notices -->