/**
 * Novalnet Direct Debit SEPA Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
*/
if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
    window.addEventListener('load', novalnet_sepa_load);
} else if (window.attachEvent) { // For IE 8 and earlier versions
    window.attachEvent('onload', novalnet_sepa_load);
}

/**
 *  To performing when page is loading and form submiting
 */
function novalnet_sepa_load() {
	 var current_url = new URL($(location).attr("href"));
	 var url = current_url.searchParams.get("payment_error");
		if(url == 'novalnet_sepa'){
		 jQuery('.novalnet_sepa').addClass('active');
	    }
	  $(document).ready(function(){
         $("#novalnet_sepa_iban").change(function(){
          var iban = jQuery('#novalnet_sepa_iban').val();
          jQuery('#nn_sepa_iban').val(iban);
     });
   });   
   
    $(document).ready(function(){
	     $("#about_mandate").hide();
    $("#mandate_confirm").click(function(){
       $("#about_mandate").toggle();
      });
     });
    jQuery('#novalnet_sepa_iban').removeAttr('name');
    jQuery('#novalnet_sepa_bic').removeAttr('name');
    var formid = '';
    jQuery('#novalnet_sepa_dob').attr("placeholder", "DD-MM-YYYY");
    jQuery('form').each(function() {
        jQuery( ".shipping_container" ).on( "click", function() {
            jQuery('#novalnet_sepa_mandate_confirm').prop('checked', false);
            if(jQuery('#novalnet_sepa_mandate_confirm').val() == 1)
                sepa_mandate_unconfirm_process();
        });
        if(jQuery(this).attr('id') == 'checkout_payment') {
            formid = jQuery(this).attr('id');
        }
    });
    
    jQuery( ".shipping_container" ).on( "click", function() {
        jQuery("#novalnet_sepa_new_pin").prop("checked", false);
    });
    jQuery("#novalnet_sepa_fraud_pin").click(function(e) {
        e.stopPropagation();
        (jQuery("#novalnet_sepa_new_pin").prop("checked")) ? jQuery( "#novalnet_sepa_new_pin" ).prop("checked","checked") : jQuery( "#novalnet_sepa_new_pin" ).prop("checked",false) ;
    });
    jQuery("#novalnet_sepa_new_pin").click(function(e) {
        e.stopPropagation();
        (jQuery("#novalnet_sepa_new_pin").prop("checked")) ? jQuery( "#novalnet_sepa_new_pin" ).prop("checked","checked") : jQuery( "#novalnet_sepa_new_pin" ).prop("checked",false) ;
    });
    
    jQuery('#novalnet_sepa_mandate_confirm').click(function(e) {
        e.stopPropagation();
        if(jQuery('#novalnet_sepa_mandate_confirm').prop('checked')) {
            jQuery( '#novalnet_sepa_mandate_confirm' ).prop("checked","checked");
            if (jQuery( '.nn_sepa_acc' ).css('display') == 'block') {
                sepaibanbiccall();
            }
        } else {
            jQuery( '#novalnet_sepa_mandate_confirm' ).prop("checked",false);
            sepa_mandate_unconfirm_process();
        }
        var selected_payment = (jQuery("input[name='payment']").attr('type') == 'hidden') ? jQuery("input[name='payment']").val() : jQuery("input[name='payment']:checked").val();
        if(jQuery('#novalnet_sepa_mandate_confirm').prop('checked')) {
            jQuery( '#novalnet_sepa_mandate_confirm' ).prop("checked","checked");
        } else {
            jQuery( '#novalnet_sepa_mandate_confirm' ).prop("checked",false);
            sepa_mandate_unconfirm_process();
        }

    });
    var parentClassName = (jQuery( ".nn_sepa_ref_details" ).parent().attr('class') == 'input') ? jQuery( ".nn_sepa_ref_details" ).parent().attr('class') : jQuery( ".nn_sepa_ref_details" ).parent().parent().attr('class') ;
    jQuery('#novalnet_sepa_new_acc').click(function() {
        jQuery('#novalnet_sepa_mandate_confirm').attr('checked', false);
        if (jQuery('.nn_sepa_acc').css('display') == 'none') {
           jQuery('.nn_sepa_acc').show();
           jQuery('.nn_sepa_ref_details' ).hide();
           jQuery('#novalnet_sepa_account_holder').removeAttr('placeholder');
           jQuery('#novalnet_sepa_iban').removeAttr('placeholder');
           jQuery('#novalnet_sepa_bic').removeAttr('placeholder');
           jQuery('#novalnet_sepachange_account' ).val('1');
           jQuery("div").find( ".nn_sepa_ref_details" ).closest('.'+parentClassName).hide();
           jQuery("div").find( ".nn_sepa_acc" ).closest('.'+parentClassName).show();
           jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_given_account').val()+'</b></u>');
        } else {
           jQuery("div").find( ".nn_sepa_ref_details" ).closest('.'+parentClassName).show();
           jQuery("div").find( ".nn_sepa_acc" ).closest('.'+parentClassName).hide();
           jQuery('#novalnet_sepa_mandate_confirm').attr('checked', false);
           jQuery('.nn_sepa_acc').hide();
           jQuery('.nn_sepa_ref_details').show();
           jQuery('#novalnet_sepachange_account').val('0');
           jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_new_account').val()+'</b></u>');
        }
    });
    if(jQuery( '#nn_sepa_shopping_type' ) != undefined && jQuery( '#nn_sepa_shopping_type' ).val() == 'ONECLICK' && jQuery('#novalnet_sepachange_account') != undefined && jQuery('#novalnet_sepachange_account').val() == 0  && jQuery('#payment_ref_details').val() != '') {
        jQuery("div").find( ".nn_sepa_acc" ).closest('.'+parentClassName ).hide();
        jQuery( '.nn_sepa_ref_details' ).show();
        if(jQuery('.nn_sepa_acc').length > 0) {
            jQuery('.nn_sepa_acc').hide();
        }
        jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_new_account').val()+'</b></u>');
    } else {
        jQuery('#payment_ref_details').parent().hide();
        if(jQuery('.nn_sepa_ref_details').length > 0) {
            jQuery("div").find( ".nn_sepa_ref_details" ).closest('.'+parentClassName).hide();
            jQuery("div").find( ".nn_sepa_acc" ).closest('.'+parentClassName).show();
        }
        jQuery('#novalnet_sepa_new_acc').html('<u><b>'+jQuery('#nn_lang_given_account').val()+'</b></u>');
    }
    jQuery( '#novalnet_sepa_iban, #novalnet_sepa_bic,  #novalnet_sepa_account_holder' ).on( 'change', function() {
        sepa_mandate_unconfirm_process();
    });
    jQuery('#novalnet_sepa_iban , #novalnet_sepa_bic').keyup(function(){
      $(this).val($(this).val().toUpperCase());
    });
    if($( "div" ).hasClass( "checkout-payment-form" )){
		if($('.checkout-payment-form').css('display') == 'none'){
		 $('.checkout-payment-form').css('display','block');
	    }
	 }
}

