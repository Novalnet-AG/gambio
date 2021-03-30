<?php
/**
 * Novalnet payment module related file
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * @category   PHP
 * @package    NovalnetGateway
 * @author     Novalnet AG
 * @copyright  Copyright by Novalnet
 * @license    https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script : NovalnetHelper.class.php
 *
 */
include_once(DIR_FS_INC . 'xtc_format_price_order.inc.php');
include_once(DIR_FS_INC . 'xtc_validate_email.inc.php');
include_once (DIR_FS_INC.'xtc_php_mail.inc.php');
class NovalnetHelper {

    /**
     * Generate 30 digit unique string
     *
     * return string
     */
    public static function uniqueRandomString() {
        $randomwordarray = explode(',', '8,7,6,5,4,3,2,1,9,0,9,7,6,1,2,3,4,5,6,7,8,9,0');
        shuffle($randomwordarray);
        return substr(implode($randomwordarray, ''), 0, 16);
    }

    /**
     * Load the language contents from the novalnet package language file (classes directory)
     *
     * @param string $langid
     * @return array
     */
    public static function loadLocaleContents($langid) {
        $lang_file = DIR_WS_LANGUAGES . $langid . '/modules/payment/novalnet.php';
        if (!file_exists($lang_file)) {
            $lang_file = DIR_WS_LANGUAGES .'german/modules/payment/novalnet.php'; // Default language file
        }
        return include_once($lang_file);
    }


