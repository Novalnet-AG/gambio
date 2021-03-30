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
 * Script : novalnet_cc.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE', 'Credit Card');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION','The amount will be debited from your credit card once the order is submitted');
define('MODULE_PAYMENT_NOVALNET_REDIRECT_DESC','After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment<br>Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_TEXT_DESCRIPTION','After the successful verification, you will be redirected to Novalnet secure order page to proceed with the payment<br>Please don&#39;t close the browser after successful payment, until you have been redirected back to the Shop');
define('MODULE_PAYMENT_NOVALNET_CC_ALLOWED_TITLE','Allowed zone(-s)');
define('MODULE_PAYMENT_NOVALNET_CC_ALLOWED_DESC','This payment method will be allowed for the mentioned zone(-s). Enter the zone(-s) in the following format E.g: DE, AT, CH. In case if the field is empty, all the zones will be allowed.');
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_TITLE','Enable payment method');
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_TITLE','Enable test mode');
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_DESC','The payment will be processed in the test mode therefore amount for this transaction will not be charged');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_TITLE','Enable 3D secure');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_DESC','The 3D-Secure will be activated for credit cards. The issuing bank prompts the buyer for a password what, in turn, help to prevent a fraudulent payment. It can be used by the issuing bank as evidence that the buyer is indeed their card holder. This is intended to help decrease a risk of charge-back.');
define('MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT_TITLE', 'Minimum transaction limit for authorization (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE_TITLE','Force 3D secure on predefined conditions');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE_DESC','If 3D secure is not enabled in the above field, then force 3D secure process as per the "Enforced 3D secure (as per predefined filters & settings)" module configuration at the Novalnet Admin Portal. If the predefined filters & settings from Enforced 3D secure module are met, then the transaction will be processed as 3D secure transaction otherwise it will be processed as non 3D secure. Please note that the "Enforced 3D secure (as per predefined filters & settings)" module should be configured at Novalnet Admin Portal prior to the activation here. For further information, please refer the description of this fraud module at "Fraud Modules" tab, below "Projects" menu, under the selected project in Novalnet Admin Portal or contact Novalnet support team.');
define('MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT_DESC', 'In case the order amount exceeds the mentioned limit, the transaction will be set on-hold till your confirmation of the transaction. You can leave the field empty if you wish to process all the transactions as on-hold.');
define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_TITLE','Display AMEX logo');
define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_DESC','Display AMEX logo in checkout page');
define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_TITLE','Display Maestro logo');
define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_DESC','Display Maestro logo in checkout page');
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE','Shopping type');
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC','Select shopping type');
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_TITLE','Minimum value of goods (in minimum unit of currency. E.g. enter 100 which is equal to 1.00)');
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_DESC','Enter the minimum value of goods from which the payment method is displayed to the customer during checkout');
define('MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO_TITLE','Notification for the buyer');
define('MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO_DESC','The entered text will be displayed on the checkout page');
define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_TITLE','Define a sorting order');
define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_DESC','This payment method will be sorted among others (in the ascending order) as per the given sort number.');
define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_TITLE','Order Completion Status');
define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_TITLE','Payment zone');
define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_DESC','This payment method will be displayed for the mentioned zone(-s)');
define('MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK','One click shopping');
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT','Zero amount booking');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE','Credit Card type');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER','Card holder name');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO','Card number');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE','Expiry date');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC','CVC/CVV/CID');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_HINT','what is this?');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_ERROR','Your credit card details are invalid');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_HOLDER_INPUT_TEXT','Name on card');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_NUMBER_INPUT_TEXT','XXXX XXXX XXXX XXXX');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_EXPIRYDATE_INPUT_TEXT','MM / YYYY');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_INPUT_TEXT','XXX');
define('MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS', 'Your credit card details are invalid');
define('MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT', 'Enter new card details');
define('MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT','Given card details');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_TITLE','CSS settings for Credit Card iframe<br><br><span style="font-weight:normal;">Label</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT_TITLE','<span style="font-weight:normal;">Input</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT_TITLE','<span style="font-weight:normal;">CSS Text</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE', 'Credit Card  '. (((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'false') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'false')) ?'<a href="https://www.novalnet.de" target="_blank"/><img title="Credit Card" alt="Credit Card" src="'.DIR_WS_ICONS.'payment/novalnet_cc.png" height="30px"></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'true') &&  ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'false')) ?'<a href="https://www.novalnet.de" target="_blank"/><img title="Credit Card" alt="Credit Card" src="'.DIR_WS_ICONS.'payment/novalnet_visa_master_amex.png" height="30px"></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'true') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'false')) ?'<a href="https://www.novalnet.de" target="_blank"/><img title="Credit Card" alt="Credit Card" src="'.DIR_WS_ICONS.'payment/novalnet_visa_master_maestro.png" height="30px"></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'true') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'true'))?'<a href="https://www.novalnet.de" target="_blank"/><img title="Credit Card" alt="Credit Card" src="'.DIR_WS_ICONS.'payment/novalnet_visa_master_amex_maestro.png" height="30px"></a>':''));
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK_TITLE','Shopping type');
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK_DESC','One click shopping');
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT_TITLE','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT_DESC','Zero amount booking');
