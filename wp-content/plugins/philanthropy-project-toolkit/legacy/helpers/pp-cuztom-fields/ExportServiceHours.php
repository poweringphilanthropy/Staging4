<?php

namespace Gizburdt\Cuztom\Fields;

use Gizburdt\Cuztom\Cuztom;
use Gizburdt\Cuztom\Support\Guard;

Guard::directAccess();

class ExportServiceHours extends Field
{
    /**
     * Fillables.
     * @var mixed
     */
    public $css_class      = 'cuztom-export-service-hours';
    public $cell_css_class = 'cuztom-export-service-hours';

    public function outputInput($value = null, $view = null) {

        $post_id = get_term_meta( $this->object, '_dashboard_page', true );

        $url = add_query_arg( array(
            'taxonomy' => 'campaign_group',
            'tag_ID' => $this->object,
            'post_type' => 'campaign',
            'pp_export_service_hours' => 'dashboard',
            'dashboard_id' => $post_id
        ), admin_url( 'term.php' ) );
            
        return ' <a class="button button-primary" href="'.wp_nonce_url( $url, 'export-service-hours', 'pp_export_nonce').'">Export Service Hours</a>';
    }
}
