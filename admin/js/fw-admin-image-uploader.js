/**
 * Provides the behavior on a Custom Post Type (CPT) meta field box where
 * we are using image upload buttons to add images to the CPT.
 *
 * USE:
 *
 * The html for image uploading must be wrapped in the shown parent <div> and
 * contain the following [minimum] html elements. Be sure to preserve the 
 * [JavaScript] class names.
 *
 * <div class="js-fw-image-upload__container">
 *    <div><img src="" style="display:none;" alt="" /></div>
 *    <input type="hidden"
 *           name="your-hidden-field-name-here"
 *           id="your-hidden-field-name-here" value="" />
 *    <input type="button"
 *           class="button js-fw-image-upload__add-button"
 *           value="Upload Image" />
 *    <input type="button" 
 *           class="button js-fw-image-upload__remove-button"
 *           value="Remove Image" style="display:none;" />
 * </div>
 *
 * @copyright  Copyright (c) 2017, freshwebstudio.com
 * @link       https://freshwebstudio.com
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * @since      0.9.1
 */
(function($) {

    'use strict';

    $(function() {

        /**
         * Callback function for the 'click' event on a add button.
         *
         * @since   0.9.1
         * @todo    We should be caching the handle to the wp.media object so we're
         *          not recreating it before every open(). How?
         */
        function addImage(event) {
            event.preventDefault();

            // Ensure the add button is displayed.
            var $addButton = $(this).show();

            // Get the parent container.
            var $container = $addButton.parent();

            // Hide the remove button.
            var $removeButton = $('.js-fw-image-upload__remove-button', $container);

            if ($removeButton.length) {
                $removeButton.hide();
            }

            // Create a new media file frame.
            var fileFrame = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                library: {
                    type: 'image' // Filter library by mediaType
                },
                multiple: false
            });
     
            fileFrame.on('select', function() {
                var attachment = fileFrame.state().get('selection').first().toJSON();

                // Display our image.
                var $image = $('img', $container);
                if ($image.length) {
                    $image.attr('src', attachment.url).fadeIn();
                }

                // We expect our hidden form field to store our image attachment id.
                var $hidden = $('input[type="hidden"]', $container);
                if ($hidden.length) {
                    $hidden.val(attachment.id);
                }

                if ($removeButton.length) {
                    $addButton.hide();
                    $removeButton.show();
                }
            });

            fileFrame.open();
        }

        /**
         * Callback function for the 'click' event on a remove button.
         *
         * @since   0.9.1
         */
        function removeImage(event) {
            event.preventDefault();

            var $removeButton = $(this);
            var $container    = $removeButton.parent();
            var $addButton    = $('.js-fw-image-upload__add-button', $container);

            // Clear hidden form field value.
            var $input = $('input[type="hidden"]', $container);
            if ($input.length) {
                $input.val('');
            }

            // Clear our image.
            var $image = $('img', $container);

            if ($image.length) {
                $image.fadeOut(500, function() {
                    $(this).attr('src', '');
                });
            }

            if ($addButton.length) {
                $removeButton.hide();
                $addButton.show();
            }
        }

        /**
         * Activate the add button.
         *
         * @since 0.9.1
         */
        function activateAddButtons() {
            $('.js-fw-image-upload__add-button', '.js-fw-image-upload__container')
                .on('click', addImage);
        }

        /**
         * Activate the remove button.
         *
         * @since 0.9.1
         */
        function activateRemoveButtons() {
            $('.js-fw-image-upload__remove-button', '.js-fw-image-upload__container')
                .on('click', removeImage);
        }

        /**
         * On the 'Add New Taxonomy' pages, clicking 
         * WordPress's submit button does not refresh the page when it submits
         * the form fields. It submits the fields via an Ajax call and then displays
         * the newly created taxonomy in the table to the right of the form.
         * After the form is submitted, WordPress only clears the text and text 
         * area form fields. Our image and hidden form fields are not cleared as
         * a result. We must do this ourselves. Again, this only applies to the 
         * 'Add New Taxonomy' page.
         *
         * @since 0.9.1
         */
        function activateClearFormFieldsAfterSubmission() {
            var $form = $('form#addtag');

            if ($form && $form.length === 1) {

                var $button = $('.js-fw-image-upload__remove-button', $form);

                if ($button && $button.length >= 1) {

                    // The WordPress JavaScript for submitting the Add Taxonomy
                    // form does not propagate the button's click event. We'll
                    // attach to the ajaxComplete hook instead so we're notified
                    // when the submission is complete.
                    $(document).ajaxComplete(function(event, jqXHR, obj) {
                        if (event &&
                            event.currentTarget &&
                            event.currentTarget.activeElement &&
                            event.currentTarget.activeElement.id === 'submit') {
                            $button.trigger('click');
                        }
                    });
                }
            }
        }

        // Initialize.
        activateAddButtons();
        activateRemoveButtons();
        activateClearFormFieldsAfterSubmission();

    });

})( jQuery );
