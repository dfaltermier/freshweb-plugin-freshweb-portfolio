<?php
 /** 
 * This class creates a meta box for the Portfolio custom post type.
 *
 * @package    FreshWeb_Portfolio
 * @subpackage Functions
 * @copyright  Copyright (c) 2017, freshwebstudio.com
 * @link       https://freshwebstudio.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      0.9.1
 */
class FW_Portfolio_Meta_Box {
    
    const MAX_PORTFOLIO_SCREENSHOTS = 6;

    const ALLOWED_HTML_TAGS = array(
        'a' => array(
            'class' => array(),
            'href'  => array(),
            'rel'   => array(),
            'title' => array()
        ),
        'abbr' => array(
            'title' => array()
        ),
        'b' => array(),
        'blockquote' => array(
            'cite'  => array()
        ),
        'cite' => array(
            'title' => array()
        ),
        'code' => array(),
        'del' => array(
            'datetime' => array(),
            'title' => array()
        ),
        'dd' => array(),
        'div' => array(
            'class' => array(),
            'title' => array(),
            'style' => array()
        ),
        'dl' => array(),
        'dt' => array(),
        'em' => array(),
        'h1' => array(),
        'h2' => array(),
        'h3' => array(),
        'h4' => array(),
        'h5' => array(),
        'h6' => array(),
        'i' => array(),
        'img' => array(
            'alt'    => array(),
            'class'  => array(),
            'height' => array(),
            'src'    => array(),
            'width'  => array()
        ),
        'li' => array(
            'class' => array()
        ),
        'ol' => array(
            'class' => array()
        ),
        'p' => array(
            'class' => array()
        ),
        'q' => array(
            'cite' => array(),
            'title' => array()
        ),
        'span' => array(
            'class' => array(),
            'title' => array(),
            'style' => array()
        ),
        'strike' => array(),
        'strong' => array(),
        'ul' => array(
            'class' => array()
        )
    );

    function __construct()  {
        
        add_action( 'add_meta_boxes', array( $this, 'add_portfolio_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_portfolio_meta_box' ), 10, 2 );

    }
    
    /**
     * Load meta box.
     *
     * @since  0.9.1
     */
    public function add_portfolio_meta_box() {

        add_meta_box(
            'fw-portfolio-details',  // Meta box ID
            'Portfolio Details',     // Meta box label
            array( $this, 'render_portfolio_meta_box' ),
            'portfolio',
            'normal',
            'high'
        );

    }

    /**
     * Callback from add_meta_box() to render our meta box.
     *
     * @since  0.9.1
     */
    public function render_portfolio_meta_box() {

        global $post;

        $this->meta_box_detail_fields( $post->ID );

    }

    /**
     * Fetch array of image data associated with the given attachment id.
     * An array of default values are returned for a null attachment id value.
     *
     * @since  0.9.1
     *
     * @param  string $attachment_id   Image attachment id. Default: null.
     * @return array                   Array of image data.
     */
    private function get_post_image_data( $attachment_id = null ) {

        $image = array();

        $image['url'] = empty( $attachment_id ) ? '' : wp_get_attachment_url( $attachment_id );

        // If we don't have the url, then there's nothing to display.
        if ( empty( $image['url'] ) ) {
            $image['attachment_id'] = '';
            $image['upload_button_style'] = 'display:inline-block;';
            $image['remove_button_style'] = 'display:none;';
            $image['img_style'] = 'display:none;';
        }
        else {
            $image['attachment_id'] = $attachment_id;
            $image['upload_button_style'] = 'display:none;';
            $image['remove_button_style'] = 'display:inline-block;';
            $image['img_style'] = 'display:inline;';
        }

        return $image;

    }

    /**
     * Fetch image data associated with the given post_id.
     *
     * @since  0.9.1
     *
     * @param  int    $post_id         Post id.
     * @param  string $post_meta_key   Key passed to get_post_meta().
     * @return array                   Array of image data.
     */
    private function get_post_image( $post_id, $post_meta_key ) {

        $image_attachment_id = get_post_meta( $post_id, $post_meta_key, true );

        $post_image = $this->get_post_image_data( $image_attachment_id );

        return $post_image;

    }

    /**
     * Fetch array of image data associated with the given post_id.
     *
     * @since  0.9.1
     *
     * @param  int    $post_id         Post id.
     * @param  string $post_meta_key   Key passed to get_post_meta().
     * @return array                   One or more arrays of image data.
     */
    private function get_post_images( $post_id, $post_meta_key ) {

        $post_images = array();
        $image_attachment_ids = get_post_meta( $post_id, $post_meta_key, true );
  
        $image_attachment_ids = is_array( $image_attachment_ids )
            ? $image_attachment_ids
            : array();

        $image_attachment_ids_count = count( $image_attachment_ids );

        for ( $i = 0; $i < self::MAX_PORTFOLIO_SCREENSHOTS; $i++ ) {

            if ( $i < $image_attachment_ids_count ) {
                $post_images[] = $this->get_post_image_data( $image_attachment_ids[$i] );
            }
            else {
                $post_images[] = $this->get_post_image_data();
            }

        }

        return $post_images;

    }

