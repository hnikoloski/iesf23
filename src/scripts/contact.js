import axios from "axios";

jQuery(document).ready(function ($) {
    if ($('.page-template-contact-page .contact-form')) {
        $('.page-template-contact-page .contact-form .contact-form').on('submit', function (e) {
            e.preventDefault();
            let $this = $(this);
            let $form = $this.serialize();
            $this.addClass('loading');
            $this.append('<div class="loading-icon"></div>');
            $this.find('.response-wrapper').hide();
            axios.post(window.location.origin + '/wp-json/iesf/v1/contact', $form)
                .then(function (response) {
                    console.log(response);
                    let err_fields = response.data.err_fields;
                    let isError = response.data.error;
                    let responseMsg = response.data.message;

                    if (isError) {
                        $this.find('.response-wrapper').addClass('error');
                        $this.find('.form-control-field').each(function () {
                            let $field = $(this);
                            if (err_fields.includes($field.attr('name'))) {
                                $field.addClass('error');
                            } else {
                                $field.removeClass('error');
                            }
                        });
                        $this.find('.response-wrapper p').html(responseMsg);
                    } else {
                        $this.find('.response-wrapper').removeClass('error');
                        $this.find('.response-wrapper').addClass('success');
                        $
                        $this.find('.form-control-field').removeClass('error');
                        $this.find('.form-control-field').val('');

                        $this.find('.response-wrapper p').html(responseMsg);
                    }
                })
                .catch(function (error) {

                }).then(() => {
                    $this.removeClass('loading');
                    $this.find('.loading-icon').remove();
                    $this.find('.response-wrapper').show();
                })
        });
    }
});