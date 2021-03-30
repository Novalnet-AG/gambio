/**
 * Novalnet Paypal script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */
    if(typeof(jQuery) == undefined ) {
      var s = document.createElement("script");
      s.type = "text/javascript";
      var nn_cc_root = document.getElementById('nn_root_paypal_catalog').value;
      s.src = nn_cc_root+"ext/novalnet/js/jquery.js";
      document.getElementsByTagName("head")[0].appendChild(s);
    }
    if (window.addEventListener) { // For all major browsers, except IE 8 and earlier
        window.addEventListener("load", novalnet_paypal_load);
    } else if (window.attachEvent) { // For IE 8 and earlier versions
        window.attachEvent("onload", novalnet_paypal_load);
    }
/**
 * To performing the page loading and one click shopping
 */
    function novalnet_paypal_load() {
        var parentClassName = (jQuery( ".nn_paypal_ref_details" ).parent().attr('class') == 'input') ? jQuery( ".nn_paypal_ref_details" ).parent().attr('class') : jQuery( ".nn_paypal_ref_details" ).parent().parent().attr('class');
        jQuery('#novalnet_paypal_new_acc').click(function() {
            if (jQuery('.nn_paypal_acc').css('display') == 'none') {
                jQuery('#nn_normal_description').html(jQuery('#nn_redirect_desc').val() + '<br>' + jQuery('#nn_redirect_browser_desc').val());
                jQuery('.nn_paypal_acc').css('display', 'block');
                jQuery('.nn_paypal_ref_details').css('display', 'none');
                jQuery('#novalnet_paypal_change_account').val('0');
                jQuery("div").find( ".nn_paypal_ref_details" ).closest('.'+parentClassName).hide();
                jQuery("div").find( ".nn_paypal_acc" ).closest('.'+parentClassName).show();
                jQuery('#novalnet_paypal_new_acc').html('<u><b>'+jQuery('#nn_lang_paypal_given_account').val()+'</b></u>');
            } else {
                jQuery('#nn_normal_description').html(jQuery('#nn_paypal_one_click_desc').val());
                jQuery('.nn_paypal_ref_details').css('display', 'block');
                jQuery("div").find( ".nn_paypal_ref_details" ).closest('.'+parentClassName).show();
                jQuery("div").find( ".nn_paypal_acc" ).closest('.'+parentClassName).hide();
                jQuery('.nn_paypal_acc').css('display', 'none');
                jQuery('#novalnet_paypal_change_account').val('1');
                jQuery('#novalnet_paypal_new_acc').html('<u><b>'+jQuery('#nn_lang_paypal_new_account').val()+'</b></u>');
            }
        });
        if (jQuery('#novalnet_paypal_change_account').val() != undefined) {
            if (jQuery('#novalnet_paypal_change_account').val() == 1 || jQuery('#novalnet_paypal_change_account').val() == '') {
                jQuery('.nn_paypal_acc').css('display', 'none');
                jQuery('.nn_paypal_ref_details').css('display', 'block');
                jQuery('.nn_paypal_ref_details').css('margin', '2% 0% 0% 0%');
                jQuery('#novalnet_paypal_new_acc').html('<u><b>'+jQuery('#nn_lang_paypal_new_account').val()+'</b></u>');
                jQuery('#nn_normal_description').html(jQuery('#nn_paypal_one_click_desc').val());
            } else {
                jQuery("div").find( ".nn_paypal_ref_details" ).closest('.'+parentClassName).hide();
                jQuery('.nn_paypal_acc').css('display', 'block');
                jQuery('.nn_paypal_ref_details').css('display', 'none');
                jQuery('#novalnet_paypal_new_acc').html('<u><b>'+jQuery('#nn_lang_paypal_given_account').val()+'</b></u>');
            }
        }
        if($( "div" ).hasClass( "checkout-payment-form" )){
		  if($('.checkout-payment-form').css('display') == 'none'){
		    $('.checkout-payment-form').css('display','block');
	        }
	 }
    }
