/**
 * Novalnet novalnet_sepa Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */
 
 $( window ).load(function() {
  instalment_table_sepa();
});
 jQuery( document ).on( 'change', '#novalnet_global_recurring_period_cycles_sepa' , function() {
	  instalment_table_sepa();
 });

//show instalment sepa plan details
function instalment_table_sepa() {

		var novalnet_order_cycle_period = $( "#novalnet_global_recurring_period_cycles_sepa option:selected" ).val();
		/*check this */
		var order_amount = $( "#order_amount" ).val();
		var total_amount = 0, instalment_due = 0, last_instalment_due = 0;
		jQuery('#novalnet_instalment_sepa_dob').attr("placeholder", "DD-MM-YYYY");			
		total_amount = ( parseFloat( order_amount ) ).toFixed( 2 );
			for ( var i=1;i<=novalnet_order_cycle_period;i++ ) {
					if ( i != novalnet_order_cycle_period ) {
						split_amount = ( parseFloat( total_amount/novalnet_order_cycle_period ) ).toFixed( 2 );
						instalment_due = parseFloat( instalment_due ) + parseFloat( split_amount );
					} else {
						last_instalment_due = ( parseFloat ( total_amount - instalment_due ) ).toFixed( 2 );
					}
			}
			
			
			var number_text = '';
			var final_due = novalnet_order_cycle_period-1;
			if ( novalnet_order_cycle_period == '0' ){
				$( "#novalnet_instalment_table_sepa thead tr" ).remove();
			}else {
				$( "#novalnet_instalment_table_sepa thead tr" ).remove();
				$( "#novalnet_instalment_table_sepa thead" ).append( "<tr><th>" + 'Instalment number' + "</th><th>" + 'Monthly instalment amount' + "</th></tr>" );
			}
			$( "#novalnet_instalment_table_sepa" ).show(); 
			$( "#novalnet_instalment_table_sepa tbody tr" ).remove();
			for ( var j=0;j<novalnet_order_cycle_period;j++ ) {
				if ( $( "#nn_shop_lang" ).val() == 'en' ) {
					if ( j+1 == 1 || j+1 == 21 ) {
							number_text = j+1+"st "+ 'Instalment';
						} else if ( j+1 == 2 || j+1 == 22 ) {
							number_text = j+1+"nd "+'Instalment';
						} else if ( j+1 == 3 ) {
							number_text = j+1+"rd "+'Instalment';
						} else {
							number_text = j+1+"th "+'Instalment';
						}
					} else {
						number_text = j+1+'. '+'Instalment';
					}
					
					if ( j != final_due ) {
						$( "#novalnet_instalment_table_sepa tbody" ).append( "<tr><td>" + number_text + "</td><td>€" + split_amount + "</td></tr>" );
					} else {
						$( "#novalnet_instalment_table_sepa tbody" ).append( "<tr><td>" + number_text + "</td><td>€" + last_instalment_due + "</td></tr>" );
					}
				}
	}

/**
 * Novalnet Direct Debit SEPA Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
*/
if (window.addEventListener) {    // For all major browsers, except IE 8 and earlier
    window.addEventListener('load', novalnet_instalment_sepa_load);
} else if (window.attachEvent) { // For IE 8 and earlier versions
    window.attachEvent('onload', novalnet_instalment_sepa_load);
}

/**
 *  To performing when page is loading and form submiting
 */
function novalnet_instalment_sepa_load() {
	 jQuery('#novalnet_instalment_sepa_iban').keyup(function(){
      $(this).val($(this).val().toUpperCase());
    });
     $(document).ready(function(){
	     $("#about_mandate_instalment").hide();
    $("#mandate_confirm_instalment").click(function(){
       $("#about_mandate_instalment").toggle();
      });
     });
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
