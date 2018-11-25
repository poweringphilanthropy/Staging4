<?php
/**
 * The class that retrieves connected downloads for the given campaign.
 *
 * @package     Charitable EDD/Classes/Charitable_EDD_Campaign
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Campaign' ) ) :

	/**
	 * Charitable_EDD_Campaign
	 *
	 * @since       1.0.0
	 */
	class Charitable_EDD_Campaign {

		/**
		 * @var WP_Post The WP_Post object associated with this campaign.
		 */
		private $post;

		/**
		 * The benefactors for this campaign.
		 *
		 * @var     array
		 * @access  private
		 */
		private $benefactors;

		/**
		 * The downloads connected to this campaign.
		 *
		 * @var     array
		 * @access  private
		 */
		private $downloads;

		/**
		 * The related Charitable_Campaign object.
		 *
		 * @var     Charitable_Campaign
		 * @access  private
		 */
		private $campaign;

		/**
		 * Class constructor.
		 *
		 * @param   mixed   $post       The post ID or WP_Post object for this this campaign.
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $post ) {
			if ( ! is_a( $post, 'WP_Post' ) ) {
				$post = get_post( $post );
			}

			$this->post = $post;
		}

		/**
		 * Get the Charitable_Campaign object for this campaign.
		 *
		 * @return  Charitable_Campaign
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_campaign() {
			if ( ! isset( $this->campaign ) ) {
				$this->campaign = new Charitable_Campaign( $this->post );
			}

			return $this->campaign;
		}

		/**
		 * Return the benefactory relationships for this campaign.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_benefactors() {
			if ( ! isset( $this->benefactors ) ) {
				$this->benefactors = charitable()->get_db_table( 'edd_benefactors' )->get_campaign_benefactors( $this->post->ID );
			}

			return $this->benefactors;
		}

		/**
		 * Returns the downloads that are connected to this campaign.
		 *
		 * @param   array   $args   Optional array of arguments to pass to get_posts().
		 * @return  false|WP_POST[] Returns false if there are no connected downloads. Otherwise an array of WP_Post objects.
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_connected_downloads( $args = array() ) {
			$cache_key = md5( $this->post->ID . '-' . serialize( $args ) );

			$downloads = wp_cache_get( $cache_key, 'charitable_campaign_connected_downloads' );

			if ( false === $downloads ) {

				$download_ids           = array();
				$download_categories    = array();
				$all_downloads          = false;
				$benefactors            = $this->get_benefactors();

				if ( empty( $benefactors ) ) {
					return false;
				}

				foreach ( $benefactors as $benefactor ) {
					if ( $benefactor->edd_download_id ) {

						$download_ids[] = $benefactor->edd_download_id;

					} elseif ( $benefactor->edd_download_category_id ) {

						$download_categories[] = $benefactor->edd_download_category_id;

					} else {

						$all_downloads = true;
						break;

					}
				}

				$query_args = array(
					'post_type'      => 'download',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				);

				if ( false === $all_downloads ) {

					if ( ! empty( $download_ids ) ) {

						$query_args['post__in'] = $download_ids;

					}

					if ( ! empty( $download_categories ) ) {

						$query_args['tax_query'] = array(
							array(
								'taxonomy'      => 'download_category',
								'field'         => 'id',
								'terms'         => $download_categories,
							),
						);
					}
				}

				$query_args = wp_parse_args( $args, $query_args );

				$downloads = get_posts( $query_args );

				foreach ( $downloads as $key => $download ) {

					$download_benefactors = array();

					foreach ( $benefactors as $benefactor ) {

						if ( $this->benefactor_applies_to_download( $download->ID, $benefactor ) ) {

							$download_benefactors[] = $benefactor;

						}
					}

					$downloads[ $key ]->benefactors = $download_benefactors;
				}

				wp_cache_set( $cache_key, $downloads, 'charitable_campaign_connected_downloads' );
			}

			return $downloads;
		}

		/**
		 * Returns true if the benefactor rule applies to the given download.
		 *
		 * @param   int $download_id
		 * @param   Object $benefactor
		 * @return  boolean
		 * @access  private
		 * @since   1.0.0
		 */
		private function benefactor_applies_to_download( $download_id, $benefactor ) {
			if ( $benefactor->edd_is_global_contribution ) {
				return true;
			}

			if ( $download_id == $benefactor->edd_download_id ) {
				return true;
			}

			return $benefactor->edd_download_category_id && has_term( $benefactor->edd_download_category_id, 'download_category', $download_id );
		}
	}

endif; // End class_exists check
