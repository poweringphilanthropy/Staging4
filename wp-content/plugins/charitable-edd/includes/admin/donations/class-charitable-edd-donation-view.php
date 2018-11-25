<?php
/**
 * The class that is responsible for customizing the donation details page.
 *
 * @package   Charitable EDD/Classes/Charitable_EDD_Donation_View
 * @version   1.1.2
 * @author    Eric Daams
 * @copyright Copyright (c) 2017, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Donation_View' ) ) :

	/**
	 * Charitable_EDD_Donation_View
	 *
	 * @since 1.1.2
	 */
	class Charitable_EDD_Donation_View {

		/**
		 * The donation ID.
		 *
		 * @since 1.1.2
		 *
		 * @var   int
		 */
		protected $donation_id;

		/**
		 * Set of EDD donations for the current donation.
		 *
		 * @since 1.1.2
		 *
		 * @var   array
		 */
		protected $edd_donations;

		/**
		 * Create class object.
		 *
		 * @since 1.1.2
		 *
		 * @param int $donation_id
		 */
		public function __construct( $donation_id ) {
			$this->donation_id   = $donation_id;
			$this->edd_donations = get_post_meta( $this->donation_id, 'donation_from_edd_payment_log', true );

			if ( is_array( $this->edd_donations ) ) {
				$this->setup_styles();

				add_filter( 'charitable_donation_details_table_campaign_donation_campaign', array( $this, 'show_campaign_name' ) );
				add_filter( 'charitable_donation_details_table_campaign_donation_amount', array( $this, 'show_donation_amount' ) );
			}
		}

		/**
		 * Add basic inline style block.
		 *
		 * @since  1.5.0
		 *
		 * @return void
		 */
		public function setup_styles() {
			echo '<style>#charitable-donation-overview-metabox .donation-source { color: #999; }</style>';
		}

		/**
		 * Display a campaign name along with the source of the donation.
		 *
		 * @since  1.1.2
		 *
		 * @return string
		 */
		public function show_campaign_name() {
			$edd_campaign_donation = current( $this->edd_donations );
			$ret = get_the_title( $edd_campaign_donation['campaign_id'] );

			if ( ! $edd_campaign_donation['edd_fee'] ) {
				$ret .= '<p class="donation-source">';

				if(isset($edd_campaign_donation['is_shipping'])){
					$ret .= 'Shipping charges for -- '.get_the_title( $edd_campaign_donation['download_id'] );
				} 
				elseif ( array_key_exists( 'price_id', $edd_campaign_donation ) ) {
					$ret .= sprintf( __( 'From purchase of %s - %s', 'charitable-edd' ),
						get_the_title( $edd_campaign_donation['download_id'] ),
						edd_get_price_option_name( $edd_campaign_donation['download_id'], $edd_campaign_donation['price_id'] )
					);
				} else {
					$ret .= sprintf( __( 'From purchase of %s', 'charitable-edd' ),
						get_the_title( $edd_campaign_donation['download_id'] )
					);
				}

				$ret .= '</p>';
			}

			return $ret;
		}
		
		/**
		 * Display the amount donated.
		 *
		 * @since  1.1.2
		 *
		 * @return string
		 */
		public function show_donation_amount() {
			/* We use array_shift here to remove the item from the array. */
			$edd_campaign_donation = array_shift( $this->edd_donations );

			return charitable_format_money( $edd_campaign_donation['amount'] );
		}
	}

endif;