    /**
     * Validate the backend configuration and display the error message
     *
     * return boolean
     */
    public static function validateMerchantConfiguration() {
		
		if(self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $_GET['module'])) {
			list( $error,$merchant_api_error,$type) = self::merchantValidate($_GET['module']);
		}
        // To validate the vendor configuration in shop backend
        if ($merchant_api_error && self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $_GET['module']) && empty($_GET['action']) && $_GET['action'] != 'edit'  && $_GET['module'] == 'novalnet_config') { // Through Novalnet Vendor Configuration error message in backend
			
				echo self::displayErrorMessage(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE,'');
			
        }
        if(self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $_GET['module'])) {
			list( $error,$merchant_api_error,$type) = self::backend_validate($_GET['module']);
		}
        
        //To validate the prepayment payment reference in shop backend
		if((defined('MODULE_PAYMENT_NOVALNET_CASHPAYMENT_STATUS') && $_GET['module'] == 'novalnet_cashpayment') || (defined('MODULE_PAYMENT_NOVALNET_INVOICE_STATUS') && $_GET['module'] == 'novalnet_invoice') || (defined('MODULE_PAYMENT_NOVALNET_PREPAYMENT_STATUS') && $_GET['module'] == 'novalnet_prepayment') || (defined('MODULE_PAYMENT_NOVALNET_SEPA_STATUS') && $_GET['module'] == 'novalnet_sepa') || (defined('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_STATUS') && $_GET['module'] == 'novalnet_instalment_sepa') && constant('MODULE_PAYMENT_'.strtoupper($_GET['module']).'_STATUS') == 'true') {
			if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, 'novalnet_cashpayment') && $merchant_api_error && $type == 'cashpayment_due_date') {
				if (!isset($_GET['action']) && $_GET['action'] != 'edit' && $_GET['module'] == 'novalnet_cashpayment') {
					echo self::displayErrorMessage(MODULE_PAYMENT_NOVALNET_CASHPAYMENT_BLOCK_TITLE, $error);
				}
			}else if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, 'novalnet_invoice') && $merchant_api_error && $type == 'invoice_due_date') {
				if (!isset($_GET['action']) && $_GET['action'] != 'edit' && $_GET['module'] == 'novalnet_invoice') {
					echo self::displayErrorMessage(MODULE_PAYMENT_NOVALNET_INVOICE_BLOCK_TITLE, $error);
				}
			}  else if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, 'novalnet_sepa') && $merchant_api_error && $type == 'sepa_due_date') {
				if (!isset($_GET['action']) && $_GET['action'] != 'edit' && $_GET['module'] == 'novalnet_sepa' ) {
					echo self::displayErrorMessage(MODULE_PAYMENT_NOVALNET_SEPA_BLOCK_TITLE, $error);
				}
			}else if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, 'novalnet_instalment_sepa') && $merchant_api_error && $type == 'instalment_sepa_due_date') {
				if (!isset($_GET['action']) && $_GET['action'] != 'edit' && $_GET['module'] == 'novalnet_instalment_sepa' ) {
					echo self::displayErrorMessage(MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_BLOCK_TITLE, $error);
				}
			}else  if( self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $_GET['module']) && ( self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, 'novalnet_sepa') || self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, 'novalnet_invoice') ) && $merchant_api_error ) {
				if($type == 'amount_invaild') {
					if (!isset($_GET['action']) && $_GET['action'] != 'edit' && in_array($_GET['module'] ,array('novalnet_sepa','novalnet_invoice'))) {
						echo self::displayErrorMessage(constant('MODULE_PAYMENT_'.strtoupper($_GET['module']).'_BLOCK_TITLE'), $error);
					}
				}
			}  
		}
		if(self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $_GET['module'])) {
		    if((defined('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND')) &&(constant('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND') == 'true')){
			  if ((MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO != '' && !self::validateEmail(   MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO)) || (MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC != '' && !self::validateEmail(MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC))) {
				echo self::displayErrorMessage(constant('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE'), MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR);
               }
	        }
	         if (!function_exists('base64_encode') || !function_exists('base64_decode') || !function_exists('bin2hex') || !function_exists('pack') || !function_exists('crc32') || !function_exists('md5') || !function_exists('curl_init')) {
			    echo self::displayErrorMessage(constant('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE'), MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_FUNC_ERROR);
            }
	    }
		if ( $merchant_api_error && $type == 'function_exits') {
				if (!isset($_GET['action']) && $_GET['action'] != 'edit' ) {
					echo self::displayErrorMessage(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE,$error);
				}
		}
        return true;
    }
    
    /**
     * Checks for the validations of backend fields
     *
     */
    public static function backend_validate($paymentname) {
	     $invoice_due_date = (defined('MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE') && MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE != '') ? MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE : '' ;
		 $sepa_due_date = (defined('MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE') && MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE != '') ? MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE : '' ;
		 $instalment_sepa_due_date = (defined('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PAYMENT_DUE_DATE') && MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PAYMENT_DUE_DATE != '') ? MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PAYMENT_DUE_DATE : '' ;
	       if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $paymentname)  && $paymentname == 'novalnet_invoice' && $invoice_due_date != '' && !is_numeric(trim($invoice_due_date))) {
			    return array(MODULE_PAYMENT_DUE_DATE_INVAILD,true,'invoice_due_date');
		        }
		    if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $paymentname)  && $paymentname == 'novalnet_sepa' && $sepa_due_date != '' && (!is_numeric(trim($sepa_due_date)) || $sepa_due_date < 2 || $sepa_due_date > 14)) {
			       return array(MODULE_PAYMENT_NOVALNET_SEPA_DUE_DATE_ERROR,true,'sepa_due_date');
		         }
		    if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $paymentname)  && $paymentname == 'novalnet_instalment_sepa' && $instalment_sepa_due_date != '' && (!is_numeric(trim($instalment_sepa_due_date)) || $instalment_sepa_due_date < 2 || $instalment_sepa_due_date > 14)) {
					return array(MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_DATE_ERROR,true,'instalment_sepa_due_date');       
		         }     
		         
		     if (self::novalnetStringCheck(MODULE_PAYMENT_INSTALLED, $paymentname)  && $paymentname == 'novalnet_cashpayment' && ( defined('MODULE_PAYMENT_NOVALNET_CASHPAYMENT_DUE_DATE') && MODULE_PAYMENT_NOVALNET_CASHPAYMENT_DUE_DATE != '' ) && !is_numeric(trim(MODULE_PAYMENT_NOVALNET_CASHPAYMENT_DUE_DATE))) {
			       return array(MODULE_PAYMENT_DUE_DATE_INVAILD,true,'cashpayment_due_date');
		        }
		    if (in_array($paymentname, array('novalnet_invoice','novalnet_sepa')) &&
		     defined('MODULE_PAYMENT_'.strtoupper($paymentname).'_GUARANTEE') == 'true' && (constant('MODULE_PAYMENT_'.strtoupper($paymentname).'_GUARANTEE_MINIMUM_ORDER_AMOUNT') != '' )) {
			     if(trim(constant('MODULE_PAYMENT_'.strtoupper($paymentname).'_GUARANTEE_MINIMUM_ORDER_AMOUNT')) != '') {
				    if (!preg_match('/^\d+$/',constant('MODULE_PAYMENT_'.strtoupper($paymentname).'_GUARANTEE_MINIMUM_ORDER_AMOUNT'))) {
					    return array(MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE,true,'amount_invaild');
				  }
			}
		}
    }

    /**
     * Checks for the given string in given text
     *
     * @param  string $value
     * @param  string $data
     * @return boolean
     */
    public static function novalnetStringCheck($value, $data = 'novalnet') {
        return (strpos($value, $data) !== false);
    }


    /**
     * Validate the payment configuration and hide in checkout page
     *
     * @param string $payment_name
     * @return boolean
     */
    public static function merchantValidate($payment_name) {
		$pattern = "/^\d+\|\d+\|[\w-]+\|\w+\|\w+\|(|\d+)\|(|\d+)\|(|\d+)\|(|\w+)\|(|\w+)$/";
        $value   = defined('MODULE_PAYMENT_NOVALNET_VENDOR_ID') . '|' . defined('MODULE_PAYMENT_NOVALNET_PRODUCT_ID') . '|' . defined('MODULE_PAYMENT_NOVALNET_TARIFF_ID') . '|' . defined('MODULE_PAYMENT_NOVALNET_AUTHCODE') . '|' . defined('MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY') . '|' . defined('MODULE_PAYMENT_NOVALNET_REFERRER_ID') . '|' . defined('MODULE_PAYMENT_NOVALNET_GATEWAY_TIMEOUT') . '|' . defined('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2_AMOUNT') . '|' . defined('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD2') . '|' . defined('MODULE_PAYMENT_NOVALNET_TARIFF_PERIOD');
        $sepa_due_date = (defined('MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE') && MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE != '') ? MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE : '' ;
        $invoice_due_date = (defined('MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE') && MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE != '') ? MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE : '' ;
        preg_match($pattern, $value, $match);
        if(defined('MODULE_PAYMENT_NOVALNET_PUBLIC_KEY') && (MODULE_PAYMENT_NOVALNET_PUBLIC_KEY == '')) {
			return array(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE,true,'basic_configuration');
        } else if (empty($match[0])) {
			return array(MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE,true,'basic_configuration');
		}
		
        return false;
    }

    /**
     * Show error message in backend
     *
     * @param string $error_payment_name
     * @param string $other_error
     * @return string
     */
    public static function displayErrorMessage($error_payment_name, $other_error) {
        $_SESSION['flag'] = true;
        return '<div class="message_stack_container"><div class = "alert alert-danger">' . $error_payment_name . '<br/><br/>' . ($other_error != '' ? $other_error : MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR) . '<button type="button" class="close" data-dismiss="alert">×</button></div></div>';
    }

    /**
     * Validate E-mail address
     *
     * @param string $emails
     * @return boolean
     */
    static function validateEmail($emails) {
        $email = explode(',', $emails);
        foreach ($email as $value) {
            if (!xtc_validate_email($value))
                return false;
        }
        return true;
    }

    /**
     * select the last successfull payment
     *
     * @param string $payment_name
     */
    public static function getLastSuccessPayment($payment_name) {
        if ($_SESSION['account_type'] != '' && empty($_SESSION['payment']) && (defined('MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION')? MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION :'') == 'true') { // Last successfull payment type
			$sqlQuerySet = xtc_db_fetch_array(xtc_db_query("SELECT payment_type FROM novalnet_transaction_detail WHERE customer_id='". xtc_db_input($_SESSION['customer_id']) ."' ORDER BY id DESC LIMIT 1"));
			if(!empty($sqlQuerySet['payment_type']) && $payment_name == $sqlQuerySet['payment_type'] ) {
				$_SESSION['payment'] = $payment_name;
			}
        }
    }


    /**
     * Validate status of fraud module
     *
     * @param string $payment_name
     * @param string $fraud_module
     * @param int $order_amount
     * @return boolean
     */
    public static function setFraudModuleStatus($payment_name, $fraud_module,$order_amount) {
        global $order;
        $customer_iso_code = strtoupper($order->billing['country']['iso_code_2']);
       
        return  ($fraud_module == 'false' || !in_array($customer_iso_code, array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' )) || (constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_CALLBACK_LIMIT') == '' && is_numeric(constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_CALLBACK_LIMIT')) && constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_CALLBACK_LIMIT') > $order_amount) || (!empty($_SESSION['novalnet'][$payment_name]['guarantee']))) ? 0 : 1;
    }


    /**
     * To get previous account details from database
     *
     * @param integer $customers_id
     * @param string $payment_name
     * @return array
     */
    public static function getPaymentDetails($customers_id, $payment_name) {
		$payment_details = xtc_db_fetch_array(xtc_db_query("SELECT payment_details,process_key,payment_type FROM novalnet_transaction_detail WHERE customer_id='" . xtc_db_input($customers_id) . "' and payment_type = '" . xtc_db_input($payment_name) . "' AND reference_transaction = '0' AND payment_details != '' ORDER BY id DESC LIMIT 1"));
		return $payment_details;
    }

    /**
     * Return payment amount of given order
     *
     * @return integer
     */
    public static function getPaymentAmount() {
        global $order;
        return (sprintf('%0.2f', $order->info['total']) * 100); // Convert into cents
    }

    /**
     * To Visible payment in checkout for  Minimum  order amount
     *
     * @param string $payment_name
     * @param integer $order_amount
     * @return boolean
     */
    public static function hidePaymentVisibility($payment_name, $order_amount) {
		$visibility_amount = constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_VISIBILITY_BY_AMOUNT');
		return ($visibility_amount == '' || (int) $visibility_amount <= (int) $order_amount);
    }

    /**
     * Validate input form fraud module fields
     *
     * @param array $datas
     * @param string $fraud_module
     * @param string $payment_name
     * @return string
     */
    public static function validateCallbackFields($datas, $fraud_module, $payment_name) {
		
		 return ($fraud_module == 'CALLBACK' && (!is_numeric(trim($datas[$payment_name . '_fraud_tel'])))) ?  MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR : ( ($fraud_module == 'SMS' && (!is_numeric($datas[$payment_name . '_fraud_mobile']) || strlen($datas[$payment_name . '_fraud_mobile']) < 8)) ? MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_ERROR : '');
    }

    /**
     * Return address information of given order
     *
     * @param array $datas
     * @return array
     */
    public static function getCustomerAddressInfo($datas) {
        $value = array();
        foreach (array( 'firstname', 'lastname', 'gender', 'street_address', 'city', 'postcode', 'telephone', 'country', 'company' ) as $values)
            $value[$values] = ((isset($datas['billing'][$values]) && $datas['billing'][$values] != '') ? $datas['billing'][$values] : $datas['customer'][$values]);

        $value['country'] = ((isset($datas['billing']['country']['iso_code_2']) && $datas['billing']['country']['iso_code_2'] != '') ? $datas['billing']['country']['iso_code_2'] : $datas['customer']['country']['iso_code_2']);
        return $value;
    }


    /**
     * To get Merchant credentials
     *
     * @return array
     */
    public static function getVendorDetails() {
        $tariff_details = explode('-', MODULE_PAYMENT_NOVALNET_TARIFF_ID);
        return array(
            'vendor' 	  => trim(MODULE_PAYMENT_NOVALNET_VENDOR_ID),
            'auth_code'   => trim(MODULE_PAYMENT_NOVALNET_AUTHCODE),
            'product' 	  => trim(MODULE_PAYMENT_NOVALNET_PRODUCT_ID),
            'tariff' 	  => $tariff_details[1],
            'access_key'  => trim(MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY),
            'tariff_type' => $tariff_details[0],
        );
    }

    /**
     * Get the payment key
     *
     * @param string $payment_name
     * @return integer
     */
    public static function getPaymentKey($payment_name) {
        $payment_key = array(
            'novalnet_cc' 			=> '6',
            'novalnet_prepayment' 	=> '27',
            'novalnet_invoice' 		=> '27',
            'novalnet_instantbank' 	=> '33',
            'novalnet_paypal' 		=> '34',
            'novalnet_sepa' 		=> '37',
            'novalnet_ideal' 		=> '49',
            'novalnet_eps' 			=> '50',
            'novalnet_giropay' 		=> '69',
            'novalnet_przelewy24'   => '78',
            'novalnet_cashpayment'  => '59',
            'novalnet_instalment_invoice'  => '96',
            'novalnet_instalment_sepa'  => '97',
        );
        return $payment_key[$payment_name];
    }

    /**
     * Generate Novalnet gateway parameters based on payment selection
     *
     * @param array $datas
     * @return array
     */
    public static function getCommonParms($datas) {
		global $gx_version;
        include(DIR_FS_CATALOG . 'release_info.php'); // Get shop version
        $payment_name          = ((isset($datas['payment'])) ? $datas['payment'] : $datas['info']['payment_method']);
        $language              = ((isset($_SESSION['language_code'])) ? strtoupper($_SESSION['language_code']) : 'DE');
        $customer_info_address = self::getCustomerAddressInfo($datas);
        $customer_info_details = self::getCustomerdetails($datas['customer']['email_address']);
        $vendor_details        = self::getVendorDetails();
        $remote_ip             = xtc_get_ip_address();
        $system_ip             = $_SERVER['SERVER_ADDR'];
        $urlparam              = array_merge(array(
            'vendor' 				=> $vendor_details['vendor'],
            'product' 				=> $vendor_details['product'],
            'key' 					=> self::getPaymentKey($payment_name),
            'tariff' 				=> $vendor_details['tariff'],
            'auth_code' 			=> $vendor_details['auth_code'],
            'currency' 				=> $datas['info']['currency'],
            'first_name' 			=> $customer_info_address['firstname'],
            'last_name' 			=> $customer_info_address['lastname'],
            'gender' 				=> $customer_info_address['gender'],
            'email' 				=> $datas['customer']['email_address'],
            'street' 				=> $customer_info_address['street_address'],
            'search_in_street' 		=> 1,
            'city' 					=> $customer_info_address['city'], 
            'zip' 					=> $customer_info_address['postcode'],
            'lang' 					=> $language,
            'country' 				=> $customer_info_address['country'],
            'country_code' 			=> $customer_info_address['country'],
            'tel' 					=> $customer_info_address['telephone'],
            'test_mode' 			=> constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_TEST_MODE') == 'true' ? 1 : 0,
            'customer_no' 			=> ((!empty($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : 'guest'),
            'amount' 				=> $datas['payment_amount'],
            'system_name' 			=> 'Gambio',
            'system_version' 		=> $gx_version . '-NN(11.2.0)',
            'system_url' 			=> ((ENABLE_SSL == true) ? HTTPS_SERVER : HTTP_SERVER . DIR_WS_CATALOG),
            'system_ip' 			=> $system_ip,
            'remote_ip' 			=> $remote_ip,
            'notify_url' 			=> MODULE_PAYMENT_NOVALNET_CALLBACK_URL
        ), array_filter(array(
			'fax' 					=> $customer_info_details['customers_fax'],
			'birthdate'				=> $customer_info_details['customers_dob'],
            'company'				=> $customer_info_address['company'],
           
            
        )));
        self::getAffDetails($urlparam); // Appending affiliate parameters
        return $urlparam;
    }

   
    /**
     * Collect Customer DOB, FAX, Gender information from the database
     *
     * @param string $customer_email
     * @return array
     */
	public static function getCustomerdetails($customer_email) {
        if ($customer_email != '') { // Get enduser info from database
            $querySearch = (isset($_SESSION['customer_id']) && $_SESSION['customers_status']['customers_status_id'] != '1') ? 'customers_id= "' . xtc_db_input($_SESSION['customer_id']) . '"' : 'customers_email_address= "' . xtc_db_input($customer_email) . '"';
            $customer_dbvalue = xtc_db_fetch_array(xtc_db_query("SELECT customers_id, customers_cid, customers_gender, customers_dob, customers_fax FROM " . TABLE_CUSTOMERS . " WHERE " . $querySearch . " ORDER BY customers_id DESC LIMIT 1"));
            $customer_dbvalue['customers_dob'] = (!in_array($customer_dbvalue['customers_dob'], array( '0000-00-00 00:00:00', '1000-01-01 00:00:00'))) ? date('Y-m-d', strtotime($customer_dbvalue['customers_dob'])) : '';
            return $customer_dbvalue;
        }
    }

    /**
     * Communicate transaction parameters with Novalnet paygate
     *
     * @param string $paygate_url
     * @param array $datas
     * @param boolean $build_query
     * @return array
     */
    public static function doPaymentCurlCall($paygate_url, $datas, $build_query = true) {
		$paygate_query = ($build_query) ? http_build_query($datas) : $datas;
	    $curl          = curl_init($paygate_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $paygate_query);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $error_no = curl_errno($curl);
        $error    = curl_error($curl);
        curl_close($curl);
        if ($error_no > 0) {
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $_SESSION['payment'] . '&error_message=' . $error, 'SSL', true, false));
        }
        
        return $response;
    }

    /**
     * Validate redirect payment server response
     *
     * @param string $payment_name
     * @param array $payment_response
     * @return array
     */
    public static function validateRedirectResponse($payment_name, $payment_response) {
        if ($payment_response['hash2'] != self::generatemd5Value($payment_response)) {
            $payment_error_return = 'payment_error=' . $payment_name . '&error_message=' . html_entity_decode(MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR);
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
        $payment_response = NovalnetHelper::decodePaygateResponse($payment_response);
        $payment_response = NovalnetHelper::checkPaymentStatus($payment_name, $payment_response);
        
        // Response value stored in session
        return $payment_response;
    }

    /**
     * Form transaction comments and assign session value
     *
     * @param string $payment_name
     * @param array $response
     * @param array $payment_param
     * @return array
     */
    public static function checkPaymentStatus($payment_name, $response,$payment_param = '') {
        global $order;
        $order_comments = $transaction_status_failed = '';
		$redirect_payments = array('novalnet_cc','novalnet_paypal','novalnet_ideal','novalnet_eps','novalnet_instantbank','novalnet_giropay','novalnet_przelewy24');
		$_SESSION['novalnet'][$payment_name] = array(
			'vendor_id'             => in_array($payment_name,$redirect_payments) ? $response['vendor'] : $payment_param['vendor'],
			'product_id'            => in_array($payment_name,$redirect_payments) ? $response['product'] :  $payment_param['product'],
			'tariff_id'             => in_array($payment_name,$redirect_payments) ? $response['tariff'] : $payment_param['tariff'],
			'authcode'              => in_array($payment_name,$redirect_payments) ? $response['auth_code'] : $payment_param['auth_code'],
			'tid'                   => $response['tid'],
			'gateway_response'      => $response,
			'payment_id'            => !empty ($response['key']) ? $response['key'] : (!empty($response['payment_id']) ? $response['payment_id'] : $payment_param['key']),
			'reference_transaction' => $_SESSION['novalnet'][$payment_name]['reference_transaction'] == '1' ? '1' : '0',
			'serialize_data'        => !empty($_SESSION['novalnet'][$payment_name]['serialize_data']) ? $_SESSION['novalnet'][$payment_name]['serialize_data'] : '',
			'test_mode'             => $response['test_mode'],
			'status'                => $response['status'],
			'customer_id'           => $response['customer_no'],
			'intial_order_amount'   => $_SESSION['novalnet'][$payment_name]['nn_order_amount'],
			'process_key'           => ($payment_name == 'novalnet_sepa') ? $_SESSION['novalnet']['novalnet_sepa']['hash'] : '',
			 );
			
        if ($response['status'] == 100 || ($payment_name == 'novalnet_paypal' && $response['status'] == 90)) { // Payment Success
            $order_comments = self::transactionCommentsForm($response, $payment_name );
            if (in_array($payment_name, array( 'novalnet_invoice', 'novalnet_prepayment' ))) {
                list($order_invoice_comments, $bank_details) = self::formInvoicePrepaymentComments($response);
                $_SESSION['novalnet'][$payment_name]['serialize_data'] = serialize($bank_details);
            } else if($payment_name == 'novalnet_cashpayment') {
                $order_invoice_comments .= MODULE_PAYMENT_NOVALNET_TRANS_SLIP_EXPIRY_DATE.date(DATE_FORMAT,strtotime($response['cashpayment_due_date'])).PHP_EOL;
                $order_invoice_comments .= PHP_EOL. MODULE_PAYMENT_NOVALNET_NEAREST_STORE_DETAILS.PHP_EOL;
				$nearest_store =  self::getNearestStore($response,'nearest_store');
				$cashpayment_slip_details = array_merge($nearest_store,array('due_date'       => $response['cp_due_date'],'cp_checkout_token'=> $response['cp_checkout_token']));                
                $_SESSION['novalnet'][$payment_name]['serialize_data'] = serialize($cashpayment_slip_details);
                if (!empty($nearest_store)) {
					$i = 0;
					foreach ($nearest_store as $key => $values) {
						$i++;
						$country_name = xtc_db_fetch_array(xtc_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . $nearest_store['nearest_store_country_'.$i] . "'"));
						if(!empty($nearest_store['nearest_store_title_'.$i])) {
							$order_invoice_comments .= PHP_EOL . $nearest_store['nearest_store_title_'.$i].PHP_EOL;
						}
						if (!empty($nearest_store['nearest_store_street_'.$i])) {
							$order_invoice_comments .= $nearest_store['nearest_store_street_'.$i].PHP_EOL;	
						}
						if(!empty($nearest_store['nearest_store_city_'.$i])) {
							$order_invoice_comments .= $nearest_store['nearest_store_city_'.$i].PHP_EOL;
						}
						if(!empty($nearest_store['nearest_store_zipcode_'.$i])) {
							$order_invoice_comments .= $nearest_store['nearest_store_zipcode_'.$i].PHP_EOL;
						}
						if(!empty($nearest_store['nearest_store_country_'.$i])) {
							$order_invoice_comments .= $country_name['countries_name'].PHP_EOL;
						}
					}
				}         
            }else if (in_array($response['key'], array( '96', '97' ))) {
				$instalment_details = array('instalment_cycle_amount'       => $response['instalment_cycle_amount'],'paid_date'=> date('yy-m-d'),'next_instalment_date'=> $response['next_instalment_date'],'reference_tid'=> $response['tid']);   
				$future_instalment_details[] = $instalment_details;
				$future_instalment_dates = explode('|', $response['future_instalment_dates']);
				array_shift($future_instalment_dates);
					foreach ($future_instalment_dates as $key => $mark) {
						$future_instalment_details[] =  explode('-', $mark, 2);
						$future_instalment_details[$key]['next_instalment_date'] == '' ? $future_instalment_details[$key]['next_instalment_date'] = $future_instalment_details[$key]['1'] : $future_instalment_details[$key]['next_instalment_date'] =$future_instalment_details[$key]['next_instalment_date'];
						unset($future_instalment_details[$key]['1']);
					}
					if($response['key'] ==  '96'){
					 $response['amount'] = $response['instalment_cycle_amount'];	
					 list($order_invoice_comments, $bank_details) = self::formInvoicePrepaymentComments($response);
					 $_SESSION['novalnet'][$payment_name]['payment_ref'] = serialize($order_invoice_comments);
				}
				if($response['tid_status'] ==  '100'){
				$order_invoice_comments .= self::formInstalmentPaymentReference($response);
			}
                $_SESSION['novalnet'][$payment_name]['serialize_data'] = serialize($future_instalment_details);
			}
            $response['novalnet_comments'] = $_SESSION['novalnet'][$payment_name]['novalnet_comments'] = ((($order->info['comments'] != '') ? PHP_EOL . $order->info['comments'] : '')) . $order_comments.(!empty($order_invoice_comments) ? $order_invoice_comments : '');
            return $response;
        } else { // Payment failed 
			$datas = array('payment_type'=>$payment_name,
						'order_no'=> $response['order_no']);
			self::logInitialTransaction($datas);
            unset($_SESSION['novalnet'][$payment_name]['reference_transaction']);
            $order_comments = self::transactionCommentsForm($response, $payment_name );
            $transaction_status_failed = NovalnetHelper::getServerResponse($response);
            $order->info['comments'] = $order_comments.PHP_EOL.$transaction_status_failed;
			xtc_db_perform(TABLE_ORDERS, array(
				'orders_status' => '99',
				'comments' 		=> $order->info['comments']
			), "update", "orders_id='".$response['order_no']."'");
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, array(
				'orders_status_id' => '99',
				'comments' 		   => $order->info['comments']
			), "update", "orders_id='".$response['order_no']."'");
            
            $payment_error_return      = 'payment_error=' . $payment_name . '&error_message=' . $transaction_status_failed;
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
    }

	/**
	 * To get nearest cashpayment store details 
	 * 
	 * @param array $response
	 * @param string $store_name
	 * return array
	 */ 

	public static function getNearestStore($response,$store_name){
		$stores_details = array();
		foreach ($response as $iKey => $stores_details){
			if(stripos($iKey,$store_name)!==FALSE){
				$stores[$iKey] = $stores_details;
			}
		}
		return $stores;
	}

    /**
     * Perform the decoding paygate response process for redirection payment methods
     *
     * @param array $datas
     * @return string
     */
    public static function decodePaygateResponse($datas) {
	    $result = array();
        $data['auth_code'] = $datas['auth_code'];
        $data['tariff']    = $datas['tariff'];
        $data['product']   = $datas['product'];
        $data['amount']    = $datas['amount'];
        $data['test_mode'] = $datas['test_mode'];
        $data['uniqid']    = $datas['uniqid'];
        
        foreach ($data as $key => $value) {
            $result[$key] = self::generateDecode($value,$data['uniqid']); // Decode process
        }
        return array_merge($datas, $result);
    }
    
    /**
     * Perform the decoding process for redirection payment methods
     * @param $data
     *
     * @return string
     */
    static public function generateDecode($data = '',$uniqid)
    {  
        try {
            $data = openssl_decrypt(base64_decode($data), "aes-256-cbc", $_SESSION['novalnet']['nn_access_key'] , true, $uniqid); 
        }
        catch (Exception $e) { // Error log for the exception
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, utf8_decode('error_message=' . $e), 'SSL'));
        }
        return $data;
    }

    /**
     * Validate pin field
     *
     * @param string $payment_module
     * @param array $datas
     * @param string $fraud_module
     */
    public static function validateUserInputsOnCallback($payment_module, $datas, $fraud_module) {
        $datas = array_map('trim', $datas);
        if (in_array($fraud_module, array( 'CALLBACK', 'SMS' ))) {
            if (!isset($datas[$payment_module . '_new_pin']) && isset($datas[$payment_module . '_fraud_pin']) && (!preg_match('/^[a-zA-Z0-9]+$/', $datas[$payment_module . '_fraud_pin']))) {
				$fraud_module_error = $datas[$payment_module . '_fraud_pin'] == '' ? MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_EMPTY : MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID;
                $payment_error_return = 'payment_error=' . $payment_module . '&error_message=' . $fraud_module_error;
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
            }
        }
    }

    /**
     * Redirect to checkout on sucess using fraud module
     *
     * @param string $fraud_module
     * @param boolean $fraud_module_status
     * @param string $payment_type
     */
    public static function gotoPaymentOnCallback($fraud_module, $fraud_module_status, $payment_type) {
        if (in_array($fraud_module, array('CALLBACK', 'SMS')) && $fraud_module_status) {
            $fraudModule_message  = ($fraud_module == 'SMS') ? MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO : MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_INFO;
            $payment_error_return = 'payment_error=' . $payment_type . '&payment_message=' . $fraudModule_message;
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
    }

    /**
     * Perform server XML request
     *
     * @param string $requesttype
     * @param string $payment_type
     * @return array
     */
    public static function doCallbackRequest($requesttype, $payment_type) {
        $vendor_details = self::getVendorDetails();
        $remote_ip      = xtc_get_ip_address();
        $xml            = '<?xml version="1.0" encoding="UTF-8"?>
        <nnxml>
            <info_request>
              <vendor_id>' . $vendor_details['vendor'] . '</vendor_id>
              <vendor_authcode>' . $vendor_details['auth_code'] . '</vendor_authcode>
              <remote_ip>' . $remote_ip . '</remote_ip>
              <request_type>' . $requesttype . '</request_type>
              <tid>' . $_SESSION['novalnet'][$payment_type]['tid'] . '</tid>';
        if ($requesttype == 'PIN_STATUS')
            $xml .= '<pin>' . trim($_SESSION['novalnet'][$payment_type][$payment_type . '_fraud_pin']) . '</pin>';
        $xml .= '</info_request></nnxml>';
        $xml_response = self::doPaymentCurlCall('https://payport.novalnet.de/nn_infoport.xml', $xml, false);
        return $xml_response;
    }

    /**
     * Validate callback amount
     *
     * @param string $payment_module
     * @param string $fraud_module
     */
    public static function validateAmountOnCallback($payment_module, $fraud_module) {
        $orderamount = self::getPaymentAmount();
        if ($_SESSION['novalnet'][$payment_module]['intial_order_amount'] != $orderamount) {
            if (isset($_SESSION['novalnet'][$payment_module])) {
                unset($_SESSION['novalnet'][$payment_module]);
            }
            $payment_error_return = 'payment_error=' . $payment_module . '&error_message=' . MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_ERROR;
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
        if ($payment_module == 'novalnet_sepa' && $fraud_module && (MODULE_PAYMENT_NOVALNET_SEPA_SHOP_TYPE == 'ZEROAMOUNT'|| MODULE_PAYMENT_NOVALNET_SEPA_ZERO_AMOUNT == 'true')) {
			$orderamount = 0;
        }
    }

    /**
     * Return Invoice / Prepayment comments
     *
     * @param array $data
     * @param boolean $bank_details
     * @return array
     */
	public static function formInvoicePrepaymentComments($data) {
		$trans_comments = '';
		if($data['tid_status'] != 75){
			$trans_comments .= PHP_EOL . MODULE_PAYMENT_NOVALNET_INVOICE_COMMENTS_PARAGRAPH . PHP_EOL;
			$trans_comments .= ($data['due_date'] != '') ? MODULE_PAYMENT_NOVALNET_DUE_DATE . date('d.m.Y', strtotime($data['due_date'])) . PHP_EOL : '';
			$trans_comments .= MODULE_PAYMENT_NOVALNET_INV_PRE_ACCOUNT_HOLDER . $data['invoice_account_holder']. PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_IBAN . ': ' . $data['invoice_iban'] . PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_BIC . ': ' . $data['invoice_bic'] . PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_BANK . $data['invoice_bankname'].' '.$data['invoice_bankplace'] .PHP_EOL;
			$trans_comments .= MODULE_PAYMENT_NOVALNET_AMOUNT . xtc_format_price_order($data['amount'], 1, $data['currency']);
		}
        $bank_details = array(
            'tid'            => $data['tid'],
            'account_holder' => $data['invoice_account_holder'],
            'bank_name'      => $data['invoice_bankname'],
            'bank_city'      => $data['invoice_bankplace'],
            'amount'         => $data['amount'] * 100,
            'currency'       => $data['currency'],
            'bank_iban'      => $data['invoice_iban'],
            'bank_bic'       => $data['invoice_bic'],
            'due_date'       => $data['due_date']
        );
        return array( $trans_comments, $bank_details );
    }

    /**
     * Perform order status update with custom order status (as per Merchant selection)
     *
     * @param integer $order_id
     * @param string $payment_name
     */
    public static function updateOrderStatus($order_id, $payment_name, $payment_id = '') {
        global $order;
        if (in_array($payment_name, array( 'novalnet_invoice','novalnet_prepayment' ,'novalnet_instalment_invoice'))) {
           
             if($_SESSION['novalnet'][$payment_name]['gateway_response']['tid_status'] != 75){
				$order->info['comments'] .= self::formInvoicePrepaymentPaymentReference(serialize($_SESSION['novalnet'][$payment_name]['payment_ref']), $payment_name);
			}
        }
        //order status set here
        if(in_array($_SESSION['novalnet'][$payment_name]['gateway_response']['tid_status'], array(75,86,90))){
			$payment_order_status = constant('MODULE_PAYMENT_'.strtoupper($payment_name).'_PENDING_ORDER_STATUS');
		}else if(in_array($_SESSION['novalnet'][$payment_name]['gateway_response']['tid_status'], array(91,99,85,98))){
			$payment_order_status = constant('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE');
		}else if($_SESSION['novalnet'][$payment_name]['gateway_response']['tid_status'] == '103'){
			$payment_order_status = constant('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED');
		}else if($payment_id == '41' && $_SESSION['novalnet'][$payment_name]['gateway_response']['tid_status'] == '100'){
			$payment_order_status = constant('MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS');
		}else{
			$payment_order_status = constant('MODULE_PAYMENT_' . strtoupper($payment_name) . '_ORDER_STATUS');
		}
        $payment_status['orders_status'] = $status_update['orders_status_id'] = $payment_order_status;
        if($_SESSION['novalnet'][$payment_name]['gateway_response']['order_no'] == ''){
			$payment_status['comments'] = $status_update['comments']  = $order->info['comments'];
		}
		// Update the Merchant selected order status in shop
		xtc_db_perform(TABLE_ORDERS, $payment_status, "update", "orders_id='$order_id'");
		xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $status_update, "update", "orders_id='$order_id'");
    }

    /**
     * Build reference comments for invoice
     *
     * @param array $payment_reference
     * @param string $payment_type
     * @param array $datas
     * @return string
     */
    public static function formInvoicePrepaymentPaymentReference($payment_reference, $payment_type, $datas = '') {
		global $insert_id;
        $vendor_details           = NovalnetHelper::getVendorDetails();
       
         // Form payment reference comments
		$payment_references = array_filter(
			array(
			 ' BNR-' . (isset($datas['product']) ? $datas['product'] : $vendor_details['product']) . '-' . (isset($datas['order_no']) ? $datas['order_no'] : $insert_id) => 'payment_reference1',
			 ' TID ' . (isset($datas['tid']) ? $datas['tid'] : $_SESSION['novalnet'][$payment_type]['tid']) => 'payment_reference2',
			)
		);
		
		$i = 1;
		$invpre_comments .= PHP_EOL.PHP_EOL.MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT;
		foreach ($payment_references as $key => $value) {
			$invpre_comments .= sprintf(PHP_EOL.MODULE_PAYMENT_NOVALNET_INVPRE_REF_MULTI, ' ' . $i++).$key;
		}
        return $invpre_comments;
    }
    
    /**
     * Build reference comments for invoice
     *
     * @param array $payment_reference
     * @param string $payment_type
     * @param array $datas
     * @return string
     */
    public static function formInstalmentPaymentReference($data) {
		$trans_comments = '';
		
		if($data['tid_status'] != 75){
		if($data['key'] ==  '96'){
			$trans_comments .= PHP_EOL.MODULE_PAYMENT_NOVALNET_INSTALMENT_PAYMENT_REF.' '.$data['tid']. PHP_EOL;
		}
			$trans_comments .=  MODULE_PAYMENT_NOVALNET_INSTALMENT_INSTALMENTS_INFO.PHP_EOL.MODULE_PAYMENT_NOVALNET_INSTALMENT_PROCESSED_INSTALMENTS.$data['instalment_cycles_executed'] . PHP_EOL;
			$trans_comments .=  MODULE_PAYMENT_NOVALNET_INSTALMENT_DUE_INSTALMENTS.$data['due_instalment_cycles']. PHP_EOL;
			$trans_comments .=  MODULE_PAYMENT_NOVALNET_INSTALMENT_NEXT_INSTALMENT_AMOUNT.$data['instalment_cycle_amount'].' '.$data['currency']. PHP_EOL;
		}
		return $trans_comments;
	}
    

    /**
     * To process Affiliate account
     *
     * @param reference $urlparam
     */
    public static function getAffDetails(&$urlparam) {
        $_SESSION['novalnet']['nn_access_key'] = MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY;
        if ($_SESSION['customer_id'] != '' && $_SESSION['customers_status']['customers_status_id'] != DEFAULT_CUSTOMERS_STATUS_ID_GUEST && (!isset($_SESSION['nn_aff_id']) || empty($_SESSION['nn_aff_id']))) { // To process affliate customers only
            $affilated_details = xtc_db_fetch_array(xtc_db_query('SELECT aff_id FROM novalnet_aff_user_detail WHERE customer_id = "' . xtc_db_input($_SESSION['customer_id']) . '" ORDER BY id DESC LIMIT 1'));
            if (isset($affilated_details['aff_id']) && !empty($affilated_details['aff_id'])) { // Set affilate id
                $_SESSION['nn_aff_id'] = $affilated_details['aff_id']; // Assign affilate id from database
            }
        }
        if (isset($_SESSION['nn_aff_id'])) { // Get affilate details
            $affilated_details = xtc_db_fetch_array(xtc_db_query('SELECT aff_authcode, aff_accesskey FROM novalnet_aff_account_detail WHERE aff_id = "' . xtc_db_input($_SESSION['nn_aff_id']) . '"'));
            if (trim($affilated_details['aff_accesskey'])!= '' && trim($affilated_details['aff_authcode']) != '' && $_SESSION['nn_aff_id'] != '') { // Assign access key for affilate id
                $urlparam['vendor']        = $_SESSION['nn_aff_id'];
                $urlparam['auth_code']     = $affilated_details['aff_authcode'];
                $_SESSION['novalnet']['nn_access_key'] = $affilated_details['aff_accesskey']; // Assign affilate access key
            }
        }
    }

    /**
     *  To proceed to second call for novalnet gateway
     *
     * @param array $datas
     */
    public static function doSecondCallProcess($datas) {
        self::logInitialTransaction($datas); // Insert transaction details to Novalnet table
        
        if (isset($_SESSION['nn_aff_id'])) { // If affilate id customer to insert in transaction details to affliated table
            xtc_db_perform('novalnet_aff_user_detail', array(
                'aff_id' 		=> $_SESSION['nn_aff_id'],
                'customer_id' 	=> $_SESSION['customer_id'],
                'aff_order_no' 	=> $datas['order_no']
            ), 'insert');
            unset($_SESSION['nn_aff_id']);
        }
        if($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['order_no'] == ''){
			self::doPaymentSecondCall($datas); // Order no to update the Novalnet server (do post back call)
		}
    }

	/**
	 * Get payment title
	 *
	 * @param string $payment
	 * @return string
	 */
	public static function getPaymentName($payment) {
		$payment_title = array(
			'novalnet_cc'         => 'CREDITCARD',
			'novalnet_sepa'       => 'DIRECT_DEBIT_SEPA',
			'novalnet_invoice'    => 'INVOICE_START',
			'novalnet_instantbank'=> 'ONLINE_TRANSFER',
			'novalnet_ideal'      => 'IDEAL',
			'novalnet_paypal'     => 'PAYPAL',
			'novalnet_przelewy24' => 'PRZELEWY24',
			'novalnet_prepayment' => 'INVOICE_START',
			'novalnet_eps'        => 'EPS',
			'novalnet_giropay'    => 'GIROPAY',
			'novalnet_cashpayment'=> 'CASHPAYMENT',
			'novalnet_instalment_invoice'  => 'INSTALMENT_INVOICE',
            'novalnet_instalment_sepa'  => 'INSTALMENT_DIRECT_DEBIT_SEPA',
		 );
		return $payment_title[$payment];
	}

    /**
     * To store Novalnet transaction details in table
     *
     * @param array $datas
     */
     public static function logInitialTransaction($datas) {
		$callback_amount = $amount              = !empty($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['amount']) ? str_replace('.', '', $_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['amount']) : $datas['recurring_amount'];
		if(!empty($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['amount']) && !empty($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['tid_status'] && !empty($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['tid_status'] != 100 ))) {
			$callback_amount = 0;
		}
		$payment_name        = self::getPaymentName($datas['payment_type']);
		xtc_db_perform('novalnet_transaction_detail', array(
            'tid'                   => !empty($_SESSION['novalnet'][$datas['payment_type']]['tid']) ? $_SESSION['novalnet'][$datas['payment_type']]['tid'] : $datas['recurring_tid'],
            'vendor'                => !empty($_SESSION['novalnet'][$datas['payment_type']]['vendor_id'])?$_SESSION['novalnet'][$datas['payment_type']]['vendor_id'] : $datas['vendor'],
            'product'               => !empty($_SESSION['novalnet'][$datas['payment_type']]['product_id']) ? $_SESSION['novalnet'][$datas['payment_type']]['product_id'] : $datas['product'],
            'tariff_id'             => !empty($_SESSION['novalnet'][$datas['payment_type']]['tariff_id']) ? $_SESSION['novalnet'][$datas['payment_type']]['tariff_id'] : $datas['tariff_id'] ,
            'auth_code'             => !empty($_SESSION['novalnet'][$datas['payment_type']]['authcode'])? $_SESSION['novalnet'][$datas['payment_type']]['authcode'] : $datas['auth_code'] ,
            'payment_id'            => !empty($_SESSION['novalnet'][$datas['payment_type']]['payment_id']) ? $_SESSION['novalnet'][$datas['payment_type']]['payment_id'] : $datas['payment_id']  ,
            'payment_type'          => $datas['payment_type'],
            'amount'                => (!empty($amount)) ? $amount : 0,
            'currency'              => !empty($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['currency']) ? $_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['currency'] : $datas['currency']  ,
            'gateway_status'        => !empty($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['tid_status']) ? $_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['tid_status'] : $datas['tid_status']  ,
            'order_no'              => !empty($datas['new_order_no']) ? $datas['new_order_no'] : $datas['order_no'],
            'novalnet_order_date'   => date('Y-m-d H:i:s'),
            'test_mode'             => (((isset($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['test_mode']) && $_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['test_mode'] == 1) || ($datas['test_mode'] == 1 )) ? 1 : 0),
            'payment_details'       => !empty($_SESSION['novalnet'][$datas['payment_type']]['serialize_data']) ? $_SESSION['novalnet'][$datas['payment_type']]['serialize_data'] : $datas['invoice_payment_details'],
            'customer_id'           => !empty($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['customer_no']) ? $_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['customer_no'] : $datas['customer_id'] ,
            'reference_transaction' => isset($_SESSION['novalnet'][$datas['payment_type']]['reference_transaction']) ? $_SESSION['novalnet'][$datas['payment_type']]['reference_transaction'] : 0,
            'payment_ref'           => isset($_SESSION['novalnet'][$datas['payment_type']]['payment_ref']) ? serialize($_SESSION['novalnet'][$datas['payment_type']]['payment_ref']) : (!empty($datas['payment_ref']) ? $datas['payment_ref'] : ''),
            'next_payment_date'            => isset($_SESSION['novalnet'][$datas['payment_type']]['next_payment_date'])? $_SESSION['novalnet'][$datas['payment_type']]['next_payment_date'] : (!empty($datas['next_payment_date']) ? $datas['next_payment_date'] : ''),
            'process_key'           => !empty($_SESSION['novalnet'][$datas['payment_type']]['process_key']) ? $_SESSION['novalnet'][$datas['payment_type']]['process_key'] : '',
            'refund_amount'         => !empty($datas['refund_amount']) ? $datas['refund_amount'] : ''), "insert");
        xtc_db_perform('novalnet_callback_history', array(
			 'callback_datetime'    => date('Y-m-d H:i:s'),
			 'payment_type'         => (($_SESSION['novalnet'][$datas['payment_type']]['payment_id'] == 41 ) ? 'GUARANTEED_INVOICE' : (($_SESSION['novalnet'][$datas['payment_type']]['payment_id'] == 40) ? 'GUARANTEED_DIRECT_DEBIT_SEPA' : $payment_name)),
			 'original_tid'         =>   !empty($_SESSION['novalnet'][$datas['payment_type']]['tid']) ? $_SESSION['novalnet'][$datas['payment_type']]['tid'] : $datas['recurring_tid'],
			 'callback_tid'         => '',
			 'order_amount'         => (!empty($amount)) ? $amount : 0,
			 'callback_amount'      =>(in_array($datas['payment_type'], array('novalnet_invoice', 'novalnet_prepayment','novalnet_cashpayment')) || in_array($_SESSION['novalnet'][$datas['payment_type']]['gateway_response']['tid_status'], array(85,86,90)) || (!empty($datas['tid_status']) && $datas['tid_status'] == 90) ) ? '0' : $callback_amount,
			 'order_no'             =>  !empty($datas['new_order_no']) ? $datas['new_order_no'] : $datas['order_no'],
			), "insert");
    }

    /**
     * Perform Paygate second call for updating order_no in novalnet server
     *
     * @param array $datas
     */
    public static function doPaymentSecondCall($datas) {
		$remote_ip      = xtc_get_ip_address();
        $urlparam       = array(
            'vendor' 	=> $_SESSION['novalnet'][$datas['payment_type']]['vendor_id'],
            'product' 	=> $_SESSION['novalnet'][$datas['payment_type']]['product_id'],
            'auth_code'	=> $_SESSION['novalnet'][$datas['payment_type']]['authcode'],
            'tariff'	=> $_SESSION['novalnet'][$datas['payment_type']]['tariff_id'],
            'key' 		=> $_SESSION['novalnet'][$datas['payment_type']]['payment_id'],
            'status' 	=> 100,
            'remote_ip' => $remote_ip,
            'tid' 		=> $_SESSION['novalnet'][$datas['payment_type']]['tid'],
            'order_no' 	=> $datas['order_no']
        );
        if (in_array($datas['payment_type'], array('novalnet_invoice', 'novalnet_prepayment' , 'novalnet_instalment_invoice'))) {
            $urlparam['invoice_ref'] = 'BNR-' . $urlparam['product'] . '-' . $datas['order_no'];
        }
        self::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparam, false);
        
        if (isset($_SESSION['novalnet'])) {
            unset($_SESSION['novalnet']);
        }
    }

   /**
     * Perform HASH Generation process for redirection payment methods
     * @param $datas
     *
     * @return string
     */
    static public function generateHashValue($datas)
    {
        foreach (array('auth_code', 'product', 'tariff', 'amount', 'test_mode') as $key) {
            $datas[$key] = self::generateEncode($datas[$key],$datas['uniqid']); // Encoding process
        }
        $datas['hash'] = self::generatemd5Value($datas); // Generate hash value
        return $datas;
    }
     /*
     * Perform the encoding process for redirection payment methods
     * @param $data
     *
     * @return string
     */
    static public function generateEncode($data = '',$uniqid)
    {
        try {
             $data = htmlentities(base64_encode(openssl_encrypt($data, "aes-256-cbc", $_SESSION['novalnet']['nn_access_key'], true, $uniqid)));
            }
        catch (Exception $e) { // Error log for the exception
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, utf8_decode('error_message=' . $e), 'SSL'));
        }
        return $data;
    }
    /**
     * Get hash value
     * @param $datas
     *
     * @return string
     */
     static public function generatemd5Value($datas)
    {
        return hash('sha256', ($datas['auth_code'].$datas['product'].$datas['tariff'].$datas['amount'].$datas['test_mode'].$datas['uniqid'].strrev($_SESSION['novalnet']['nn_access_key'])));
    }  
      
     /**
     * Get the redirect payment params
     * @param $params
     *
     * @return none
     */
    static public function getRedirectParams(&$params)
    {
        $encoded_values = self::generateHashValue(array(
            'auth_code' => $params['auth_code'],
            'product'   => $params['product'],
            'tariff'    => $params['tariff'],
            'amount'    => $params['amount'],
            'test_mode' => $params['test_mode'],
            'uniqid'    => self::uniqueRandomString()
        ));

        $params['implementation']  = 'ENC';
        $params['auth_code']       = $encoded_values['auth_code'];
        $params['product']         = $encoded_values['product'];
        $params['tariff']          = $encoded_values['tariff'];
        $params['amount']          = $encoded_values['amount'];
        $params['test_mode']       = $encoded_values['test_mode'];
        $params['uniqid']          = $encoded_values['uniqid'];
        $params['hash']            = $encoded_values['hash'];
        
    }

   
    /**
     * To get Novalnet transaction information from novalnet table
     *
     * @param integer $order_no
     * @return array
     */
    public static function getNovalnetTransDetails($order_no) {
        return xtc_db_fetch_array(xtc_db_query("SELECT tid, vendor, product, tariff_id, auth_code, refund_amount, payment_id, payment_type, amount, currency, gateway_status, novalnet_order_date, test_mode, customer_id, payment_details,payment_ref,next_payment_date,order_no FROM novalnet_transaction_detail WHERE order_no='" . xtc_db_input($order_no) . "'"));
    }

    /**
     * To form server response in session and transaction comments  while processing one click shopping
     *
     * @param string $paymentname
     * @param array $params
     * @param array $payment_response
     */
    public static function getPostValues($paymentname, $params, $payment_response) {
		global $order;
		$serialize_data = '';
		if($paymentname == 'novalnet_cc' && (constant('MODULE_PAYMENT_'.strtoupper($paymentname).'_SHOP_TYPE') == 'ONECLICK' || constant('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK') == 'true' ) && !empty($params['create_payment_ref'])) {
			$serialize_data = ($paymentname == 'novalnet_cc') ? serialize(array(
							'cc_holder' 		=> $payment_response['cc_holder'],
							'cc_no' 			=> $payment_response['cc_no'],
							'cc_exp_year' 		=> $payment_response['cc_exp_year'],
							'cc_exp_month' 		=> $payment_response['cc_exp_month'],
							'tid_status' 		=> $payment_response['tid_status'],
							'cc_card_type' 		=> $payment_response['cc_card_type'],
							'tid' 				=> $payment_response['tid'] ))
							: '';
		}
		$_SESSION['novalnet'][$paymentname] = array(
						'vendor_id'				=> $params['vendor'],
						'product_id'			=> $params['product'],
						'authcode'				=> $params['auth_code'],
						'tariff_id'				=> $params['tariff'],
						'tid' 					=> $payment_response['tid'],
						'payment_id' 			=> $params['key'],
						'test_mode' 			=> $payment_response['test_mode'],
						'reference_transaction' => ( isset($_SESSION['novalnet'][$paymentname]['reference_transaction']) ) ? '1' : '0',
						'gateway_response' 		=> $payment_response,
						'gateway_response' 		=> $payment_response,
						'serialize_data' 		=> !empty($_SESSION['novalnet'][$paymentname]['serialize_data']) ? $_SESSION['novalnet'][$paymentname]['serialize_data'] : $serialize_data,
		);
		$user_comments  = !empty($order->info['comments']) ? PHP_EOL . $order->info['comments'] : '';
		$trans_comments = self::transactionCommentsForm($payment_response, $paymentname );
		$order->info['comments']           = $user_comments . $trans_comments; 
	}

	/**
	 *  Form Novalnet transactions comments
	 *
	 * @param array $response
	 * @param string $paymentname
	 * @return string 
	 */
	public static function transactionCommentsForm ($response,$paymentname) {
		$trans_comments = '';
		if(in_array($response['payment_id'],array('40','41')) && in_array($response['tid_status'], array('75','91','99','100'))){
			$trans_comments .=  MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY.PHP_EOL;
		}
		$trans_comments .= PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $response['tid'] . ((($response['test_mode'] == 1) || constant('MODULE_PAYMENT_' . strtoupper($paymentname) . '_TEST_MODE') == 'true') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE . PHP_EOL : '');
		if(in_array($response['payment_id'],array('41','96')) && in_array($response['tid_status'], array('75'))){
			$trans_comments .= PHP_EOL . MODULE_PAYMENT_NOVALNET_MENTION_GUARANTEE_PAYMENT_PENDING_TEXT.PHP_EOL;
		}
		if(in_array($response['payment_id'],array('40','97')) && in_array($response['tid_status'], array('75'))){
			$trans_comments .= PHP_EOL . MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_PENDING_TEXT.PHP_EOL;
		}
		
		return $trans_comments;
	}

	/**
     *  To show javascript error message if not enable javascript in browser
     *
     * @return string
     */
    public static function enableJavascript() {
		return '<nobr><noscript><input type="hidden" name="nn_js_enabled" value="1"><br/><div style="color:red"><b>'. MODULE_PAYMENT_NOVALNET_NO_SCRIPT . '</b></div></noscript></nobr>';
	 }

	/**
	 *  Get server response
	 *
	 * @param array $response
	 * return string
	 */
	 public static  function getServerResponse($response) {
		return (!empty($response['status_desc']) ? $response['status_desc'] : (!empty($response['status_text']) ? $response['status_text'] : (!empty($response['status_message']) ? $response['status_message'] : MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR)));
	 }
	 
     /**
      * To update barzahlen slip in checkout succes page
      * 
      * @param int $order_id
      * @param string $get_cashpayment_token
      * 
      * return array
      */
      public static function barzahlen_checkout_url($order_id, $get_cashpayment_token){
			$get_cashpayment_token = unserialize($get_cashpayment_token['payment_details']);
			$barzahlen_checkout_url = "https://cdn.barzahlen.de/js/v2/checkout-sandbox.js";
			if($get_cashpayment_token['test_mode'] == '0')
				$barzahlen_checkout_url = "https://cdn.barzahlen.de/js/v2/checkout.js";
			if($_SESSION['language'] == 'english'){
				define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SUCCESS_BUTTON', 'Pay now with Barzahlen');
			}else{
				define('MODULE_PAYMENT_NOVALNET_BARZAHLEN_SUCCESS_BUTTON', 'Bezahlen mit Barzahlen');
			}
			
			return array(
					'barzahlen_url'            =>$barzahlen_checkout_url,
					'novalnet_barzahlen_token' => $get_cashpayment_token['cp_checkout_token'],
					'novalnet_success_button'  => MODULE_PAYMENT_NOVALNET_BARZAHLEN_SUCCESS_BUTTON
			);
	  }

     /**
     * To form guarantee payment order confirmation mail 
     * 
     * @param $datas array
     */
    public static function guarantee_mail ($datas,$db_details = ''){
		if($db_details == ''){
			$order = new order($datas['order_no']);
			$customername  = $order->customer['firstname'].' '.$order->customer['lastname'];
			$customeremail = $order->customer['email_address'];
		}else {
			$customer_dbvalue = xtc_db_fetch_array(xtc_db_query("SELECT customers_firstname,customers_lastname,customers_email_address FROM " . TABLE_CUSTOMERS . " WHERE customers_id= ". xtc_db_input($db_details['customer_id']) ."  ORDER BY customers_id DESC LIMIT 1"));
			$customername  = $customer_dbvalue['customers_firstname'].$customer_dbvalue['customers_lastname'];
			$customeremail = $customer_dbvalue['customers_email_address'];
		}
		$subject = sprintf(MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_SUBJECT,$datas['order_no'],STORE_NAME);
        $get_mail_content = NovalnetHelper::get_mail_content_array_novalnet($datas['comments'],$datas);
         $html_mail= $get_mail_content['smarty']->fetch(DIR_FS_CATALOG.'lang/'.$get_mail_content['lang'].'/original_mail_templates/order_mail.html');
         $txt_mail= $get_mail_content['smarty']->fetch(DIR_FS_CATALOG.'lang/'.$get_mail_content['lang'].'/original_mail_templates/order_mail.txt');
		xtc_php_mail(EMAIL_FROM, STORE_NAME, $customeremail, STORE_OWNER, '', '', '', '', '', $subject, $html_mail, $txt_mail);
	}
	
	 /**
     * To form instalment payment order mail and cycle info mail
     * 
     * @param $data array
     */
	
	public static function instalment_mail ($data ){
		 $subject = sprintf(MODULE_PAYMENT_NOVALNET_INSTALMENT_MAIL_SUBJECT,STORE_NAME,$data['order_no']);
	     $get_mail_content = NovalnetHelper::get_mail_content_array_novalnet($data['comments'],$data);
         $html_mail= $get_mail_content['smarty']->fetch(DIR_FS_CATALOG.'lang/'.$get_mail_content['lang'].'/original_mail_templates/order_mail.html');
         $txt_mail= $get_mail_content['smarty']->fetch(DIR_FS_CATALOG.'lang/'.$get_mail_content['lang'].'/original_mail_templates/order_mail.txt');
		  xtc_php_mail(EMAIL_FROM, STORE_NAME, $data['email'], STORE_OWNER, '', '', '', '', '', $subject, $html_mail, $txt_mail);
	}
	
	/**
     * To form mail templete as like default mail
     * 
     * @param $data array
     */
	
	 public static function get_mail_content_array_novalnet($comments,$data) {
		$smarty = new Smarty;
        if(empty($data['order'])){
            $t_order = new order($data['order_no']);
	    }else{
			$t_order = $data['order'];
		}
        $order_lang = xtc_db_fetch_array(xtc_db_query("SELECT currency,language FROM ".TABLE_ORDERS." WHERE orders_id='". xtc_db_input($data['order_no']) ."'"));
         $mail_lang_set =  xtc_db_fetch_array(xtc_db_query("SELECT code FROM ".TABLE_LANGUAGES." WHERE directory='". xtc_db_input($order_lang['language']) ."'"));
        
		$t_order_id = $data['order_no'];
		$t_language = $order_lang['language'];
	    $t_language_id = $mail_lang_set['code'];
        // SET CONTENT DATA
        $smarty->assign('csID', $t_order->customer['csID']);
        $smarty->assign('customer_vat', $t_order->customer['vat_id']);
        $smarty->assign('order_data', NovalnetHelper::getOrderData($t_order_id,$order_lang['currency']));
        $t_order_total = NovalnetHelper::getTotalData($t_order_id);
        $smarty->assign('order_total', $t_order_total['data']);
        $smarty->assign('language', $t_language);
        $smarty->assign('language_id', $t_language_id);
        $smarty->assign('tpl_path', DIR_FS_CATALOG . StaticGXCoreLoader::getThemeControl()->getThemeHtmlPath());
        $smarty->assign('logo_path',
                                HTTP_SERVER . DIR_WS_CATALOG . StaticGXCoreLoader::getThemeControl()
                                    ->getThemeImagePath());
        $smarty->assign('oID', $t_order_id);
        $t_payment_method = '';
        if ($t_order->info['payment_method'] != '' && $t_order->info['payment_method'] != 'no_payment') {
            $t_payment_method = PaymentTitleProvider::getStrippedTagsTitle($t_order->info['payment_method']);
            $smarty->assign('PAYMENT_MODUL', $t_order->info['payment_method']);
        }
        $smarty->assign('PAYMENT_METHOD', $t_payment_method);
        $smarty->assign('NAME', $t_order->customer['name']);
        $smarty->assign('GENDER', $t_order->customer['gender']);
        $smarty->assign('COMMENTS', nl2br($comments));
        $smarty->assign('EMAIL', $t_order->customer['email_address']);
        $smarty->assign('PHONE', $t_order->customer['telephone']);
        if (defined('EMAIL_SIGNATURE')) {
            $smarty->assign('EMAIL_SIGNATURE_HTML', nl2br(EMAIL_SIGNATURE));
            $smarty->assign('EMAIL_SIGNATURE_TEXT', EMAIL_SIGNATURE);
        }
        // PREPARE HTML MAIL
        $smarty->assign('address_label_customer', xtc_address_format($t_order->customer['format_id'],$t_order->customer, 1, '', '<br />'));
        $smarty->assign('address_label_shipping', xtc_address_format($t_order->delivery['format_id'], $t_order->delivery, 1,'','<br />'));
        $smarty->assign('address_label_payment', xtc_address_format($t_order->billing['format_id'], $t_order->billing , 1, '', '<br />'));
        return array( 'lang' =>  $t_language ,'smarty' => $smarty);
    }
	
	public static function getOrderData($oID,$currency){
			require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');
			
			$order_query = "SELECT
									  op.products_id,
									  op.orders_products_id,
									  op.products_model,
									  op.products_name,
									  op.checkout_information,
									  op.final_price,
									  op.products_shipping_time,
									  op.products_quantity,
									  opqu.quantity_unit_id,
									  opqu.unit_name
								  FROM " . TABLE_ORDERS_PRODUCTS . " op
								  LEFT JOIN orders_products_quantity_units opqu USING (orders_products_id)
								  WHERE op.orders_id = '" . (int)$oID . "'";
			$order_data = array();
			$order_query = xtc_db_query($order_query);
			while($order_data_values = xtc_db_fetch_array($order_query))
			{
				$attributes_query = "SELECT
									  products_options,
									  products_options_values,
									  price_prefix,
									  options_values_price
									  FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
									  WHERE orders_products_id='" . $order_data_values['orders_products_id'] . "'
									  AND orders_id='" . (int)$oID . "'";
				$attributes_data = '';
				$attributes_model = '';
				$attributes_query = xtc_db_query($attributes_query);
				
				while($attributes_data_values = xtc_db_fetch_array($attributes_query))
				{
					$attributes_data .= '<br />' . $attributes_data_values['products_options'] . ':' . $attributes_data_values['products_options_values'];
					$attributes_model .= '<br />' . xtc_get_attributes_model($order_data_values['products_id'], $attributes_data_values['products_options_values'], $attributes_data_values['products_options']);
				}
				
				// properties
				$coo_properties_control = MainFactory::create_object('PropertiesControl');
				$t_properties_array = $coo_properties_control->get_orders_products_properties($order_data_values['orders_products_id']);
				
				if(ACTIVATE_SHIPPING_STATUS == 'true')
				{
					$shipping_time = $order_data_values['products_shipping_time'];
				}
				else
				{
					$shipping_time = '';
				}
				
				// BOF GM_MOD GX-Customizer
				require(DIR_FS_CATALOG . 'gm/modules/gm_gprint_order.php');

				$order_data[] = array('PRODUCTS_MODEL' => $order_data_values['products_model'],
										'PRODUCTS_NAME' => $order_data_values['products_name'],
										'CHECKOUT_INFORMATION' => $order_data_values['checkout_information'],
										'CHECKOUT_INFORMATION_TEXT' => html_entity_decode_wrapper(strip_tags($order_data_values['checkout_information'])),
										'PRODUCTS_SHIPPING_TIME' => $shipping_time,
										'PRODUCTS_ATTRIBUTES' => $attributes_data,
										'PRODUCTS_ATTRIBUTES_MODEL' => $attributes_model,
										'PRODUCTS_PROPERTIES' => $t_properties_array,
										'PRODUCTS_PRICE' => 	xtc_format_price_order($order_data_values['final_price'], 1, $currency),
										'PRODUCTS_SINGLE_PRICE' => xtc_format_price_order($order_data_values['final_price'] / $order_data_values['products_quantity'], 1, $currency),
										'PRODUCTS_QTY' => gm_prepare_number($order_data_values['products_quantity'], ','),
										'UNIT' => $order_data_values['unit_name']);
			}

			return $order_data;
		}
	
	public static function getTotalData($oID){
			// get order_total data
			$oder_total_query = "SELECT
										title,
										text,
										class,
										value,
										sort_order
									FROM " . TABLE_ORDERS_TOTAL . "
									WHERE orders_id='" . (int)$oID . "'
									ORDER BY sort_order ASC";

			$order_total = array();
			$oder_total_query = xtc_db_query($oder_total_query);
			while($oder_total_values = xtc_db_fetch_array($oder_total_query))
			{
				$order_total[] = array('TITLE' => $oder_total_values['title'], 
										'CLASS' => $oder_total_values['class'], 
										'VALUE' => $oder_total_values['value'], 
										'TEXT' => $oder_total_values['text']);
				
				if($oder_total_values['class'] == 'ot_total')
				{
					$total = $oder_total_values['value'];
				}
			}

			return array('data' => $order_total, 'total' => $total);
		}
	
	public static function novalnet_get_instalment_cycles($amount,$total_period) {
		$i             = 0;
	    foreach ( $total_period as $period ) {
			$cycle_amount = number_format($amount / $period, 2);
			if ( $cycle_amount >= 9.99 ) {
				$cycles .= '<option value='.$period.'>'.sprintf(( '%s Cycles'), $period ) . ' / €' . $cycle_amount . ' per month'.'</option>';
				$i++;
			}
	    }
		if ( $i == 0 ) {
			return $i;
		} else {
			return $cycles;
		}
    }
    
     public static function instalment_date($novalnet_instalment_cycle,$novalnet_recurring_period_cycle){
		$total_invoice_instalment_cycle = !empty( $novalnet_instalment_cycle ) ? $novalnet_instalment_cycle[count($novalnet_instalment_cycle)-1]:'';
		$current_month_invoice = date('m');
		for ( $i=0; $i<$total_invoice_instalment_cycle; $i++ ) {
		  $last_day = date('Y-m-d', strtotime( '+'.$novalnet_recurring_period_cycle * $i.'months' ) );
		  $instlment_date_month[] = date('m', strtotime( '+'.$novalnet_recurring_period_cycle * $i.'months' ) );
		  if( $current_month_invoice > 12 ) {
			$current_month_invoice = $current_month_invoice - 12;
		  }
		  if ( $current_month_invoice == $instlment_date_month[$i] ) {
			  $instlment_date_invoice[] = date('Y-m-d', strtotime( '+'.$novalnet_recurring_period_cycle * $i.'months' ) );
		  } else {
			  $instlment_date_invoice[] = date('Y-m-d', strtotime( $instlment_date_invoice[$i].' last day of previous month' , strtotime ( $last_day ) ) );
		   }
			 $current_month_invoice = $current_month_invoice + $novalnet_recurring_period_cycle;
		}
		$instlment_date_invoice = implode( '/', $instlment_date_invoice );
		
		return $instlment_date_invoice;
	}
	
	/**
     * To Proceed and instalment validation
     *
     * @param integer $orderamount
     * return string
     */
	function novalnet_instalment_validation($orderamount) {
		global $order;
		// Delivery address
		$delivery_address = array(
			'street_address' => $order->delivery['street_address'],
			'city'           => $order->delivery['city'],
			'postcode'       => $order->delivery['postcode'],
			'country'        => $order->delivery['country']['iso_code_2'],
		);
		// Billing address
		$billing_address = array(
			'street_address' => $order->billing['street_address'],
			'city'           => $order->billing['city'],
			'postcode'       => $order->billing['postcode'],
			'country'        => $order->billing['country']['iso_code_2'],
		);
		$minimum_amount_gurantee    =  '1998';
		// Check instalment payment validation
			if ($orderamount >= $minimum_amount_gurantee  && in_array(strtoupper($order->billing['country']['iso_code_2']), array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' )) && $order->info['currency'] == 'EUR' && $delivery_address === $billing_address) {
			 return true;
			} else{
				 return false;
			}
			
	}
	
	/**
	 * Get shipping details from order object for paypal payment to process.
	 *
	 * 
	 * @param object $order order object.
	 *
	 * @return $shipping_address
	 */
	public static function get_novalnet_shipping_detail( $order ) {
		$shipping_address= array(
						's_first_name'     => $order->delivery['firstname'],
						's_last_name'      => $order->delivery['lastname'],
						's_street'         => $order->delivery['street_address'],
						's_house_no'       => $order->delivery['house_number'],
						's_city'           => $order->delivery['city'],
						's_zip'            => $order->delivery['postcode'],
						's_country_code'   => $order->delivery['country']['iso_code_2'],
						's_company'        => $order->delivery['company'],
					);
		$shipping = array(
			'street_address' => $order->delivery['street_address'],
			'city'           => $order->delivery['city'],
			'postcode'       => $order->delivery['postcode'],
			'country'        => $order->delivery['country']['iso_code_2'],
		);
		// Billing address
		$billing_address = array(
			'street_address' => $order->billing['street_address'],
			'city'           => $order->billing['city'],
			'postcode'       => $order->billing['postcode'],
			'country'        => $order->billing['country']['iso_code_2'],
		);
		if($billing_address == $shipping){
		   $shipping_address['ship_add_sab']=1;
		}
		return $shipping_address;
	}
}

