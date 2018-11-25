<?php
/**
 * G4G_Template
 * Custom template path
 * @since       1.0.0
 */
 
 if ( class_exists( 'PP_Template' ) ){
class G4G_Template extends PP_Template {      

    

    /**
     * Locate the template file of the highest priority.
     *
     * @uses    locate_template()
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function locate_template() {
        /* Template options are first checked in the theme/child theme using locate_template. */
        $template = locate_template( $this->get_theme_template_options(), false );

        /* No templates found in the theme/child theme, so use our template. */
        if ( ! $template && file_exists( g4g()->plugin_path() . '/templates/' . $this->template_names[0] )  ) {
            $template = g4g()->plugin_path() . '/templates/' . $this->template_names[0];
        }

        /* No templates found in the theme/child theme, so use the plugin's default template. */
        if ( ! $template ) {
            $template = $this->base_template_path . $this->template_names[0];
        }

        return apply_filters( 'pp_locate_template', $template, $this->template_names );
    }

}}

/**
 * Helper function to load our custom charitable template under /templates/charitable
 * @param  [type] $template_name [description]
 * @param  array  $args          [description]
 * @return [type]                [description]
 */
function g4g_template( $template_name, array $args = array() ) {
    if ( empty( $args ) ) {
        $template = new G4G_Template( $template_name );
    } else {
        $template = new G4G_Template( $template_name, false );
        $template->set_view_args( $args );
        $template->render();
    }

    return $template;
}

/**
 * Return the template path if the template exists. Otherwise, return default.
 *
 * @param   string|string[] $template
 * @return  string The template path if the template exists. Otherwise, return default.
 * @since   1.0.0
 */
function g4g_get_template_path( $template, $default = '' ) {
    $t = new G4G_Template( $template, false );
    $path = $t->locate_template();

    if ( ! file_exists( $path ) ) {
        $path = $default;
    }

    return $path;
}