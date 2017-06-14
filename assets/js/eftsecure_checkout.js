jQuery( function( $ ) {
	'use strict';

    window.wc_checkout_form = {
        submit_error: function(error_message) {
            var $checkout_form = jQuery('form.checkout');
            jQuery('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
            $checkout_form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>');
            $checkout_form.removeClass('processing').unblock();
            $checkout_form.find('.input-text, select, input:checkbox').blur();
            jQuery('html, body').animate({
                scrollTop: (jQuery('form.checkout').offset().top - 100)
            }, 1000);
            jQuery(document.body).trigger('checkout_error');
        }
    };

	var wc_eftsecure_form = {
		init: function( form ) {
			//Check for service url override
			if (typeof wc_eftsecure_params.service_url != 'undefined' ) {
                eftSec.checkout.settings.serviceUrl = wc_eftsecure_params.service_url;
			}
			else {
                eftSec.checkout.settings.serviceUrl = "{protocol}://eftsecure.callpay.com/eft";
			}

			this.form          = form;
			this.eftsecure_submit = false;

			$( this.form )
				.on( 'click', '#place_order', this.onSubmit )
				.on( 'submit checkout_place_order_eftsecure' );

			$( document.body ).on( 'checkout_error', this.resetModal );
		},

        validates: function() {

            var $required_inputs;

            if ( $( 'input#terms' ).length === 1 && $( 'input#terms:checked' ).length === 0 ) {
                return false;
            }

            if ( $( '#createaccount' ).is( ':checked' ) && $( '#account_password' ).length && $( '#account_password' ).val() === '' ) {
                return false;
            }

            // check to see if we need to validate shipping address
            if ( $( '#ship-to-different-address-checkbox' ).is( ':checked' ) ) {
                $required_inputs = $( '.woocommerce-billing-fields .validate-required, .woocommerce-shipping-fields .validate-required' );
            } else {
                $required_inputs = $( '.woocommerce-billing-fields .validate-required' );
            }

            if ( $required_inputs.length ) {
                var required_error = false;

                $required_inputs.each( function() {
                    if ( $( this ).find( 'input.input-text, select' ).not( $( '#account_password, #account_username' ) ).val() === '' ) {
                        required_error = true;
                    }
                });

                if ( required_error ) {
                    return false;
                }
            }

            return true;
        },

		isEftsecureChosen: function() {
			return $( '#payment_method_eftsecure' ).is( ':checked' );
		},

		isEftsecureModalNeeded: function( e ) {
			// Don't affect submit if modal is not needed.
			if (!wc_eftsecure_form.isEftsecureChosen() || !wc_eftsecure_form.validates()) {
				return false;
			}
            // Don't affect submit if payment already complete.
			if (wc_eftsecure_form.eftsecure_submit) {
				return false;
			}
			return true;
		},

		block: function() {
			wc_eftsecure_form.form.block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
		},

		unblock: function() {
			wc_eftsecure_form.form.unblock();
		},

		onClose: function() {
			wc_eftsecure_form.unblock();
		},

		onSubmit: function( e ) {

			if ( wc_eftsecure_form.isEftsecureModalNeeded()) {
				var $data = jQuery('#eftsecure-payment-data');
				e.preventDefault();

                jQuery.ajax({
                    type: 'POST',
                    url: wc_checkout_params.checkout_url,
                    data: jQuery('form.checkout').serialize(),
                    dataType: 'json',
                    success: function(result) {
                        try {
                            if ('success' === result.result) {
                                var $paymentKey = result.paymentKey;
                                console.log($paymentKey);
                                console.log('Initiating transaction');
								eftSec.checkout.init({
                                    paymentKey: $paymentKey,
									onLoad: function() {
										wc_eftsecure_form.unblock();
									},
									onComplete: function(data) {
										eftSec.checkout.hideFrame();
										wc_eftsecure_form.eftsecure_submit = true;
										var $form = wc_eftsecure_form.form;
										if ($form.find( 'input.eftsecure_transaction_id' ).length > 0) {
											$form.find('input.eftsecure_transaction_id').remove();
										}
										$form.append( '<input type="hidden" class="eftsecure_transaction_id" name="eftsecure_transaction_id" value="' + data.transaction_id + '"/>' );
										$form.submit();
									}
								});

                            } else if ('failure' === result.result) {
                                throw 'Result failure';
                            } else {
                                throw 'Invalid response';
                            }
                        } catch (err) {
                            // Reload page
                            if (true === result.reload) {
                                window.location.reload();
                                return;
                            }

                            // Trigger update in case we need a fresh nonce
                            if (true === result.refresh) {
                                jQuery(document.body).trigger('update_checkout');
                            }

                            // Add new errors
                            if (result.messages) {
                                window.wc_checkout_form.submit_error(result.messages);
                            } else {
                                window.wc_checkout_form.submit_error('<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>');
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        wc_checkout_form.submit_error('<div class="woocommerce-error">' + errorThrown + '</div>');
                    }
                });

				wc_eftsecure_form.block();


				return false;
			}

			return true;
		},

		resetModal: function() {
			if (wc_eftsecure_form.form.find( 'input.eftsecure_transaction_id' ).length > 0) {
                wc_eftsecure_form.form.find('input.eftsecure_transaction_id').remove();
            }
			wc_eftsecure_form.eftsecure_submit = false;
		}
	};

	wc_eftsecure_form.init( $( "form.checkout, form#order_review, form#add_payment_method" ) );
} );
