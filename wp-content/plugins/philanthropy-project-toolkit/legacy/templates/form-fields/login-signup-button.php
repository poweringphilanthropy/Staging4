<?php
/**
 * The template used to display ogin signup form fields.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'field' ] ) ) {
    return;
}

$form           = $view_args[ 'form' ];
$field          = $view_args[ 'field' ];
$classes        = esc_attr( $view_args[ 'classes' ] );
$field_type     = isset( $field[ 'type' ] ) ? $field[ 'type' ] : 'text';
$is_required    = isset( $field[ 'required' ] ) ? $field[ 'required' ] : false;
$value          = isset( $field[ 'value' ] ) ? $field[ 'value' ] : '';

?>
<div class="pp-login-signup-button">
    <div class="charitable-form-field fullwidth">
         <?php if ( isset( $field['label'] ) ) : ?>
            <p><?php echo $field['label'] ?></p>
        <?php endif ?>
    </div>

    <div class="charitable-form-field odd">

        <div class="messages"></div>

        <div class="charitable-form-field charitable-form-field-text required-field fullwidth">
            <label for="charitable_field_user_login">
                Username <abbr class="required" title="required">*</abbr>
            </label>
            <input id="charitable_field_user_login" type="text" name="user_login" value="" placeholder="Username">
        </div>

        <div class="charitable-form-field charitable-form-field-text required-field fullwidth">
            <label for="charitable_field_password">
                Password <abbr class="required" title="required">*</abbr>
            </label>
            <input id="charitable_field_password" type="password" name="password" value="" placeholder="Your password">
        </div>

        <div class="charitable-form-field charitable-form-field-text required-field fullwidth">
            <a href="#" id="login-button-on-create-campaign" class="button" data-nonce="<?php echo wp_create_nonce( 'login_on_create_campaign' ); ?>">Login</a>
        </div>
    </div>
    <div id="charitable_field_login_button" class="charitable-form-field even">
        <div class="container-signup-button-on-create-campaign">
            <p>Don't have account? <a href="#" class="your-account-signup">Sign Up</a></p>
        </div>
    </div>
</div>