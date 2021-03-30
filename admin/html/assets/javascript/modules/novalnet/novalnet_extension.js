/**
 * Novalnet extension feature Script
 * By Novalnet (https://www.novalnet.de)
 * Copyright (c) Novalnet
*/
/**
 * To check wheather number or not
 *
 * @param event 
 * return boolean
 */
$(document).ready(function(){
   $("#book_amount,#refund_trans_amount,#new_amount").on("paste",function (event) {    
           $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
        });
   $("#book_amount,#refund_trans_amount,#new_amount").on("keypress keyup blur",function (event) {    
           $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }
            });
});

/**
 * To validate void capture option
 *
 * @param event
 * return boolean
 */
function void_capture_status() {
	if (document.getElementById('trans_status').value == '') {
		document.getElementById('nn_void_capture_error').innerHTML=document.getElementsByName("nn_select_status")[0].value;
		return false;
	}
	display_status =  document.getElementById("trans_status").value == 100 ? document.getElementsByName("nn_capture_update")[0].value : document.getElementsByName("nn_void_update")[0].value;
	if (!confirm(display_status)) {
		return false;
	}
	return true;
}

/**
 * To remove the validation message
 * 
 */
 function remove_void_capture_error_message() {
	 document.getElementById('nn_void_capture_error').innerHTML='';
 } 




/**
 * To validate the refund amount field
 */    
function refund_amount_validation() {
	if (document.getElementById('refund_ref') != null) {
		var refund_ref = document.getElementById('refund_ref').value;
		refund_ref = refund_ref.trim();
		var re = /[\/\\#,+!^()$~%.":*?<>{}]/g;
		if (re.test(refund_ref)) {
			document.getElementById('nn_refund_error').innerHTML=document.getElementsByName("nn_valid_account")[0].value;
			return false;
		}
	}
	else {
		var amount = document.getElementById('refund_trans_amount').value;
		if (amount.trim() == '' || amount == 0 || isNaN(amount)) {
			document.getElementById('nn_refund_error').innerHTML= document.getElementsByName("nn_amount_error")[0].value;
			return false;
		}
	}
	if (document.getElementById('refund_payment_type_sepa') && document.getElementById('refund_payment_type_sepa').checked) {
		var accholder = document.getElementById('refund_payment_type_accountholder').value;
		var iban = document.getElementById('refund_payment_type_iban').value;
		var bic = document.getElementById('refund_payment_type_bic').value;
		if (accholder.trim() == '' || iban.trim() == '' ||  bic.trim() == '') {
			document.getElementById('nn_refund_error').innerHTML=document.getElementsByName("nn_valid_account")[0].value;
			return false;
		}
	}
	if (!confirm(document.getElementsByName("nn_refund_amount_confirm")[0].value)) {
		return false;
	}
}

/**
 * To validate the zero amount processing
 *
 * @param event
 */	
function zero_amount_validationt() {
	var bookamount = document.getElementById('book_amount').value;
	if (bookamount.trim() == '' || bookamount == 0 || isNaN(bookamount) ) {
		document.getElementById('nn_zero_amount_error').innerHTML=document.getElementsByName("nn_amount_error")[0].value;
		return false;
	}
	if (!confirm(document.getElementsByName("nn_zero_amount_book_confirm")[0].value)) {
		return false;
	}
}

/**
 * To validate the amount update process
 */
function validate_amount_update() {
	var changeamount = (document.getElementById('new_amount').value).trim();
	if(document.getElementsByName('invoice_payment')[0].value == 1) {
		var invoice_payment_due_date = document.getElementsByName('invoice_payment_due_date')[0].value;
		var date = document.getElementById('amount_change_year').value + '-' + document.getElementById('amount_change_month').value + '-' + document.getElementById('amount_change_day').value;
		var today_date = new Date();
        var current_day_value = ('0' + today_date.getDate()).slice(-2);
        var current_month_value = ('0' + (today_date.getMonth() + 1)).slice(-2);
        var current_year_value = today_date.getFullYear();
        var current_date_value = current_year_value + '-' + current_month_value + '-' + current_day_value;
			if(!is_valid_date(date)) {
				document.getElementById('nn_amount_update_error').innerHTML=document.getElementsByName("nn_duedate_error")[0].value;
				return false;
			}
			if(date < current_date_value) {
				document.getElementById('nn_amount_update_error').innerHTML=document.getElementsByName("nn_duedate_feature_error")[0].value;
				return false;
			}
	}
	if (changeamount == '' || changeamount <= 0 || isNaN(changeamount)) {
		document.getElementById('nn_amount_update_error').innerHTML=document.getElementsByName("nn_amount_error")[0].value;
		return false;
	}
	
	var display_text =  (document.getElementsByName('invoice_payment')[0].value == 1) ? document.getElementsByName("nn_duedate_update")[0].value :document.getElementsByName("nn_order_amount_update")[0].value;
	if (!confirm(display_text)) {
		return false;
	}
}

function is_valid_date(dueDate) {
	if(dueDate ==  '')
		return false;
	var rxDatePattern = /^(\d{4})(\/|-)(\d{1,2})(\/|-)(\d{1,2})$/; //Declare Regex
	var dtArray = dueDate.match(rxDatePattern); // is format OK?
	if (dtArray == null)
		return false;

	//Checks for yyyy/mm/dd format.
	dtYear = dtArray[1];
	dtMonth = dtArray[3];
	dtDay = dtArray[5];
	if (dtMonth < 1 || dtMonth > 12)
		return false;
	else if (dtDay < 1 || dtDay> 31)
		return false;
	else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31)
		return false;
	else if (dtMonth == 2)
	{
		var isleap = (dtYear % 4 ==  0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
		if (dtDay> 29 || (dtDay == 29 && !isleap))
				return false;
	}
	return true;
}

