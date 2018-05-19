<?php
 /** 
 * This class creates the Portfolio custom post type and registers the associated
 * taxonomies.
 *
 * @package    FreshWeb_Portfolio
 * @subpackage Functions
 * @copyright  Copyright (c) 2017, freshwebstudio.com
 * @link       https://freshwebstudio.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      0.9.1
 */
class FW_Portfolio_Post_Types {
    
    function __construct()  {
        
        add_action( 'init', array( $this, 'register_post_types' ) );
        add_action( 'init', array( $this, 'register_taxonomies' ) );

        // Add additional columns to our table.
        //add_filter( 'manage_portfolio_posts_columns' , array( $this, 'add_portfolio_columns' ) );
        //add_action( 'manage_portfolio_posts_custom_column' , array( $this, 'populate_portfolio_columns' ), 10, 2 );

        // Add a select menu at the top of the CPT table so posts can be filtered by taxonomies.
        //add_action( 'restrict_manage_posts', array( $this, 'add_taxonomy_filters' ) );

        // Remove the [publish] date select menu from the 'All Portfolio Items' page. Not needed
        // since we don't display the publish date.
        // TODO: remove this? Uneeded?
        //add_filter( 'months_dropdown_results', array( $this, 'remove_date_filter' ), 10, 2 );

    }

    /**
     * Register our Portfolio post type.
     *
     * @since  0.9.1
     *
     */
    public function register_post_types() {

        global $menu;

        /*
         * We would like to place our Portfolio menu option as close to 'Posts' as possible since
         * we are similar as a 'custom' post type. All menu options are registered with a
         * menu_position in a pecking order where 'Posts' has a menu_position of '5' and the
         * other menu options are listed in increasing menu_position order (e.g.: 10, 15, ...)
         * down the vertical menu. The lower the menu_position number, the higher you stay in 
         * the vertical menu.
         *
         * We will attempt to position ourselves as close to the 'Posts' menu option ('5')
         * as possible without choosing a number that is already taken by another menu option.
         * If, for some reason, our Portfolio menu option fails to display, it may be the rare
         * case that another plugin is conflicting their menu_position with ours. WordPress
         * will only choose one plugin to occupy that spot, so if we lose out, look for this
         * to be the problem. 
         *
         * See https://codex.wordpress.org/Function_Reference/register_post_type#menu_position
         */
        $menu_position = 8; // Start under the 'Posts' menu option of '5'.
        while ( isset( $menu[$menu_position] ) ) {
            $menu_position++;
        }

        $portfolio_labels =  array(
            'name'               => 'Portfolio',
            'singular_name'      => 'Portfolio',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Portfolio Item',
            'edit_item'          => 'Edit Portfolio Item',
            'new_item'           => 'New Portfolio Item',
            'all_items'          => 'All Portfolio Items',
            'view_item'          => 'View Portfolio Item',
            'search_items'       => 'Search Portfolio',
            'not_found'          => 'No Portfolio Items Found',
            'not_found_in_trash' => 'No Portfolio Items Found In Trash',
            'menu_name'          => 'Portfolio'
        );

        $portfolio_args = array(
            'labels'             => $portfolio_labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'menu_position'      => $menu_position,
            'menu_icon'          => 'dashicons-id-alt',
            'rewrite'            => true,
            'has_archive'        => 'true',
            'hierarchical'       => false,
            'supports'           => array( 'title', 'revisions', 'author' )
        );

        register_post_type( 'portfolio', $portfolio_args );
        
    }

    /**
     * Register taxonomies
     *
     * @since  0.9.1
     */
    public function register_taxonomies() {

        /* Groups */
        $group_labels = array(
            'name'          => 'Groups',
            'singular_name' => 'Group',
            'search_items'  => 'Search Groups',
            'all_items'     => 'All Groups',
            'parent_item'   => 'Parent Group',
            'edit_item'     => 'Edit Group',
            'update_item'   => 'Update Group',
            'add_new_item'  => 'Add New Group',
            'new_item_name' => 'New Group',
            'menu_name'     => 'Groups',
            'not_found'     => 'No Groups found.'
        );

        $group_args = array(
            'hierarchical' => true,
            'labels'       => $group_labels,
            'show_ui'      => true,
            'query_var'    => 'portfolio_group',
            'rewrite'      => array( 'slug' => 'portfolio/group', 'with_front' => true, 'hierarchical' => true )
        );

        register_taxonomy( 'portfolio_group', array( 'portfolio' ), $group_args );

    }

    /**
     * Configure the given list of table columns with our own.
     *
     * @since   0.9.1
     *
     * @param   array  $columns  List of column ids and labels.
     * @return  array            Same list.
     */
    public function add_portfolio_columns( $columns ) {
  
        unset( $columns['author'] );
        unset( $columns['date'] );

        $columns = array_merge(
            $columns,
            array(
                'portfolio_players'   => 'Players',
                'portfolio_downloads' => 'Downloads',
                'portfolio_series'    => 'Series',
                'portfolio_speaker'   => 'Speaker',
                'featured_image'   => 'Image',
                'date'             => 'Publish Date'
            )
        );

        return $columns;

    }

    /**
     * Switch on the given column id and display an appropriate string
     * in our Portfolio table.
     *
     * @since  0.9.1
     *
     * @param  string  $column    Column id for the value to fetch. See add_portfolio_columns().
     * @param  int     $post_id   Post id.
     */
    public function populate_portfolio_columns( $column, $post_id  ) {

        switch ( $column ) {

            case 'portfolio_players' :
                echo $this->get_portfolio_players( $post_id );
                break;

            case 'portfolio_downloads' :
                echo $this->get_portfolio_downloads( $post_id );
                break;

            case 'portfolio_series' :
                echo $this->get_portfolio_series( $post_id );
                break;

            case 'portfolio_speaker' :
                echo $this->get_portfolio_speaker( $post_id );
                break;
            
            case 'featured_image' :
                echo $this->get_thumbnail_image_html( $post_id );
                break;

            default:
                echo '';
                break;

        }
    }

