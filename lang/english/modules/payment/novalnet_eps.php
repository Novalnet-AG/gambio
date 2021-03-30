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
 * Script : novalnet_eps.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_EPS_TEXT_TITLE', 'eps');
define('MODULE_PAYMENT_NOVALNET_REDIRECT_DESC','After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment<br>Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop');
define('MODULE_PAYMENT_NOVALNET_EPS_ALLOWED_TITLE','Allowed zone(-s)');
define('MODULE_PAYMENT_NOVALNET_EPS_ALLOWED_DESC','This payment method will be allowed for the mentioned zone(-s). Enter the zone(-s) in the following format E.g: DE, AT, CH. In case if the field is empty, all the zones will be allowed.');
define('MODULE_PAYMENT_NOVALNET_EPS_STATUS_TITLE','Enable payment method');
define('MODULE_PAYMENT_NOVALNET_EPS_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_EPS_TEST_MODE_TITLE','Enable test mode');
define('MODULE_PAYMENT_NOVALNET_EPS_TEST_MODE_DESC','The payment will be processed in the test mode therefore amount for this transaction will not be charged');
define('MODULE_PAYMENT_NOVALNET_EPS_VISIBILITY_BY_AMOUNT_TITLE','Minimum value of goods (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_EPS_VISIBILITY_BY_AMOUNT_DESC','Enter the minimum value of goods from which the payment method is displayed to the customer during checkout');
define('MODULE_PAYMENT_NOVALNET_EPS_ENDCUSTOMER_INFO_TITLE','Notification for the buyer');
define('MODULE_PAYMENT_NOVALNET_EPS_ENDCUSTOMER_INFO_DESC','The entered text will be displayed on the checkout page');
define('MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER_TITLE','Define a sorting order');
define('MODULE_PAYMENT_NOVALNET_EPS_SORT_ORDER_DESC','This payment method will be sorted among others (in the ascending order) as per the given sort number.');
define('MODULE_PAYMENT_NOVALNET_EPS_ORDER_STATUS_TITLE','Order completion status');
define('MODULE_PAYMENT_NOVALNET_EPS_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_EPS_PAYMENT_ZONE_TITLE','Payment zone');
define('MODULE_PAYMENT_NOVALNET_EPS_PAYMENT_ZONE_DESC','This payment method will be displayed for the mentioned zone(-s)');
define('MODULE_PAYMENT_NOVALNET_EPS_PUBLIC_TITLE', 'eps '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') ? '<a href="https://www.novalnet.com" target="_blank"/><img title="eps" alt="eps" src="'.DIR_WS_ICONS.'payment/novalnet_eps.png" width=9%></a>':''));
