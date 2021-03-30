<?php
/**
 * Novalnet payment method module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * @category   PHP
 * @package    NovalnetGateway
 * @author     Novalnet AG
 * @copyright  Copyright by Novalnet
 * @license    https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script : novalnet.php
 *
 */
define('MODULE_PAYMENT_NOVALNET_TRUE', 'true');
define('MODULE_PAYMENT_NOVALNET_FALSE', 'false');
define('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE','<b>Novalnet Global Configuration</b> (V_11.2.0)');
define('MODULE_PAYMENT_NOVALNET_SELECT','-- SELECT -- ');
define('MODULE_PAYMENT_NOVALNET_NO_SCRIPT','Please enable the Javascript in your browser to proceed further with the payment');
define('MODULE_PAYMENT_NOVALNET_OPTION_NONE','None');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK','PIN by callback');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS', 'PIN by SMS');
define('MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY','This is processed as a guarantee payment');
define('MODULE_PAYMENT_NOVALNET_MENTION_GUARANTEE_PAYMENT_PENDING_TEXT','Your order is under verification and once confirmed, we will send you our bank details to where the order amount should be transferred. Please note that this may take upto 24 hours.');
define('MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_PENDING_TEXT','Your order is under verification and once confirmed, we will send you our bank details to where the order amount should be transferred. Please note that this may take upto 24 hours.');