    /**
     * Returns the media players that are available for viewing/listening with the given Portfolio post id. 
     *
     * @since   0.9.1
     *
     * @param   int     $post_id   Post id.
     * @return  string             Formats (e.g.: 'Audio, Video')
     */
    public function get_portfolio_players( $post_id ) {

        $format = array();

        $audio_player_url = get_post_meta( $post_id, '_fw_portfolio_audio_player_url', true );
        $video_player_url = get_post_meta( $post_id, '_fw_portfolio_video_player_url', true );

        if ( ! empty( $audio_player_url ) ) {
            $format[] = 'Audio';
        }

        if ( ! empty( $video_player_url ) ) {
            $format[] = 'Video';
        }

        return join( ', ', $format );

    }

    /**
     * Returns the media formats that are available for download for the given Portfolio post id. 
     *
     * @since   0.9.1
     *
     * @param   int     $post_id   Post id.
     * @return  string             Formats (e.g.: 'Audio, Video')
     */
    public function get_portfolio_downloads( $post_id ) {

        $format = array();

        $audio_download_url = get_post_meta( $post_id, '_fw_portfolio_audio_download_url', true );
        $video_download_url = get_post_meta( $post_id, '_fw_portfolio_video_download_url', true );
        $document_links     = get_post_meta( $post_id, '_fw_portfolio_document_links', true );
        
        if ( ! empty( $audio_download_url ) ) {
            $format[] = 'Audio';
        }

        if ( ! empty( $video_download_url ) ) {
            $format[] = 'Video';
        }

        if ( ! empty( $document_links ) ) {
            $format[] = 'Portfolio Notes';
        }

        return join( ', ', $format );

    }

    /**
     * Returns the series name associated with the given Portfolio post id. 
     *
     * @since   0.9.1
     *
     * @param   int     $post_id   Post id.
     * @return  string             Series name.
     */
    public function get_portfolio_series( $post_id ) {

        $terms = get_the_terms( $post_id, 'portfolio_series' );

        if ( !empty( $terms ) ) {
            foreach( $terms as $term ) {
                return $term->name;
            }
        } else {
            return '';
        }
        
    }

    /**
     * Returns the speaker name associated with the given Portfolio post id. 
     *
     * @since   0.9.1
     *
     * @param   int      $post_id   Post id.
     * @return  string              Speaker name.
     */
    public function get_portfolio_speaker( $post_id ) {

        $terms = get_the_terms( $post_id, 'portfolio_speaker' );

        if ( !empty( $terms ) ) {
            foreach( $terms as $term ) {
                return $term->name;
            }
        } else {
            return '';
        }
        
    }

    /**
     * Builds and returns an image html string with a thumbnail view of the post's
     * featured image. 
     *
     * @since   0.9.1
     *
     * @param   int      $post_id  Post id.
     * @param   string   $classes  Optional. Space separated list of classes to attach to image html.
     * @return  string             Image html associated with the given post id or empty string.
     */
    public function get_thumbnail_image_html( $post_id, $classes = "" ) {

        $image_id = get_post_thumbnail_id( $post_id );

        if ( ! empty( $image_id ) ) {
            $img_html = wp_get_attachment_image(
                $image_id, 
                'thumbnail', 
                false, 
                array( 'class' => 'fw-portfolio-featured-thumbnail ' . esc_attr( $classes ) )
            );
            return $img_html;
        }

        return '';

    }

    /**
     * Action for displaying one or more select menus on our 'All Portfolio' page.
     * Each menu contains the list of terms for one taxonomy. The selected term
     * will act as a filter when the [WordPress] Filter button is clicked.
     *
     * Portions of code taken from Mike Hemberger's example at:
     * http://thestizmedia.com/custom-post-type-filter-admin-custom-taxonomy/
     *
     * @since  0.9.1
     */
    public function add_taxonomy_filters() {
        global $typenow;

        if ( $typenow === 'portfolio' ) {

            // An array of all the taxonomies you want to display. Use the taxonomy slug.
            $taxonomy_slugs = array( 'portfolio_series', 'portfolio_speaker' );

            foreach ( $taxonomy_slugs as $taxonomy_slug ) {

                $selected  = isset($_GET[$taxonomy_slug]) ? $_GET[$taxonomy_slug] : '';
                $taxonomy_obj   = get_taxonomy( $taxonomy_slug );
                $taxonomy_label = strtolower( $taxonomy_obj->label );

                wp_dropdown_categories(array(
                    'show_option_all' => __("All $taxonomy_label" ),
                    'taxonomy'        => $taxonomy_slug,
                    'name'            => $taxonomy_slug,
                    'orderby'         => 'name',
                    'selected'        => $selected,
                    'show_count'      => true,
                    'hide_empty'      => true,
                    'value_field'     => 'slug'
                ));

            }
        };
    }

    // TODO: remove this? Uneeded?
    /**
     * Action for removing the date select menu from the 'All Portfolio' page.
     * It's not useful to us since we are not displaying the publishing dates.
     *
     * @since  0.9.1
     *
     * @param  array   $months      Array of month objects.
     * @param  string  $post_type   Post type of which we expect 'portfolio'.
     * @return array                $months array.
     */
    /* 
    public function remove_date_filter( $months, $post_type ) {

        // Returning an empty array will remove the select menu.
        return ( $post_type === 'portfolio' ) ? array() : $months;

    }
    */

}
