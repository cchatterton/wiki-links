<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://alphasys.com.au/
 * @since      1.0.0
 *
 * @package    Wiki_Links
 * @subpackage Wiki_Links/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wiki_Links
 * @subpackage Wiki_Links/admin
 * @author     AlphaSys <junjie@alphasys.com.au>
 */
class Wiki_Links_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wiki-links-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wiki-links-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Handles the saving of Wiki Links metadata on the post
	 */
	public function save_post_wiki_metadata( $post_id ) {

		$post = get_post( $post_id );

		// Return if $post->post_type is not viewable
		if ( ! is_post_type_viewable( $post->post_type ) ) return;

		// Return if revision
		if ( wp_is_post_revision( $post_id ) ) return;

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
			$content = $post->post_content;
			preg_match_all( $pattern, $content, $matched_texts );
		}

		if ( !empty( $matched_texts ) ) {
			$enclosed_strs = $matched_texts[1];
			$matched_texts = $matched_texts[0];
		}

		foreach ( $matched_texts as $index => $text_to_replace ) {
			$text_to_search = $enclosed_strs[$index];

			$text_to_search = str_replace( ' ', '-', strtolower( $text_to_search ) );

			$link_page = get_page_by_path( $text_to_search, 'OBJECT', $public_post_types );

			$link_path = isset( $link_page ) ? get_permalink( $link_page ) : false;			

			$link_class = $link_path ? 'has-link' : 'no-link';			
			$link_label = $link_path ? $enclosed_strs[$index] : $text_to_replace;
			$link_style = $link_class === 'no-link' ? 'style="color:red"' : '';
			
			$replacement_text[$text_to_search] = sprintf( '<a href="%s" class="wiki-link %s" %s>%s</a>', $link_path ? $link_path : '#', $link_class, $link_style, $link_label );
		}
		
		$potential_links = get_field( 'potential_links' );

		foreach ( $replacement_text as $replacement_key => $replacement_val ) {	
							
			if ( $potential_links ) {

				$existing_link = false;
				$updated_content = false;
				$updated_val = false;
				$updated_row = false;

				foreach( $potential_links as $row_index => $potential ) {
					if ( $existing_link ) {
						break;
					}

					if ( $potential['slug'] === $replacement_key ) {
						$existing_link = true;						

						if ( $potential['link'] !== $replacement_val ) {
							$updated_content = true;
							$updated_val = array(
								'slug' => $potential['slug'],
								'link' => $replacement_val
							);
							$updated_row = $row_index;
						}
					} 
				}

				if ( $existing_link ) {
					if ( $updated_content ) {
						// added +1 as acf repeater rows start with index 1
						update_row( 'potential_links', absint( $updated_row ) + 1, $updated_val );
					}
				} else {
					add_row( 'potential_links', array(
						'slug' => $replacement_key,
						'link' => $replacement_val
					) );
				}
			}
		}
	}

}