    /**
     * Create a wp_editor() with standardized parameters.
     *
     * @since  0.9.1
     *
     * @param  string  $content    HTML/text string
     * @param  string  $editor_id  HTML ID
     */
    private function wp_editor( $content, $editor_id ) {

        return wp_editor ( 
            $content,
            $editor_id,
            array (
                'media_buttons' => false, 
                'editor_height' => '200px',
                'wpautop'       => false  // Add <p> tags
            ) 
        );

    }

    /**
     * Save string value created by wp_editor().
     *
     * @since  0.9.1
     *
     * @param  string  $post_id       Post ID
     * @param  string  $editor_id     HTML ID
     * @param  string  $post_meta_id  Post meta ID
     */
    private function update_post_meta_from_wp_editor( $post_id, $editor_id, $post_meta_id ) {

        $value = $_POST[$editor_id];
        $value = wp_kses( $value, self::ALLOWED_HTML_TAGS );
        $value = wpautop( $value, true ); // true: paragraph conversion are converted to HTML <br>.

        update_post_meta( 
            $post_id,
            $post_meta_id, 
            ( empty( $value ) ? '' : $value )
        );

    }

    /**
     * Display our meta box fields.
     *
     * @since  0.9.1
     *
     * @param  int  $post_id   Post id.
     */
    private function meta_box_detail_fields( $post_id ) {

        $header_image       = $this->get_post_image( $post_id, '_fw_portfolio_header_image_attachment_id' );
        $lead_image         = $this->get_post_image( $post_id, '_fw_portfolio_lead_image_attachment_id' );
        $summary            = get_post_meta( $post_id, '_fw_portfolio_summary', true );
        $introduction       = get_post_meta( $post_id, '_fw_portfolio_introduction', true );
        $solution           = get_post_meta( $post_id, '_fw_portfolio_solution', true );
        $client_testimonial = get_post_meta( $post_id, '_fw_portfolio_client_testimonial', true );
        $client_image       = $this->get_post_image( $post_id, '_fw_portfolio_client_image_attachment_id' );
        $client_name        = get_post_meta( $post_id, '_fw_portfolio_client_name', true );
        $client_title       = get_post_meta( $post_id, '_fw_portfolio_client_title', true );
        $client_website_url = get_post_meta( $post_id, '_fw_portfolio_client_website_url', true );

        // This will be an array of all portfolio screenshot images.
        $portfolio_images   = $this->get_post_images( $post_id, '_fw_portfolio_portfolio_images_attachment_ids' );

        ?>
        <?php wp_nonce_field( 'fw_portfolio_save', 'fw_portfolio_meta_box_nonce' ); ?>

        <?php 
            // Classnames beginning with 'js-' are hooks used by our JavaScript file fw-admin-image-uploader.js.
        ?>
        <table class="form-table">
            <tr>
                <th><label>Header Image</label></th>
                <td>
                    <div class="fw-image-upload__header-image-container js-fw-image-upload__container">
                        <div><img 
                            src="<?php echo esc_attr( $header_image['url'] ); ?>"
                            style="<?php echo esc_attr( $header_image['img_style'] ); ?>"
                            alt="Header image" /></div>
                        <input type="hidden"
                               name="fw_portfolio_header_image_attachment_id"
                               id="fw_portfolio_header_image_attachment_id" 
                               value="<?php echo esc_attr( $header_image['attachment_id'] ); ?>" />
                        <input type="button"
                               style="<?php echo esc_attr( $header_image['upload_button_style'] ); ?>"
                               class="button js-fw-image-upload__add-button"
                               value="Upload Image" />
                        <input type="button"
                               style="<?php echo esc_attr( $header_image['remove_button_style'] ); ?>"
                               class="button js-fw-image-upload__remove-button"
                               value="Remove Image" />
                    </div>
                    <p class="description">
                        The header image is located below the main navigation.
                        Expected dimensions: 1800px x 460px.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Lead Image</label></th>
                <td>
                    <div class="fw-image-upload__lead-image-container js-fw-image-upload__container">
                        <div><img 
                            src="<?php echo esc_attr( $lead_image['url'] ); ?>"
                            style="<?php echo esc_attr( $lead_image['img_style'] ); ?>"
                            alt="Lead image" /></div>
                        <input type="hidden"
                               name="fw_portfolio_lead_image_attachment_id"
                               id="fw_portfolio_lead_image_attachment_id" 
                               value="<?php echo esc_attr( $lead_image['attachment_id'] ); ?>" />
                        <input type="button"
                               style="<?php echo esc_attr( $lead_image['upload_button_style'] ); ?>"
                               class="button js-fw-image-upload__add-button"
                               value="Upload Image" />
                        <input type="button"
                               style="<?php echo esc_attr( $lead_image['remove_button_style'] ); ?>"
                               class="button js-fw-image-upload__remove-button"
                               value="Remove Image" />
                    </div>
                    <p class="description">
                        The lead image is located near the introduction text.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Summary</label></th>
                <td>
                    <?php $this->wp_editor( $summary, 'fw_portfolio_summary' ); ?>
                    <p class="description">
                        Provide a short summary of the project. This summary can be displayed where
                        a short overview is needed.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Introduction</label></th>
                <td>
                    <?php $this->wp_editor( $introduction, 'fw_portfolio_introduction' ); ?>
                    <p class="description">
                        Provide an introduction to the problem we solved.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Solution</label></th>
                <td>
                    <?php $this->wp_editor( $solution, 'fw_portfolio_solution' ); ?>
                    <p class="description">
                        Describe the solution we provided.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Client Testimonial</label></th>
                <td>
                    <?php $this->wp_editor( $client_testimonial, 'fw_portfolio_client_testimonial' ); ?>
                    <p class="description">
                        Provide a short client testimonial.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Client Photo</label></th>
                <td>
                    <div class="fw-image-upload__client-image-container js-fw-image-upload__container">
                        <div><img 
                            src="<?php echo esc_attr( $client_image['url'] ); ?>"
                            style="<?php echo esc_attr( $client_image['img_style'] ); ?>"
                            alt="Client image" /></div>
                        <input type="hidden"
                               name="fw_portfolio_client_image_attachment_id"
                               id="fw_portfolio_client_image_attachment_id" 
                               value="<?php echo esc_attr( $client_image['attachment_id'] ); ?>" />
                        <input type="button"
                               style="<?php echo esc_attr( $client_image['upload_button_style'] ); ?>"
                               class="button js-fw-image-upload__add-button"
                               value="Upload Image" />
                        <input type="button"
                               style="<?php echo esc_attr( $client_image['remove_button_style'] ); ?>"
                               class="button js-fw-image-upload__remove-button"
                               value="Remove Image" />
                    </div>
                    <p class="description">
                        Provide a photo of the client.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Client's Name</label></th>
                <td><input type="text" 
                     id="fw_portfolio_client_name"
                     name="fw_portfolio_client_name" 
                     value="<?php echo esc_attr( $client_name ); ?>"
                     placeholder="Sally Jones"
                     maxlength="300" />
                    <p class="description">
                        Client's personal full name.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Client's Title</label></th>
                <td><input type="text" 
                     id="fw_portfolio_client_title"
                     name="fw_portfolio_client_title" 
                     value="<?php echo esc_attr( $client_title ); ?>"
                     placeholder="Founder"
                     maxlength="300" />
                    <p class="description">
                        Client's business position or title.
                    </p>
                </td>
            </tr> 

            <tr>
                <th><label>Client's Website Url</label></th>
                <td><input type="text" 
                     id="fw_portfolio_client_website_url"
                     name="fw_portfolio_client_website_url" 
                     value="<?php echo esc_attr( $client_website_url ); ?>"
                     placeholder="https://google.com"
                     maxlength="300" />
                    <p class="description">
                        Client's business website url.
                    </p>
                </td>
            </tr> 

           <tr>
                <th><label>Portfolio Screenshots</label></th>
                <td>

                    <?php 
                    foreach ($portfolio_images as $portfolio_image) : ?>

                        <div class="fw-image-upload__portfolio-screenshot-image-wrapper">
                            <div class="fw-image-upload__portfolio-screenshot-image-container js-fw-image-upload__container">
                                <div><img 
                                    src="<?php echo esc_attr( $portfolio_image['url'] ); ?>"
                                    style="<?php echo esc_attr( $portfolio_image['img_style'] ); ?>"
                                    alt="Portfolio screenshot" /></div>
                                <input type="hidden"
                                       name="fw_portfolio_screenshot_image_attachment_id[]"
                                       value="<?php echo esc_attr( $portfolio_image['attachment_id'] ); ?>" />
                                <input type="button"
                                       style="<?php echo esc_attr( $portfolio_image['upload_button_style'] ); ?>"
                                       class="button js-fw-image-upload__add-button"
                                       value="Upload Image" />
                                <input type="button"
                                       style="<?php echo esc_attr( $portfolio_image['remove_button_style'] ); ?>"
                                       class="button js-fw-image-upload__remove-button"
                                       value="Remove Image" />
                            </div>
                            <p class="description">
                                Provide a screenshot of the client website.
                            </p>
                        </div>

                    <?php endforeach; ?>
                </td>
            </tr> 

        </table>
        <?php
    }

