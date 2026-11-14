<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://alphasys.com.au/
 * @since      1.0.0
 *
 * @package    Wiki_Links
 * @subpackage Wiki_Links/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wiki_Links
 * @subpackage Wiki_Links/public
 * @author     AlphaSys <junjie@alphasys.com.au>
 */
class Wiki_Links_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wiki_Links_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wiki_Links_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wiki-links-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wiki_Links_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wiki_Links_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wiki-links-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Transform the [[wiki-link]] format to actual links
	 * 
	 * @since 	1.0.0
	 * @param 	string $content	the post's content to be modified
	 * @return	string $content the modified post's content
	 */
	public function apply_wiki_links_on_content( $content ) {

		if ( ( is_single() || is_page() ) && in_the_loop() && is_main_query() ) {

			global $post;

			if ( ! is_post_type_viewable( $post->post_type ) ) { 
				return $content;
			}
			
			// Get all public post types
			$public_post_types = get_post_types( array(
				'public' => true,
				'publicly_queryable' => true
			), 'names', 'or' );

			$excluded_post_types = array(
				'attachment',
				'card',
				'row',
				'__interest',
				'__persona'
			);

			$public_post_types = array_diff( $public_post_types, $excluded_post_types );

			// Process the $post-post_content for Wiki Links markup

			$matched_texts = array();
			$enclosed_strs = array();
			$replacement_text = array();

			if ( in_array( $post->post_type, $public_post_types ) ) {
				$pattern = '/\[\[(.*)\]\]/U';
				preg_match_all( $pattern, $content, $matched_texts );
			}

			if ( !empty( $matched_texts ) ) {
				$enclosed_strs = $matched_texts[1];
				$matched_texts = $matched_texts[0];
			}

			$potential_links_raw = get_field( 'potential_links' );
			$potential_links = array();

			foreach( $potential_links_raw as $potential_link ) {
				$slug = $potential_link['slug'];
				$link = $potential_link['link'];
				$potential_links[$slug] = $link;
			}

			foreach ( $matched_texts as $index => $text_to_replace ) {
				$text_to_search = $enclosed_strs[$index];

				$text_to_search = str_replace( ' ', '-', strtolower( $text_to_search ) );

				// Check the meta first if it the link exists
				if ( array_key_exists( $text_to_search, $potential_links ) ) {
					$replacement_text[$text_to_replace] = $potential_links[$text_to_search];
				} else {
					$link_page = get_page_by_path( $text_to_search, 'OBJECT', $public_post_types );
					$link_path = isset( $link_page ) ? get_permalink( $link_page ) : false;
					
					$link_class = $link_path ? 'has-link' : 'no-link';			
					$link_label = $link_path ? $enclosed_strs[$index] : $text_to_replace;
					$link_style = $link_class === 'no-link' ? 'style="color:red"' : '';
					
					$replacement_text[$text_to_replace] = sprintf( '<a href="%s" class="wiki-link %s" %s>%s</a>', $link_path ? $link_path : '#', $link_class, $link_style, $link_label );
				}
				
			}

			return str_replace( array_keys( $replacement_text ), array_values( $replacement_text ), $content );

		} else {
			return $content;
		}
	}

}
