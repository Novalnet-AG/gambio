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
 * Script : novalnet_paypal.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEXT_TITLE', 'PayPal');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_REDIRECTION_TEXT_DESCRIPTION','After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment');
define('MODULE_PAYMENT_NOVALNET_REDIRECT_DESC','After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment<br>Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_REDIRECTION_BROWSER_TEXT_DESCRIPTION','Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ALLOWED_TITLE','Allowed zone(-s)');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ALLOWED_DESC','This payment method will be allowed for the mentioned zone(-s). Enter the zone(-s) in the following format E.g: DE, AT, CH. In case if the field is empty, all the zones will be allowed.');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_TITLE','Enable payment method');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_TITLE','Enable test mode');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TEST_MODE_DESC','The payment will be processed in the test mode therefore amount for this transaction will not be charged');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_TITLE','Minimum value of goods (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_VISIBILITY_BY_AMOUNT_DESC','Enter the minimum value of goods from which the payment method is displayed to the customer during checkout');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_MANUAL_CHECK_LIMIT_TITLE', 'Minimum transaction limit for authorization (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_MANUAL_CHECK_LIMIT_DESC', 'In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ENDCUSTOMER_INFO_TITLE','Notification for the buyer');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ENDCUSTOMER_INFO_DESC','The entered text will be displayed on the checkout page');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_TITLE','Define a sorting order');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SORT_ORDER_DESC','This payment method will be sorted among others (in the ascending order) as per the given sort number.');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_TITLE','Order status for the pending payment');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PENDING_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_TITLE','Order completion status');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_TITLE','Shopping type');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_DESC','Select shopping type <br><span style="color:red">In order to use this option you must have billing agreement option enabled in your PayPal account. Please contact your account manager at PayPal.</span>');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK','One click shopping');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ZERO_AMOUNT','Zero amount booking');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_NEW_ACCOUNT', 'Proceed with new PayPal account details');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_GIVEN_ACCOUNT','Given PayPal account details');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_TRANSACTION_TID','PayPal transaction ID');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ORDER_TRANSACTION_TID','Novalnet transaction ID');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ONE_CLICK_TEXT_DESCRIPTION','Once the order is submitted, the payment will be processed as a reference transaction at Novalnet');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_TITLE','Payment zone');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PAYMENT_ZONE_DESC','This payment method will be displayed for the mentioned zone(-s)');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_BLOCK_TITLE','<b>PayPal API Configuration</b>');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_PUBLIC_TITLE', 'PayPal ');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_CLICK_TITLE','Shopping type');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_CLICK_DESC','One click shopping');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ZERO_AMOUNT_TITLE','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_ZERO_AMOUNT_DESC','Zero amount booking');
