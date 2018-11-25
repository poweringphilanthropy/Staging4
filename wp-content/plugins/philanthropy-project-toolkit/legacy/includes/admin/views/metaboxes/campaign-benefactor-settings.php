<?php 
/**
 * Renders the EDD part of the campaign benefactors form.
 *
 * @since       1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a 
 */

$benefactor = isset( $view_args[ 'benefactor' ] ) ? $view_args[ 'benefactor' ] : null;
$type                 = isset( $view_args['type'] ) ? $view_args[ 'type' ] : 'download';
$disable              = isset( $view_args['disable'] ) ? boolval($view_args[ 'disable' ]) : false;

if ( is_null( $benefactor ) ) {
    $default_args = array(
        'index'                             => '_0',       
        'edd_download_id'                   => '',
        'edd_download_category_id'          => '',
        'edd_is_global_contribution'        => 1
    );

    $args = array_merge( $default_args, $view_args );
}
else {
    $args = array(
        'index'                             => $benefactor->campaign_benefactor_id,
        'edd_download_id'                   => $benefactor->edd_download_id,
        'edd_download_category_id'          => $benefactor->edd_download_category_id,
        'edd_is_global_contribution'        => $benefactor->edd_is_global_contribution
    );  
}

$id_base = 'campaign_benefactor_' . $args[ 'index' ];
$name_base = '_campaign_benefactor[' . $args[ 'index' ] . ']';
$disable_attr = ($disable) ? 'disabled="disabled"' : '';

$download_args = array( 
    'post_type' => 'download', 
    'posts_per_page' => -1, 
    'post_status' => array( 'draft', 'pending', 'publish' ),
);
if( $type != 'download' ){
    $download_args['tax_query'] = array(
        array(
            'taxonomy' => 'download_category',
            'field' => 'slug',
            'terms' => $type
        )
    );
}

$downloads              = get_posts( $download_args );
// $download_categories    = get_terms( 'download_category', array( 'hide_empty' => false, 'fields' => 'id=>name' ) );
$download_categories    = false;

?>  
<p><label for="<?php echo $id_base ?>_edd"><?php _e( 'When You Purchase:', 'charitable-edd' ) ?></label></p>
<select id="<?php echo $id_base ?>_edd" name="<?php echo $name_base ?>[edd]" <?php echo $disable_attr; ?>>
    <option value="global" <?php selected( $args['edd_is_global_contribution'] ) ?>><?php _e( 'Any Download', 'charitable-edd' ) ?></option>
    <?php 
    if ( count( $download_categories ) ) : 
        ?>  
        <optgroup label="<?php _e( 'Downloads in these categories', 'charitable-edd' ) ?>">
            <?php foreach ( $download_categories as $category_id => $name ) : ?>
                <option value="category-<?php echo $category_id ?>" <?php selected( $category_id, $args['edd_download_category_id'] ) ?>><?php echo $name ?></option>
            <?php endforeach ?>
        </optgroup>
        <?php 
    endif;

    if ( count( $downloads ) ) : 
        ?>
        <optgroup label="<?php _e( 'One of these downloads', 'charitable-edd' ) ?>">
            <?php foreach ( $downloads as $download ) : ?>
                <option value="download-<?php echo $download->ID ?>" <?php selected( $download->ID, $args['edd_download_id'] ) ?>><?php echo $download->post_title ?></option>
            <?php endforeach ?>
        </optgroup>
        <?php 
    endif;
    ?>
</select>