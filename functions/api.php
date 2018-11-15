<?php
/**
 * All custom wp-json API endpoints should be defined
 * within this file.
 */
if ( ! class_exists( 'UCF_Today_Custom_API' ) ) {
    class UCF_Today_Custom_API extends WP_REST_Controller {
        /**
         * Registers the rest routes for the ucf_news api
         * @since 2.8.0
         * @author Jim Barnes
         */
        public static function register_rest_routes() {
            $root    = 'ucf-news';
            $version = 'v1';

            register_rest_route( "{$root}/{$version}", "/external-stories", array(
                array(
                    'methods'              => WP_REST_Server::READABLE,
                    'callback'             => array( 'UCF_Today_Custom_API', 'get_external_stories' ),
                    'permissions_callback' => array( 'UCF_Today_Custom_API', 'get_permissions' ),
                    'args'                 => array( 'UCF_Today_Custom_API', 'get_external_story_args' )
                )
			) );

			register_rest_route( "{$root}/{$version}", "/gmucf-email-options", array(
                array(
                    'methods'              => WP_REST_Server::READABLE,
                    'callback'             => array( 'UCF_Today_Custom_API', 'get_gmucf_email_options' ),
                    'permissions_callback' => array( 'UCF_Today_Custom_API', 'get_permissions' )
                )
            ) );
        }

        /**
         * Gets the external stories
         * @since 2.8.0
         * @author Jim Barnes
         * @param WP_REST_Request $request | Contains GET params
         * @return WP_REST_Response
         */
        public static function get_external_stories( $request ) {
            // Handle args and set defaults
            $search     = $request['search'];
            $source     = $request['source'];
            $limit      = $request['limit'] ? $request['limit'] : 10;
            $offset     = $request['offset'] ? $request['offset'] : 0;
            $categories = $request['categories'];

            // Initialize out return value
            $retval = array();

            // Initialize and set defaults on argument array
            $args = array(
                'post_type'      => 'externalstory',
                'posts_per_page' => $limit,
                'offset'         => $offset
            );

            // Add search if it's set
            if ( $search ) {
                $args['s'] = $search;
            }

            // Add source meta query if it's set
            if ( $source ) {
                $sources = explode( ',', $source );

                $args['meta_query'] = array();

                foreach ( $sources as $source ) {
                    $args['meta_query'][] = array(
                        'key'   => 'externalstory_source',
                        'value' => $source
                    );
                }

                if ( count( $args['meta_query'] ) > 1 ) {
                    $args['meta_query']['relation'] = 'OR';
                }
            }

            if ( $categories ) {
                $args['category_name'] = $categories;
            }

            $posts = get_posts( $args );

            foreach( $posts as $post ) {
                $data     = self::prepare_external_story_for_response( $post, $request );
                $retval[] = $data;
            }

            return new WP_REST_Response( $retval, 200 );
        }

        /**
         * Formats the external story for response
         * @since 2.8.0
         * @author Jim Barnes
         * @param WP_Post $post The post
         * @param WP_REST_Request $request The request object
         * @return array A serializable array.
         */
        private static function prepare_external_story_for_response( $post, $request ) {
            // Prepare the return value format
            $retval = array(
                'title'        => '',
                'link_text'    => '',
                'description'  => '',
                'url'          => '',
                'source'       => '',
                'publish_date' => '',
                'categories'   => array()
            );

            $retval['title']        = $post->post_title;
            $retval['link_text']    = get_post_meta( $post->ID, 'externalstory_text', true );
            $retval['description']  = get_post_meta( $post->ID, 'externalstory_description', true );
            $retval['url']          = get_post_meta( $post->ID, 'externalstory_url', true );
            $retval['source']       = get_post_meta( $post->ID, 'externalstory_source', true );
            $retval['publish_date'] = $post->post_date;
            $retval['categories']   = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );

            return $retval;
        }

        /**
         * Gets the default permissions
         * @since 2.8.0
         * @author Jim Barnes
         */
        public static function get_permissions() {
            return true;
        }

        /**
         * Gets the allowable args for external stories
         * @since 2.8.0
         * @author Jim Barnes
         */
        public static function get_external_story_args() {
            return array(
                array(
                    'search' => array(
                        'default'           => false,
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'source' => array(
                        'default'           => false,
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'limit' => array(
                        'default'           => 10,
                        'sanitize_callback' => 'absint'
                    ),
                    'offset' => array(
                        'default'           => 0,
                        'sanitize_callback' => 'absint'
                    ),
                    'categories' => array(
                        'default'           => false,
                        'sanitize_callback' => 'sanitize_text_field'
                    )
                )
            );
		}

		/**
         * Gets the GMUCF email options
         * @since 2.9.0
         * @author Cadie
         * @param WP_REST_Request $request | Contains GET params
         * @return WP_REST_Response
         */
        public static function get_gmucf_email_options( $request ) {

            $retval = array(
                'send_date'        => '',
				'social_share'     => '',
				'top_stories'      => '',
				'featured_stories' => '',
				'spotlights'       => ''
			);

			$retval['send_date']        = get_field( 'gmucf_email_send_date', 'option' );
			$retval['social_share']     = get_field( 'gmucf_show_social_share_buttons', 'option' );

			if ( have_rows( 'gmucf_email_content', 'option' ) ) :

				while ( have_rows( 'gmucf_email_content', 'option' ) ) : the_row();

					if ( get_row_layout() == 'gmucf_top_story' ) :

						$retval['top_stories'][] = get_sub_field( 'gmucf_story' );

					elseif ( get_row_layout() == 'gmucf_featured_story' ) :

						$retval['featured_stories'][] = get_sub_field( 'gmucf_story' );

					elseif ( get_row_layout() == 'gmucf_spotlight' ) :

						$retval['spotlights'][] = get_sub_field( 'gmucf_spotlight_image' );

					endif;

				endwhile;

			endif;

			// $retval['top_stories']      = gmucf_stories_default_values( get_field( 'gmucf_top_stories', 'option' ) );
			// $retval['featured_stories'] = gmucf_stories_default_values( get_field( 'gmucf_featured_stories', 'option' ) );
			// $retval['spotlights']       = get_field( 'gmucf_spotlights', 'option' );

            return new WP_REST_Response( $retval, 200 );
        }
    }
}
