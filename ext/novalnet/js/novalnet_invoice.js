/**
 * Novalnet novalnet_invoice Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */

	if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
		window.addEventListener('load', novalnet_invoice_load);
	} else if (window.attachEvent) { // For IE 8 and earlier versions
		window.attachEvent('onload', novalnet_invoice_load);
	}

/**
 * To performing the page loading and fraud module
 */
	function novalnet_invoice_load() {
		var current_url = new URL($(location).attr("href"));
		var url = current_url.searchParams.get("payment_error");
		var shop_lang = jQuery('#nn_shop_lang').val();
		jQuery('#novalnet_invoice_dob').attr("placeholder", "DD-MM-YYYY");		
		jQuery( ".shipping_container" ).click(function() {
			jQuery("#novalnet_invoice_callbacknew_pin").prop("checked", false);
		});
		jQuery("#novalnet_invoice_callbackpin").click(function(e) {
			e.stopPropagation();
			(jQuery("#novalnet_invoice_callbacknew_pin").prop("checked")) ? jQuery( "#novalnet_invoice_callbacknew_pin" ).prop("checked","checked") : jQuery( "#novalnet_invoice_callbacknew_pin" ).prop("checked",false) ;
		});
		jQuery("#novalnet_invoice_callbacknew_pin").click(function(e) {
			e.stopPropagation();
			(jQuery("#novalnet_invoice_callbacknew_pin").prop("checked")) ? jQuery( "#novalnet_invoice-callbacknew_pin" ).prop("checked","checked") : jQuery( "#novalnet_invoice-callbacknew_pin" ).prop("checked",false) ;
		});
		if($( "div" ).hasClass( "checkout-payment-form" )){
		if($('.checkout-payment-form').css('display') == 'none'){
		 $('.checkout-payment-form').css('display','block');
	    }
	 }
	 if(url == 'novalnet_invoice'){
		 jQuery('.novalnet_invoice').addClass('active');
	    }
	}

/**
 * validate the fraud_pin
 */
function pin_val(event){
	var keycode = ('which' in event) ? event.which : event.keyCode;
	var reg = /^[a-z0-9]+$/i;
	return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (event.ctrlKey == true && keycode == 114)) ? true : false;
}
