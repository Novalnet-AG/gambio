/**
 * Novalnet Credit Card script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */
	if(typeof(jQuery) == undefined || typeof(jQuery) == 'undefined' || typeof(jQuery) == 'function' ) {
	  var s = document.createElement("script");
	  s.type = "text/javascript";
	  var nn_cc_root = document.getElementById('nn_root_cc_catalog').value;
	  s.src = nn_cc_root+"ext/novalnet/js/jquery.js";
	  document.getElementsByTagName("head")[0].appendChild(s);
	}
	if (window.addEventListener) { // For all major browsers, except IE 8 and earlier
		window.addEventListener("load", novalnet_cc_load);
	} else if (window.attachEvent) { // For IE 8 and earlier versions
		window.attachEvent("onload", novalnet_cc_load);
	}

/**
 * To performing the page loading and form submiting
 */
	function novalnet_cc_load() {
		var formid= '';
		formid = getFormid();
		jQuery('#'+formid).submit(function (evt) {
			var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();
			var change_account = jQuery('#novalnet_ccchange_account').length ? jQuery('#novalnet_ccchange_account').val() : '';
			if(selected_payment != 'novalnet_cc') { return true; }
			if(jQuery("#nn_pan_hash").val() == "" && ( change_account== '0' || change_account== '') ) {
				evt.preventDefault();
			  cchashcall();
			}
		});

/**
 * To get pan hash from novalnet server
 */
	function cchashcall() {
		var iframe= jQuery('#nnIframe')[0].contentWindow? jQuery('#nnIframe')[0].contentWindow : jQuery('#nnIframe')[0].contentDocument.defaultView;
		iframe.postMessage(JSON.stringify({
			callBack: 'getHash'
		}), 'https://secure.novalnet.de'); // Call the postMessage event for getting the iframe content height dynamically
	}
	
/**
 * To performing oneclick shopping
 */
	var parentClassName = (jQuery( ".nn_cc_ref_details" ).parent().attr('class') == 'input') ? jQuery( ".nn_cc_ref_details" ).parent().attr('class') : jQuery( ".nn_cc_ref_details" ).parent().parent().attr('class');
	jQuery('#novalnet_cc_new_acc').click(function() {
		if (jQuery('.nn_cc_acc').css('display') == 'none') {
			jQuery('.nn_cc_acc').css('display', 'block');
			jQuery('.nn_cc_ref_details').css('display', 'none');
			jQuery('#novalnet_ccchange_account').val('0');
			jQuery("div").find( ".nn_cc_ref_details" ).closest('.'+parentClassName).hide();
			jQuery("div").find( ".nn_cc_acc" ).closest('.'+parentClassName).show();
			jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_given_account').val()+'</b></u>');
		} else {
			jQuery('.nn_cc_ref_details').css('display', 'block');
			jQuery("div").find( ".nn_cc_ref_details" ).closest('.'+parentClassName).show();
			jQuery("div").find( ".nn_cc_acc" ).closest('.'+parentClassName).hide();
			jQuery('.nn_cc_acc').css('display', 'none');
			jQuery('#novalnet_ccchange_account').val('1');
			jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_new_account').val()+'</b></u>');
		}
	});
	if (jQuery('#novalnet_ccchange_account').val() != undefined) {
		if (jQuery('#novalnet_ccchange_account').val() == 1 || jQuery('#novalnet_ccchange_account').val() == '') {
		  jQuery('.nn_cc_acc').css('display', 'none');
		  jQuery('.nn_cc_ref_details').css('display', 'block');
		  jQuery('.nn_cc_ref_details').css('margin', '2% 0% 0% 0%');
		  jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_new_account').val()+'</b></u>');
		} else {
		  jQuery("div").find( ".nn_cc_ref_details" ).closest('.'+parentClassName).hide();
		  jQuery('.nn_cc_acc').css('display', 'block');
		  jQuery('.nn_cc_ref_details').css('display', 'none');
		  jQuery('#novalnet_cc_new_acc').html('<u><b>'+jQuery('#nn_lang_cc_given_account').val()+'</b></u>');
		}
	}
	if($( "div" ).hasClass( "checkout-payment-form" )){
		if($('.checkout-payment-form').css('display') == 'none'){
		 $('.checkout-payment-form').css('display','block');
	    }
	 }
}

/**
 * Peform addEventListener
 */
	if (window.addEventListener) {
		// addEventListener works for all major browsers
		window.addEventListener('message', function(e) {
			assignHash(e);
		}, false);
	} else {
		// attachEvent works for IE8
		window.attachEvent('onmessage', function(e) {
			assignHash(e);
		});
	}