define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_CALLBACK_INPUT_TITLE','Telephone number');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_INPUT_TITLE','Mobile number');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_INFO' ,'You will shortly receive a transaction PIN through phone call to complete the payment');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO','You will shortly receive an sms containing your transaction PIN to complete the payment');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC', 'Transaction PIN');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN', '&nbsp; Forgot your PIN?');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_EMPTY', 'Enter your PIN');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID', 'The PIN you entered is incorrect');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_ERROR', 'The order amount has been changed, please proceed with the new order');
define('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_FUNC_ERROR', 'Mentioned PHP Package(s) not available in this Server. Please enable it.<br/>');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR', 'Please enter your telephone number ');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_ERROR','Please enter your mobile number');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFUND_CONFIRM','Are you sure you want to refund the amount?');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZERO_AMOUNT_BOOK_CONFIRM','Are you sure you want to book the order amount?');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_CAPTURE_CONFIRM','Are you sure you want to capture the payment?');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_VOID_CONFIRM','Are you sure you want to cancel the payment?');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ID','Novalnet transaction ID: ');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE','Test order'); 
define('MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG','<span style="color:red;">The payment will be processed in the test mode therefore amount for this transaction will not be charged</span>');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE','The amount is invalid');
define('MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR','Your account details are invalid');
define('MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR','Please fill in all the mandatory fields');
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_TITLE','<b>Manage transaction process</b>');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT', 'Please select status');
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','The transaction has been confirmed on %s, %s');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','Novalnet callback received. The transaction has been confirmed on %s, %s');
define('MODULE_PAYMENT_NOVALNET_INVOICE_ON_HOLD_CONFIRM_TEXT','The transaction has been confirmed successfully for the TID: %s and the due date updated as %s');
define('MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE','The transaction has been canceled on %s, %s');
define('MODULE_PAYMENT_NOVALNET_REFUND_AMT_TITLE','Please enter the refund amount');
define('MODULE_PAYMENT_NOVALNET_REFUND_TITLE','Refund process');
define('MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG','The refund has been executed for the TID: %s with the amount of %s');
define('MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG',' .Your new TID for the refund amount: %s');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_EX',' (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT','Confirm');
define('MODULE_PAYMENT_NOVALNET_BACK_TEXT', 'Back');
define('MODULE_PAYMENT_NOVALNET_AMOUNT', 'Amount: ');
define('MODULE_PAYMENT_NOVALNET_UPDATE_TEXT','Update');
define('MODULE_PAYMENT_NOVALNET_CANCEL_TEXT','Cancel');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION', '--Select--');
define('MODULE_PAYMENT_NOVALNET_BOOK_TITLE','Book transaction');
define('MODULE_PAYMENT_NOVALNET_IBAN','IBAN');
define('MODULE_PAYMENT_NOVALNET_INV_PRE_ACCOUNT_HOLDER','Account holder: ');
define('MODULE_PAYMENT_NOVALNET_DUE_DATE','Due date: ');
define('MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE','Transaction booking amount');
define('MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE','Your order has been booked with the amount of %s. Your new TID for the booked amount: %s');
define('MODULE_PAYMENT_NOVALNET_PAYMENTTYPE_NONE', 'None');
define('MODULE_PAYMENT_NOVALNET_SEPA_TEXT','Direct Debit SEPA');
define('MODULE_PAYMENT_NOVALNET_BIC', ' BIC');
define('MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER','Account holder');
define('MODULE_PAYMENT_NOVALNET_MAP_PAGE_HEADER', 'Login here with Novalnet merchant credentials. For the activation of new payment methods please contact <a href="mailto:support@novalnet.de">support@novalnet.de</a>');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR','Payment was not successful. An error occurred.');
define('MODULE_PAYMENT_NOVALNET_REFUND_REFERENCE_TEXT','Refund reference');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR', 'While redirecting some data has been changed. The hash check failed');
define('MODULE_PAYMENT_NOVALNET_BANK','Bank: ');
define('MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TITLE', 'Transaction Amount');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_TITLE' ,'Change the  amount / due date');
define('MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_TITLE','Transaction due date');
define('MODULE_PAYMENT_NOVALNET_TRANS_SLIP_EXPIRY_DATE','Slip expiry date ');
define('MODULE_PAYMENT_NOVALNET_NEAREST_STORE_DETAILS','Store(s) near you');
define('MODULE_PAYMENT_GUARANTEE_FIELD','Your date of birth');
define('MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE','The date should be in future');
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF','Payment Reference:');
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF_MULTI','Payment Reference%s :');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT','Please use the following payment reference for your money transfer, as only through this way your payment is matched and assigned to the order:');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT','Please use any one of the following references as the payment reference, as only through this way your payment is matched and assigned to the order:');
define('MODULE_PAYMENT_NOVALNET_ORDER_NUMBER',' Order number ');
define('MODULE_PAYMENT_NOVALNET_INVOICE_COMMENTS_PARAGRAPH', 'Please transfer the amount to the below mentioned account details of our payment processor Novalnet');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TITLE', 'Enable fraud prevention');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_DESC', 'To authenticate the buyer for a transaction, the PIN will be automatically generated and sent to the buyer. This service is only available for customers from DE, AT, CH');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_AGE_ERROR_MESSAGE','You need to be at least 18 years old');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_DOB_EMPTY_ERROR_MESSAGE','Please enter your date of birth');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_INVAILD_DOB_ERROR_MESSAGE','The date format is invalid');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_DOB_ERROR_MESSAGE','Please enter valid birthdate');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE','Amount update');
define('MODULE_PAYMENT_NOVALNET_SLIP_DATE_CHANGE_TITLE' ,'Change the amount / slip expiry date');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT','Are you sure you want to change the order amount?');
define('MODULE_PAYMENT_NOVALNET_CHANGE_SLIP_DATE','Are you sure you want to change the slip expiry date? ');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_DATE_UPDATE_TEXT','Are you sure you want to change the order amount or due date?');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_SLIP_DATE_UPDATE_TEXT','Are you sure you want to change the order amount / slip expity date?');
define('MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE','The transaction has been updated with amount %s and due date with %s');
define('MODULE_PAYMENT_NOVALNET_SEPA_TRANS_AMOUNT_UPDATED_MESSAGE','The transaction amount %s has been updated successfully on %s, %s');
define('MODULE_PAYMENT_DUE_DATE_INVAILD','Invalid due date');
define('MODULE_PAYMENT_INVOICE_CREDIT_COMMENTS','Novalnet Callback Script executed successfully for the TID: %s with amount: %s on %s & %s. Please refer PAID transaction in our Novalnet Merchant Administration with the TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS','Novalnet callback received. Chargeback executed successfully for the TID: %s amount: %s on %s & %s. The subsequent TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_BOOKBACK_COMMENTS','Novalnet callback received. Refund/Bookback executed successfully for the TID: %s amount: %s on %s & %s. The subsequent TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS', 'Novalnet Callback Script executed successfully for the TID: %s with amount %s on %s & %s.');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS','Next charging date: %s ');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_REFERENCE_TID_COMMENTS',' Reference TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_RECURRING_COMMENTS' ,'Novalnet Callback Script executed successfully for the TID: %s with amount %s on  %s %s . Please refer PAID transaction in our Novalnet Merchant Administration with the TID: %s.');
define('MODULE_PAYMENT_NOVALNET_CANCEL_ORDER_MESSAGE' ,'The transaction has been canceled due to: %s');
define('MODULE_PAYMENT_NOVALNET_TEST_TRANSACTION_EMAIL_HEADING' ,'Novalnet test order notification-%s ');
define('MODULE_PAYMENT_NOVALNET_TEST_TRANSACTION_EMAIL_CONTENT' ,'Dear client,<br/> <p>&emsp;We would like to inform you that test order %s has been placed in your shop recently.Please make sure your project is in LIVE mode at Novalnet administration portal and Novalnet payments are enabled in your shop system. Please ignore this email if the order has been placed by you for testing purpose.</p><br/> Regards,<br/> Novalnet AG');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_PENDING_TO_HOLD_MESSAGE','Novalnet callback received. The transaction status has been changed from pending to on hold for the TID: %s on %s %s.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_CANCELLED_MESSAGE','Novalnet callback received. The transaction has been canceled on %s %s');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_MESSAGE','We are pleased to inform you that your order has been confirmed.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_SUBJECT','Order Confirmation - Your Order %s with %s has been confirmed!');
define('MODULE_PAYMENT_ONLINE_TRANSFER_CREDIT_COMMENTS','The amount of amount_currency for the order order_no has been paid. Please verify received amount and TID details, and update the order status accordingly.');
define('MODULE_PAYMENT_NOVALNET_ORDER_MAIL_SUBJECT', 'Your Order %s, %s, %s');
$novalnet_temp_status_text = 'NN payment pending';
define('MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT_TEXT', '<span style="color:red">This order will be processed as zero amount booking which store your payment data for further online purchases.</span>');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_ADDRESS','The payment cannot be processed, because the basic requirements for the payment guarantee haven'."'".'t been met (The shipping address must be the same as the billing address)');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_COUNTRY','The payment cannot be processed, because the basic requirements for the payment guarantee haven'."'".'t been met (Only Germany, Austria or Switzerland are allowed)');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_AMOUNT','The payment cannot be processed, because the basic requirements for the payment guarantee haven'."'".'t been met (Minimum order amount must be ');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_CURRENCY','The payment cannot be processed, because the basic requirements for the payment guarantee haven'."'".'t been met (Only EUR currency allowed)');
define('MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE_TITLE','Payment action');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE_TITLE','Payment action');
define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_TITLE','Payment action');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_TITLE','Payment action');
define('MODULE_PAYMENT_NOVALNET_SEPA_AUTHENTICATE_TITLE','Payment action');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_AUTHENTICATE_TITLE','Payment action');
if($_SESSION['GX'] == '1'){
	define('MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE_DESC','Authorize');
	define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE_DESC','Authorize');
	define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_DESC','Authorize');
	define('MODULE_PAYMENT_NOVALNET_SEPA_AUTHENTICATE_DESC','Authorize');
	define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_AUTHENTICATE_DESC','Authorize');
	define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_DESC','Authorize');
}
define('MODULE_PAYMENT_NOVALNET_CAPTURE','Capture');
define('MODULE_PAYMENT_NOVALNET_AUTHORIZE','Authorize');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT_TITLE','<b>Choose your instalment plan</b>');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT_DESC','<p>Choose the financing option that best fits your needs and you will be charged based on that chosen plan</p>');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT','Net loan amount:');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_CALLBACK_COMMENT','The next instalment cycle have arrived for the instalment order %s placed at the store novalnet, kindly refer further details below.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INSTALMENTS_INFO','Instalment Information: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_PROCESSED_INSTALMENTS','Processed instalments: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_DUE_INSTALMENTS','Due instalments: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_NEXT_INSTALMENT_AMOUNT','Instalment Cycle Amount: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_PAYMENT_REF','Please use the following reference as the payment reference, as only through this way your payment is matched and assigned to the order. Payment Reference');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_AMOUNT_DEBIT_TEXT','The instalment amount for this cycle %s %s will be debited from your account in one - three business days.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_CANCEL_TEXT','Cancel');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_CANCEL_ADMIN_TEXT','Instalment cancel');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_MAIL_SUBJECT','Instalment confirmation at  %s order no: %s');