    /**
     * Save our meta box fields.
     *
     * @since  0.9.1
     *
     * @param  int       $post_id   Post id.
     * @param  WP_Post   $post      Post object (https://developer.wordpress.org/reference/classes/wp_post/)
     */
    public function save_portfolio_meta_box( $post_id, $post ) {
        
        if ( ! isset( $_POST['fw_portfolio_meta_box_nonce'] ) ||
             ! wp_verify_nonce( $_POST['fw_portfolio_meta_box_nonce'], 'fw_portfolio_save' ) ) {
            return;
        }

        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
             ( defined( 'DOING_AJAX') && DOING_AJAX ) ||
               isset( $_REQUEST['bulk_edit'] ) ) {
            return;
        }

        if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
            return;
        }

        if ( ! current_user_can( 'edit_posts', $post_id ) ) {
            return;
        }

        /*
         * Save the header image attachment id.
         */
        if ( isset( $_POST['fw_portfolio_header_image_attachment_id'] ) ) {

            $value = sanitize_text_field( trim( $_POST['fw_portfolio_header_image_attachment_id'] ) );
            update_post_meta( $post_id, '_fw_portfolio_header_image_attachment_id', $value );

        }

        /*
         * Save the lead image attachment id.
         */
        if ( isset( $_POST['fw_portfolio_lead_image_attachment_id'] ) ) {

            $value = sanitize_text_field( trim( $_POST['fw_portfolio_lead_image_attachment_id'] ) );
            update_post_meta( $post_id, '_fw_portfolio_lead_image_attachment_id', $value );

        }

        /*
         * Save the summary.
         */
        if ( isset( $_POST['fw_portfolio_summary'] ) ) {

            $this->update_post_meta_from_wp_editor(
                $post_id, 
                'fw_portfolio_summary', 
                '_fw_portfolio_summary'
            );

        }

        /*
         * Save the introduction.
         */
        if ( isset( $_POST['fw_portfolio_introduction'] ) ) {

            $this->update_post_meta_from_wp_editor(
                $post_id, 
                'fw_portfolio_introduction', 
                '_fw_portfolio_introduction'
            );

        }

        /*
         * Save the solution.
         */
        if ( isset( $_POST['fw_portfolio_solution'] ) ) {

            $this->update_post_meta_from_wp_editor(
                $post_id, 
                'fw_portfolio_solution', 
                '_fw_portfolio_solution'
            );

        }

        /*
         * Save the client testimonial.
         */
        if ( isset( $_POST['fw_portfolio_client_testimonial'] ) ) {

            $this->update_post_meta_from_wp_editor(
                $post_id, 
                'fw_portfolio_client_testimonial', 
                '_fw_portfolio_client_testimonial'
            );

        }

        /*
         * Save the client photo.
         */
        if ( isset( $_POST['fw_portfolio_client_image_attachment_id'] ) ) {

            $value = sanitize_text_field( trim( $_POST['fw_portfolio_client_image_attachment_id'] ) );
            update_post_meta( $post_id, '_fw_portfolio_client_image_attachment_id', $value );

        }

        /*
         * Save the client name.
         */
        if ( isset( $_POST['fw_portfolio_client_name'] ) ) {

            $value = sanitize_text_field( trim( $_POST['fw_portfolio_client_name'] ) );
            update_post_meta( $post_id, '_fw_portfolio_client_name', $value );

        }

        /*
         * Save the client title.
         */
        if ( isset( $_POST['fw_portfolio_client_title'] ) ) {

            $value = sanitize_text_field( trim( $_POST['fw_portfolio_client_title'] ) );
            update_post_meta( $post_id, '_fw_portfolio_client_title', $value );

        }

        /*
         * Save the client website url.
         */
        if ( isset( $_POST['fw_portfolio_client_website_url'] ) ) {

            $value = sanitize_text_field( trim( $_POST['fw_portfolio_client_website_url'] ) );
            update_post_meta( $post_id, '_fw_portfolio_client_website_url', $value );

        }

        /*
         * Save the portfolio screenshot image attachment ids.
         */
        if ( isset( $_POST['fw_portfolio_screenshot_image_attachment_id'] ) ) {

            $array_value = array_map( 'sanitize_text_field', $_POST['fw_portfolio_screenshot_image_attachment_id'] );
            update_post_meta( $post_id, '_fw_portfolio_portfolio_images_attachment_ids', $array_value );

        }

    }

}