/**
 * Function to handle Event Listener
 */
	function assignHash(e) {
		if (e.origin === 'https://secure.novalnet.de') { // To check the message listener origin with the iframe host
			var data = (typeof e.data === 'string') ? eval('(' + e.data.replace(/(<([^>]+)>)/gi, "") + ')') : e.data; // Convert message string to object
			if (data['callBack'] == 'getHash') { // To check the eventListener message from iframe for hash
				if (data['error_message'] != undefined) {
					alert(jQuery('<textarea />').html(data['error_message']).text());
					return false;
				} else {
					jQuery('#nn_pan_hash').val(data['hash']);
					jQuery('#nn_cc_uniqueid').val(data['unique_id']);
					 if (jQuery('#nn_pan_hash').val() != '') {
						var formid= '';
						formid = getFormid();
						jQuery('#'+formid).submit();
					 }else{
						alert(jQuery('<textarea />').html(data['error_message']).text());
						return false;
					 }
				}
			} else if (data['callBack'] == 'getHeight') { // To check the eventListener message from iframe to get the iframe content height
				jQuery('#nnIframe').attr('height', data['contentHeight']);// set the content height to the iframe height ### nnIframe => iframe Id
			}
		}
	}
	/**
	 * Get formid to sumbit form 
	 */ 
	 function getFormid() {
		 var formid = '';
		jQuery('form').each(function() {
			if(jQuery(this).attr('id') == 'checkout_payment') {
				formid = jQuery(this).attr('id');
			}
		});
		if(formid == '') {
			formid = jQuery('#nn_root_cc_catalog').closest('form').attr('id');
		 }
		 if(formid == undefined ) {
			jQuery('#nn_root_cc_catalog').closest('form').attr('id', 'checkout_payment');
			formid = jQuery('#nn_root_cc_catalog').closest('form').attr('id');
		}
		return formid;
	 }
	
/**
 * To performing the page loading and get iframe in novalnet server
 */
	function getIframeForm() {
		var styleObj = {
			labelStyle: (jQuery('#nn_css_standard').val()) ? jQuery('#nn_css_standard').val() : '',
			inputStyle: (jQuery('#nn_css_standard_input').val()) ? jQuery('#nn_css_standard_input').val() : '',
			styleText: (jQuery('#nn_css_text').val()) ? jQuery('#nn_css_text').val() : '',
			card_holder: {
				labelStyle: (jQuery('#nn_css_card_holder').val()) ? jQuery('#nn_css_card_holder').val() : '',
				inputStyle: (jQuery('#nn_css_holder_input').val()) ? jQuery('#nn_css_holder_input').val() : '',
			},
			card_number: {
				labelStyle: (jQuery('#nn_css_card_no').val()) ? jQuery('#nn_css_card_no').val() : '',
				inputStyle: (jQuery('#nn_css_card_no_input').val()) ? jQuery('#nn_css_card_no_input').val() : '',
			},
			expiry_date: {
				labelStyle: (jQuery('#nn_css_expiry_date').val()) ? jQuery('#nn_css_expiry_date').val() : '',
				inputStyle: (jQuery('#nn_css_expiry_date_input').val()) ? jQuery('#nn_css_expiry_date_input').val() : '',
			},
			cvc: {
				labelStyle: (jQuery('#nn_css_cvc').val()) ? jQuery('#nn_css_cvc').val() : '',
				inputStyle: (jQuery('#nn_css_cvc_input').val()) ? jQuery('#nn_css_cvc_input').val() : '',
			}
		};
		var textObj   = {
			card_holder: {
				labelText: (jQuery('#nn_iframe_holder_label').val()) ? jQuery('#nn_iframe_holder_label').val() : '',
				inputText: (jQuery('#nn_iframe_holder_input').val()) ?jQuery('#nn_iframe_holder_input').val() : '',
			},
			card_number: {
				labelText: (jQuery('#nn_iframe_number_label').val()) ? jQuery('#nn_iframe_number_label').val() : '',
				inputText: (jQuery('#nn_iframe_number_input').val()) ? jQuery('#nn_iframe_number_input').val() : '',
			},
			expiry_date: {
				labelText: (jQuery('#nn_iframe_expire_label').val()) ? jQuery('#nn_iframe_expire_label').val() : '',
				inputText: (jQuery('#nn_iframe_expire_input').val()) ? jQuery('#nn_iframe_expire_input').val() : '',
			},
			cvc: {
				labelText: (jQuery('#nn_iframe_cvc_label').val()) ? jQuery('#nn_iframe_cvc_label').val() : '',
				inputText: (jQuery('#nn_iframe_cvc_input').val()) ? jQuery('#nn_iframe_cvc_input').val() : '',
			},
			cvcHintText: (jQuery('#nn_iframe_cvc_hint').val()) ? jQuery('#nn_iframe_cvc_hint').val() : '',
			errorText: (jQuery('#nn_iframe_error').val()) ? jQuery('#nn_iframe_error').val() : '',
		};
		var requestObj = {
			callBack: 'createElements',
			customText: textObj,
			customStyle: styleObj
		};
		var iframe= jQuery('#nnIframe')[0].contentWindow? jQuery('#nnIframe')[0].contentWindow : jQuery('#nnIframe')[0].contentDocument.defaultView;
		iframe.postMessage(JSON.stringify(requestObj), 'https://secure.novalnet.de');
		iframe.postMessage(JSON.stringify({callBack: 'getHeight'}), 'https://secure.novalnet.de');
	}


