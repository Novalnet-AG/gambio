/**
 * Novalnet novalnet api call Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */
	if(typeof(jQuery) == undefined || typeof(jQuery) == 'undefined' || typeof(jQuery) == 'function' ) {
	  var s = document.createElement("script");
	  s.type = "text/javascript";
	  var nn_cc_root = document.getElementById('nn_api_shoproot').value;
	  s.src = nn_cc_root+"ext/novalnet/js/jquery.js";
	  document.getElementsByTagName("head")[0].appendChild(s);
	}
	if (window.addEventListener) { // For all major browsers, except IE 8 and earlier
		window.addEventListener("load", novalnet_api_load);
	} else if (window.attachEvent) { // For IE 8 and earlier versions
		window.attachEvent("onload", novalnet_api_load);
	}
/**
 * To get the backend field configuration values
 */
function novalnet_api_load() {
    if(jQuery('#gx_configurations').val() == '1'){
	jQuery('input[name="configuration[configuration/MODULE_PAYMENT_NOVALNET_PUBLIC_KEY]"]').attr('id', 'novalnet_public_key');
    jQuery('input[name="configuration[configuration/MODULE_PAYMENT_NOVALNET_VENDOR_ID]"]').attr('id', 'novalnet_vendor_id');
    jQuery('input[name="configuration[configuration/MODULE_PAYMENT_NOVALNET_AUTHCODE]"]').attr('id', 'novalnet_auth_code');
    jQuery('input[name="configuration[configuration/MODULE_PAYMENT_NOVALNET_PRODUCT_ID]"]').attr('id', 'novalnet_product_id');
    jQuery('input[name="configuration[configuration/MODULE_PAYMENT_NOVALNET_TARIFF_ID]"]').attr('id', 'novalnet_tariff_id');
    jQuery('input[name="configuration[configuration/MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY]"]').attr('id', 'novalnet_access_key');    
	}else{
	jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PUBLIC_KEY]"]').attr('id', 'novalnet_public_key');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_VENDOR_ID]"]').attr('id', 'novalnet_vendor_id');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_AUTHCODE]"]').attr('id', 'novalnet_auth_code');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PRODUCT_ID]"]').attr('id', 'novalnet_product_id');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_TARIFF_ID]"]').attr('id', 'novalnet_tariff_id');
    jQuery('input[name="configuration[MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY]"]').attr('id', 'novalnet_access_key');    
	}    
    jQuery('#novalnet_vendor_id,#novalnet_auth_code,#novalnet_product_id,#novalnet_access_key').attr('readonly', true);
   jQuery('#novalnet_public_key').change(function() {
			get_merchant_details();
		});
    
    get_merchant_details();
	return true;    
}

function get_merchant_details() {
		
		var public_key = jQuery.trim(jQuery('#novalnet_public_key').val());
		var language   = jQuery('#nn_language').val();
		if(public_key == '') {
			null_basic_params();
			return false;
		}
		jQuery('#nn_api_config_call').attr('value', 0);
		var data_to_send = {"hash": public_key, 'lang': language}
		if ('XDomainRequest' in window && window.XDomainRequest !== null) {
			var xdr = new XDomainRequest();
			xdr.open('POST' , '../novalnet_auto_config.php');
			xdr.onload = function () {
				process_result( data_to_send );
			};
			xdr.send( data_to_send );
		}else{
			jQuery.ajax({
				type : 'POST',
				url  : '../novalnet_auto_config.php',
				data : data_to_send,
				success: function(data) {
					process_result( data );
				},
				
			});
		}
		return true;
	}


/**
 * To performing unset the vendor configuration details in global configuration
 */
	function null_basic_params() {
		jQuery('#novalnet_vendor_id, #novalnet_auth_code, #novalnet_product_id, #novalnet_access_key').val('');
		jQuery('#novalnet_tariff_id').find('option' ).remove();
		jQuery('#nn_api_config_call').attr('value', 1);
		jQuery('#novalnet_tariff_id').append(jQuery( '<option>', {
			value: '',
			text : '',
		}));
	}

/**
 * To fetch the vendor configuration details in shop backend
 *
 * @param hahs_string
 */
	function process_result(hash_string) {
		
    var result = JSON.parse(hash_string);
    var saved_tariff_id = jQuery('#novalnet_tariff_id').val();
    
    jQuery('#novalnet_tariff_id').replaceWith('<select id="novalnet_tariff_id" name= "configuration[MODULE_PAYMENT_NOVALNET_TARIFF_ID]" ></select>');
    var tariff = result.tariff;
  
    if (tariff != undefined) { 
		
        jQuery('#novalnet_vendor_id').val(result.vendor_id);
        jQuery('#novalnet_auth_code').val(result.auth_code);
        jQuery('#novalnet_product_id').val(result.product_id);
        jQuery('#novalnet_access_key').val(result.access_key);
        jQuery('#novalnet_ajax_complete').attr('value', 1);
        jQuery.each(tariff, function( index, value ) {
            var tariff_val = value.type + '-' + index;
            jQuery('#novalnet_tariff_id').append(jQuery('<option>', {
                 value: jQuery.trim(tariff_val),
                 text: jQuery.trim(value.name)
            }));
            if (saved_tariff_id != undefined && saved_tariff_id == tariff_val) {
                 jQuery('#novalnet_tariff_id').val(tariff_val);
            }
        });
      } else {
        null_basic_params();
        alert(result.status_desc);
    }
   }
