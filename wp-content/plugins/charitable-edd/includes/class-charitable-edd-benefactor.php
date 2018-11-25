<?php
/**
 * Class responsible for augmenting / decorating the core Charitable_Benefactor class. 
 *
 * @package		Charitable EDD/Classes/Charitable_EDD_Benefactor
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Benefactor' ) ) : 

    /**
     * Charitable_EDD_Benefactor
     *
     * @since 		1.0.0
     */
    class Charitable_EDD_Benefactor extends Charitable_Benefactor {

    	/**
    	 * Composite benefactor record. Contains the combined values of the core record and the EDD benefactor record.
    	 *
    	 * @var 	Object|null 	Null if this is not an EDD benefactor record.
    	 * @access  protected
    	 */
    	protected $benefactor;

    	/**
    	 * Create class object.
    	 * 
    	 * @param 	mixed 	$benefactor
    	 * @access 	public
    	 * @since	1.0.0
    	 */
    	public function __construct( $benefactor ) {
    		if ( ! is_object( $benefactor ) ) {
    			$benefactor = charitable_get_table( 'edd_benefactors' )->get_composite_benefactor_object( $benefactor );
    		}

    		$this->benefactor = $benefactor;
    	}

    	/**
    	 * Returns whether this is an EDD benefactor.  
    	 *
    	 * @return 	boolean
    	 * @access  public
    	 * @since 	1.0.0
    	 */
    	public function is_edd_benefactor() {
    		return is_null( $this->benefactor );
    	}

        /**
         * Returns whether the benefit rule is active. 
         *
         * @return  boolean
         * @access  public
         * @since   1.0.4
         */
        public function is_active() {
        	if ( $this->is_expired ) {
        		return false;
        	}

        	if ( ! $this->benefactor->edd_download_id ) {
        		return true;
        	}

        	return 'publish' == get_post_status( $this->benefactor->edd_download_id );
        }

    	/**
    	 * Return the type of contribution.  
    	 *
    	 * @return 	string
    	 * @access  public
    	 * @since 	1.0.0
    	 */
    	public function get_contribution_description() {
    		if ( $this->benefactor->edd_download_id ) {

    			$type = $this->get_contribution_description_product();

    		}
    		elseif ( $this->benefactor->edd_download_category_id ) {
    			
    			$type = $this->get_contribution_description_product_category();

    		}
    		else {

    			$type = $this->get_contribution_description_global();

    		}

    		return $type;
    	}

    	/**
    	 * Get contribution type when it's for any purchase. 
    	 *
    	 * @return 	string
    	 * @access  public
    	 * @since 	1.0.0
    	 */
    	public function get_contribution_description_global() {
    		if ( $this->benefactor->contribution_amount_is_per_item ) {
    			return sprintf( 
    				_x( '%1$s of every product purchased goes towards %2$s.', 'contribution amount of every product purchased', 'charitable-edd' ), 
    				$this->get_contribution_amount(), 
    				get_the_title( $this->benefactor->campaign_id ) 
    			);
    		}

    		return sprintf( 
    			_x( '%1$s of every purchase goes towards %2$s.', 'contribution amount of every purchase', 'charitable-edd' ), 
    			$this->get_contribution_amount(), 
    			get_the_title( $this->benefactor->campaign_id ) 
    		);
    	}

    	/**
    	 * Get contribution type when it's for purchases of a specific product.
    	 *
    	 * @return 	string
    	 * @access  public
    	 * @since 	1.0.0
    	 */
    	public function get_contribution_description_product() {
    		$download = get_post( $this->benefactor->edd_download_id );

    		if ( ! $download ) {
    			return "";
    		}

    		if ( $this->benefactor->contribution_amount_is_per_item ) {

    			$output = sprintf( 
    				_x( '%1$s of every %2$s purchased goes towards %3$s.', 'Contribution amount for every product purchased', 'charitable-edd' ), 
    				$this->get_contribution_amount(), 
    				$download->post_title, 
    				get_the_title( $this->benefactor->campaign_id ) 
    			);

    		} else { 

    			$output = sprintf( 
    				_x( '%1$s of every purchase with %2$s goes towards %3$s.', 'Contribution amount for every purchase including product', 'charitable-edd' ), 
    				$this->get_contribution_amount(), 
    				$download->post_title, 
    				get_the_title( $this->benefactor->campaign_id ) 
    			);

    		}

    		return $output;
    	}	

    	/**
    	 * Get contribution type when it's for purchases of a specific product category.
    	 *
    	 * @return 	string
    	 * @access  public
    	 * @since 	1.0.0
    	 */
    	public function get_contribution_description_product_category() {
    		$category = get_term( $this->benefactor->edd_download_category_id, 'download_category' );

    		if ( $this->benefactor->contribution_amount_is_per_item ) {
    			return sprintf( 
    				_x( '%1$s of every item in %2$s category goes towards %3$s.', 'Contribution amount for every item in category', 'charitable-edd' ), 
    				$this->get_contribution_amount(), 
    				$category->name,
    				get_the_title( $this->benefactor->campaign_id ) 
    			);
    		}
    		
    		return sprintf( 
    			_x( '%1$s of every purchase with item in %2$s category goes towards %3$s.', 'Contribution amount for every item in category', 'charitable-edd' ), 
    			$this->get_contribution_amount(), 
    			$category->name,
    			get_the_title( $this->benefactor->campaign_id ) 
    		);
    	}		

    	/**
    	 * Return an array of the benefits provided by the benefactor for the set of downloads.	
    	 *
    	 * @param 	array 	$downloads
    	 * @return  float[]
    	 * @access  public
    	 * @since   1.0.0
    	 */ 
    	public function get_benefits_for_downloads( $downloads ) {

    		if ( $this->benefit_is_per_cart() ) {

    			return array( 'cart' => array( 
    				'campaign_id' 	=> $this->benefactor->campaign_id,
    				'contribution' 	=> $this->calculate_line_item_fixed_contribution() 
    			) );

    		}	

    		return array_reduce( $downloads, array( $this, 'get_line_item_contribution' ), array() );
    	}

        /**
         * Return the benefit amount of a line item (download) based on a single benefit record. 
         *
         * @param 	array 	$download
         * @return  float 
         * @access  public
         * @since   1.0.0
         */
        public function calculate_line_item_contribution( $download ) {
            if ( $this->benefactor->contribution_amount_is_percentage ) {
                return $this->calculate_line_item_percent_contribution( $download[ 'item_price' ], $download[ 'quantity' ] );
            }

            return $this->calculate_line_item_fixed_contribution( $download[ 'quantity' ] );
        }

    	/**
    	 * Receives a download and calculates its contribution amount, adding this to the benefits array. 
    	 *
    	 * This method is designed to be used by array_reduce to process a group of downloads. It takes 
    	 * into account the quantity of a download being purchased.
    	 * 
    	 * @param 	float[] $benefits
    	 * @param 	array 	$download 
    	 * @return  float[]	
    	 * @access  private
    	 * @since   1.0.0
    	 */
    	private function get_line_item_contribution( $benefits, $download ) {
    		if ( ! $this->benefactor_applies_to_download( $download ) ) {
    			return $benefits;
    		}
    	
            $benefits[ $download['key'] ] = array(
            	'campaign_id'  => $this->benefactor->campaign_id,
            	'quantity'     => $download['quantity'],
            	'price'        => $download['item_price'],
            	'contribution' => $this->calculate_line_item_contribution( $download ),
            );

            return $benefits;
    	}

        /**
         * Returns whether the given benefactor applies to the download. 
         *
         * @return  boolean
         * @access  private
         * @since   1.0.0
         */
        private function benefactor_applies_to_download( $download ) {
            return $this->benefactor->edd_is_global_contribution 
                || $download[ 'id' ] == $this->benefactor->edd_download_id 
                || in_array( $this->benefactor->edd_download_category_id, $download[ 'category_ids' ] );
        }	
    }

endif; // End class_exists check