/**
 * To unset the sepa form value
 */
function sepa_mandate_unconfirm_process() {
    
    jQuery('#novalnet_sepa_iban_span').html('');
    jQuery('#novalnet_sepa_bic_span').html('');
    jQuery('#novalnet_sepa_mandate_confirm').attr('checked', false);
    jQuery('novalnet_sepa_mandate_confirm').attr("disabled", false);
}

/**
 * To validate the sepa account holder field
 *
 * @param event
 * @param allowSpace
 * return boolean
 */
function account_holder_validate(event, allowSpace) {
   var keycode = ('which' in event) ? event.which : event.keyCode;
   if (allowSpace == true) { var reg = /[^0-9\[\]\/\\#,+@!^()§$~%'"=:;<>{}\_\|*?`]/g  };
   return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 45 ||keycode == 46 || keycode == 8 || (event.ctrlKey == true && keycode == 114) || ( allowSpace == true && keycode == 32))? true : false;
}

/**
 * To validate the input value in sepa form
 *
 * @param input_val
 * return boolean
 */
function validateSpace(input_val) {
    var input = jQuery.trim(input_val.replace(/\b \b/g, ''));
    return jQuery.trim(input.replace(/\s{2,}/g, ''));
}

/**
 *  To remove the special characters in sepa form
 *
 * @param input_val
 * return boolean
 */
function removeUnwantedSpecialCharsSepa(value, req)
{
    if (value != 'undefined' || value != '') {
         value.replace(/^\s+|\s+$/g, '');
        if (req != 'undefined' && req == 'account_holder') {
            return value.replace(/[\/\\|\]\[|#@,+()`'$~%":;*?<>!^{}=_]/g, '');
        }else {
            return value.replace(/[\/\\|\]\[|#@,+()`'$~%.":;*?<>!^{}=_-]/g, '');
        }
    }
}

/**
 * To validate the special characters
 *
 * @param input_val
 * return boolean
 */
function validateSpecialChars(input_val) {
    var re = /[\/\\#,+!^()$~%.":*?<>{}]/g;
    return re.test(input_val);
}




/**
 * To validate the iban bic field
 *
 * @param event
 * @param allowSpace
 * return boolean
 */
function ibanbic_validate(event, allowSpace) {
    var keycode = ('which' in event) ? event.which : event.keyCode;
    var reg = /^(?:[A-Za-z0-9]+$)/; 
    if(allowSpace == true)
      var reg = /^(?:[A-Za-z0-9&\s]+$)/;
    if(event.target.id == 'novalnet_sepa_account_holder')
      var reg = /^(?:[A-Za-z-&.\s]+$)/;
    return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (event.ctrlKey == true && keycode == 114) || ( allowSpace == true && keycode == 32))? true : false;
}




/**
 * validate the fraud_pin
 */
function pin_val(event){
	var keycode = ('which' in event) ? event.which : event.keyCode;
	var reg = /^[a-z0-9]+$/i;
	return (reg.test(String.fromCharCode(keycode)) || keycode == 0 || keycode == 8 || (event.ctrlKey == true && keycode == 114)) ? true : false;
	
}


