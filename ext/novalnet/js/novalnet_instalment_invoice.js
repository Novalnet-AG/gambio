/**
 * Novalnet novalnet_invoice Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
 */

 $( window ).load(function() {
  instalment_table_invoice();
});
 jQuery( document ).on( 'change', '#novalnet_global_recurring_period_cycles' , function() {
	  instalment_table_invoice();
 });
 
//show instalment invoice plan details
function instalment_table_invoice() {
		var novalnet_order_cycle_period = $( "#novalnet_global_recurring_period_cycles option:selected" ).val();
		/*check this */
		var order_amount = $( "#order_amount" ).val();
		var total_amount = 0, instalment_due = 0, last_instalment_due = 0;	
		jQuery('#novalnet_instalment_invoice_dob').attr("placeholder", "DD-MM-YYYY");			
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
				$( "#novalnet_instalment_table_invoice thead tr" ).remove();
			}else {
				$( "#novalnet_instalment_table_invoice thead tr" ).remove();
				$( "#novalnet_instalment_table_invoice thead" ).append( "<tr><th>" + 'Instalment number' + "</th><th>" + 'Monthly instalment amount' + "</th></tr>" );
			}
			$( "#novalnet_instalment_table_invoice" ).show(); 
			$( "#novalnet_instalment_table_invoice tbody tr" ).remove();
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
						$( "#novalnet_instalment_table_invoice tbody" ).append( "<tr><td>" + number_text + "</td><td>€" + split_amount + "</td></tr>" );
					} else {
						$( "#novalnet_instalment_table_invoice tbody" ).append( "<tr><td>" + number_text + "</td><td>€" + last_instalment_due + "</td></tr>" );
					}
				}
	}
