<?php
 /** 
 * Bootstrapping class.
 *
 * All of our plugin dependencies are initalized here.
 *
 * @package    FreshWeb_Portfolio
 * @subpackage Functions
 * @copyright  Copyright (c) 2017, freshwebstudio.com
 * @link       https://freshwebstudio.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      0.9.1
 */
class FW_Portfolio {
    
    function __construct()  { 
    }

    /**
     * Run our initialization.
     *
     * @since 0.9.1
     */
    public function run() {

        $this->setup_constants();
        $this->includes();

        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
    
    }

    /**
     * Determines if we're on one of our plugin's admin pages.
     *
     * @since 0.9.1
     *
     * @return  bool  Returns true if this is so.
     */
    public function is_plugin_admin_page() {

        global $typenow;

        return ( 'portfolio' === $typenow ? true : false );

    }

    /**
     * Enqueue our scripts and stylesheets.
     *
     * @since 0.9.1
     *
     */
    public function load_admin_scripts() {

        // Only enqueue if we're in our plugin pages.
        if ( ! $this->is_plugin_admin_page() ) {
            return;
        }

        wp_enqueue_script(
            'fw_admin_image_uploader',
            FW_PORTFOLIO_PLUGIN_URL . 'admin/js/fw-admin-image-uploader.js',
            array( 'jquery' ),
            FW_PORTFOLIO_VERSION
        );

        wp_enqueue_style(
            'fw_portfolio_styles',
            FW_PORTFOLIO_PLUGIN_URL . 'admin/css/style.css', 
            array(), 
            FW_PORTFOLIO_VERSION
        );

    }

    /**
     * Setup plugin constants.
     *
     * @since  0.9.1
     * @access private
     */
    private function setup_constants() {

        /*
         * Set true if plugin is to be detected by theme writers as activated.
         *
         * Theme writers: Use this defined variable to determine if plugin is installed
         * and activated. False means No, True means yes.
         */
        if ( ! defined( 'FW_PORTFOLIO_IS_ACTIVATED' ) ) {
            define( 'FW_PORTFOLIO_IS_ACTIVATED', true );
        }     

        // Plugin version.
        if ( ! defined( 'FW_PORTFOLIO_VERSION' ) ) {
            define( 'FW_PORTFOLIO_VERSION', '0.9.1' );
        }

        // Plugin Folder Path (without trailing slash)
        if ( ! defined( 'FW_PORTFOLIO_PLUGIN_DIR' ) ) {
            define( 'FW_PORTFOLIO_PLUGIN_DIR', dirname( __DIR__ ) );
        }

        // Plugin Folder URL (with trailing slash)
        if ( ! defined( 'FW_PORTFOLIO_PLUGIN_URL' ) ) {
            define( 'FW_PORTFOLIO_PLUGIN_URL', plugin_dir_url( __DIR__ ) );
        }

    }

    /**
     * Include required files.
     *
     * @since  0.9.1
     * @access private
     */
    private function includes() {

        require_once FW_PORTFOLIO_PLUGIN_DIR . '/includes/class-fw-portfolio-post-types.php';
        $post_types = new FW_Portfolio_Post_Types;

        require_once FW_PORTFOLIO_PLUGIN_DIR . '/includes/class-fw-portfolio-meta-box.php';
        $meta_boxes = new FW_Portfolio_Meta_Box;

    }

}