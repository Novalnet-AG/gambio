<?php
/**
 * Novalnet payment module
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * @category   PHP
 * @package    NovalnetGateway
 * @author     Novalnet AG
 * @copyright  Copyright by Novalnet
 * @license    https://www.novalnet.de/payment-plugins/kostenlos/lizenz
 *
 * Script : novalnet_invoice.php
 *
 */
require_once(DIR_FS_CATALOG . 'ext/novalnet/NovalnetHelper.class.php');
class novalnet_invoice {
    var $code, $title, $public_title, $sort_order, $enabled, $test_mode;

    /**
     * Core Function : Constructor()
     *
     */
    public function __construct() {
        global $order;
        $this->code         = 'novalnet_invoice';
        $this->title        = defined('MODULE_PAYMENT_NOVALNET_INVOICE_TEXT_TITLE') ? 'Novalnet '. MODULE_PAYMENT_NOVALNET_INVOICE_TEXT_TITLE : '';
        $this->public_title = defined('MODULE_PAYMENT_NOVALNET_INVOICE_PUBLIC_TITLE') ? MODULE_PAYMENT_NOVALNET_INVOICE_PUBLIC_TITLE : '';
        $this->sort_order   =  0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
			$this->sort_order   = defined('MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER') && trim(MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER) != '' ? trim(MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER) : 0;
            $this->enabled      = ((defined('MODULE_PAYMENT_NOVALNET_INVOICE_STATUS') && MODULE_PAYMENT_NOVALNET_INVOICE_STATUS == 'true') ? true : false);
            $result = xtc_db_query("SHOW TABLES LIKE 'gx_configurations'");
            $gx_config = $result->num_rows; 
            $_SESSION['GX'] = $gx_config; 
             //fraud module
            if($_SESSION['GX'] == '1' ){
             $this->fraud_module = MODULE_PAYMENT_NOVALNET_INVOICE_FRAUDMODULE_CALLBACK == 'true' ?  'CALLBACK' : (MODULE_PAYMENT_NOVALNET_INVOICE_FRAUDMODULE_SMS == 'true' ? 'SMS' : 'false');
		    }else{
            $this->fraud_module = ((defined('MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE')) ? MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE : '');
            }
            $this->test_mode    = ((defined('MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE') && MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE == 'true') ? true : false); // To check the test mode
            NovalnetHelper::getLastSuccessPayment($this->code); // By default last transaction payment select in checkout page
        
        }
        
        if (is_object($order))
            $this->update_status();
	}

    /**
     * Core Function : update_status()
     *
     * check if zone is allowed to see module
     */
    function update_status() {
        global $order;
        if ($_SESSION['shipping']['id'] == 'selfpickup_selfpickup') {
            $this->enabled = false;
        }
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE > 0)) {
            $check_flag  = false;
            $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = xtc_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }
            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    /**
     * Core Function : selection()
     *
     * Display checkout form in chekout payment page
     * @return array
     */
    function selection() {
		    global $order;
			// Get order amount
		    if(!empty($order)) {
			$order_amount = NovalnetHelper::getPaymentAmount();
			// Payment hide in checkout page when condition was true
			if (NovalnetHelper::merchantValidate($this->code) || strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') === false || !NovalnetHelper::hidePaymentVisibility($this->code, $order_amount)) {
				if (!empty($_SESSION['payment']) && $_SESSION['payment'] == $this->code) {
					unset($_SESSION['payment']);
				}
				return false;
			}
			// Unset the novalnet session variable
			if (isset($_SESSION['novalnet'][$this->code]['tid']) && !empty($_SESSION['payment']) && $_SESSION['payment'] != $this->code) {
				unset($_SESSION['novalnet'][$this->code]['tid']);
			}
			// Payment hide in checkout page when condition was true if fraud module enable
			if (!empty($_SESSION[$this->code . '_payment_lock_nn']) && isset($_SESSION[$this->code . '_callback_max_time_nn']) && $_SESSION[$this->code . '_callback_max_time_nn'] > time()) {
				return false;
			}
			// Unset the fraud module session variable
			if (isset($_SESSION['novalnet'][$this->code]['tid']) && !empty($_SESSION[$this->code . '_payment_lock_nn']) && isset($_SESSION[$this->code . '_callback_max_time_nn']) && $_SESSION[$this->code . '_callback_max_time_nn'] < time()) {
				unset($_SESSION[$this->code . '_callback_max_time_nn'], $_SESSION[$this->code . '_payment_lock_nn'], $_SESSION['novalnet'][$this->code]['tid']);
			}
			// Get order amount set for fraud module validation
			if(empty($_SESSION['novalnet']['novalnet_invoice']['nn_set_order_amount']) || (empty($_REQUEST['payment']) && !empty($_SESSION['novalnet']['novalnet_invoice']['nn_set_order_amount']) && $order_amount != $_SESSION['novalnet']['novalnet_invoice']['nn_order_amount'])) {
				$_SESSION['novalnet']['novalnet_invoice']['nn_set_order_amount'] = true;
				$_SESSION['novalnet']['novalnet_invoice']['nn_order_amount'] = $order_amount;
			}
			// To check the test mode in shop
			$test_mode                 = $this->test_mode == 1 ? MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG : '';
			// Merchant can show the information for end customer in checkout page
			$endcustomerinfo           = trim(strip_tags(MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO)) != '' ? trim(strip_tags(MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO)) : '';
			if(!empty($order)){
			    $gurantee_error = $this->proceedToInvoiceGuranteePayment($order_amount);
			}
			global $gx_version;
			require DIR_FS_CATALOG . 'release_info.php';
			$shop_version = str_replace('.','',$gx_version);
			$shop_version = str_replace('v','',$shop_version);
			if($shop_version > '3810') {
				$payment_title= str_replace('Novalnet','',$this->title);
				$title = $payment_title;
			} else {
				$title = $this->public_title;
			}
			$selection  = array(
					'id' => $this->code,
					'module' => $title,
					'description' => MODULE_PAYMENT_NOVALNET_INV_PRE_DESC . '<br>' . $test_mode .'<br><span id ="notification_buyer_wrap">' . $endcustomerinfo.'</span><br><span style="color:red">'.$gurantee_error.'</span><br><input type="hidden" id="nn_root_invoice_catalog" value="' . DIR_WS_CATALOG . '"/><script type= text/javascript src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet_invoice.js"></script><noscript><input type="hidden" name="js_enabled" value=1 ><br /></noscript>');
			if($shop_version > '3810') {
				if((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true'){
					$selection['logo_url'] = xtc_href_link('images/icons/payment/novalnet_invoice.png', '', 'SSL', false, false, false, true, true);
				}else {
					$selection['logo_url'] = '';
					$selection['logo_alt'] = '';
				}
			}
			//To check the basic fraud module requirements
			$this->fraud_module_status = NovalnetHelper::setFraudModuleStatus($this->code, $this->fraud_module,$_SESSION['novalnet']['novalnet_invoice']['nn_order_amount']);
			if ($_SESSION['novalnet'][$this->code]['tid'] == '' && in_array($this->fraud_module,array('CALLBACK', 'SMS')) && $this->fraud_module_status && empty($_SESSION['novalnet'][$this->code]['guarantee']) && ($order->info['total']*100 >= MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT)) {
				if ($this->fraud_module == 'CALLBACK') {
					$selection['fields'][] = array(
							'title' => MODULE_PAYMENT_NOVALNET_FRAUDMODULE_CALLBACK_INPUT_TITLE,
							'field' => '<div>'. xtc_draw_input_field($this->code . '_fraud_tel', $order->customer['telephone'], 'id="' . $this->code . '_' .strtolower($this->fraud_module).'" AUTOCOMPLETE=off') . '</div>'
						);
				} else {
					$selection['fields'][] = array(
							'title' => MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_INPUT_TITLE,
							'field' => '<div>'. xtc_draw_input_field($this->code . '_fraud_mobile', '', 'id="' . $this->code . '_' .strtolower($this->fraud_module).'pin'.'" AUTOCOMPLETE=off').'</div>'
					   );
				}
			}
			// Fraud module pin box showing in checkout page if fraud module enable
			if (isset($_SESSION['novalnet'][$this->code]['tid']) && in_array($this->fraud_module,array('CALLBACK', 'SMS')) && $this->fraud_module_status  && empty($_SESSION['novalnet'][$this->code]['guarantee'])) {
							 $selection['fields'][] = array(
					'title' => MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC,
					'field' => '<div>'.xtc_draw_input_field($this->code . '_fraud_pin', '', 'id="' . $this->code . '_' . strtolower($this->fraud_module) . 'pin"AUTOCOMPLETE=off onkeypress="return pin_val(event)"').'</div><input type="hidden" id="nn_root_invoice_catalog" value="' . DIR_WS_CATALOG . '"/>'
				);
				$selection['fields'][] = array(
					'title' => '',
					'field' => xtc_draw_checkbox_field($this->code . '_new_pin', '1', false, 'id="' . $this->code . '_' . strtolower($this->fraud_module) . 'new_pin"') . MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN,
				);

			}
			// Get the customer id for to get end customer dob in database
			$customer_id                       = (isset($_SESSION['customer_id'])) ? 'customers_id= "' . xtc_db_input($_SESSION['customer_id']) . '"' : 'customers_email_address= "' . xtc_db_input($order->customer['email_address']) . '"';
			$customer_dbvalue                  = xtc_db_fetch_array(xtc_db_query("SELECT customers_dob FROM " . TABLE_CUSTOMERS . " WHERE " . $customer_id . " ORDER BY customers_id DESC"));
			$customer_dbvalue['customers_dob'] = !in_array($customer_dbvalue['customers_dob'] ,array('0000-00-00 00:00:00','1000-01-01 00:00:00')) ? date('Y-m-d', strtotime($customer_dbvalue['customers_dob'])) :'';
			// To show the gurantee payment field
			if (!empty($_SESSION['novalnet'][$this->code]['guarantee']) && (($order->billing['company'] == '') || ($order->billing['company'] != '' && MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B == 'false'))) {
				$selection['fields'][] = array(
							'title' => MODULE_PAYMENT_GUARANTEE_FIELD,
							'field' => '<div>'.xtc_draw_input_field($this->code.'_dob',$customer_dbvalue['customers_dob'], 'id="'.$this->code.'_dob" AUTOCOMPLETE=off class=form-control form-type-date hasDatepicker').xtc_draw_hidden_field('', $_SESSION['language_code'], 'id="nn_shop_lang"').'</div>
							<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
							<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
							<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
							<script src="https://cdn.novalnet.de/js/v2/NovalnetUtility.js"></script>
							<script type= text/javascript src="' . DIR_WS_CATALOG . 'ext/novalnet/js/datepicker-'.$_SESSION['language_code'].'.js"></script>
							<script type="text/javascript">
								var j = jQuery.noConflict();
								jQuery(document).ready(function() {
									j("#novalnet_invoice_dob").keydown(function(){
									  return NovalnetUtility.isNumericBirthdate( this, event )
									});
								});
						</script><br/>'
				);
			}
			return $selection;
		    }
    }

    /**
     * Core Function : pre_confirmation_check()
     *
     * Perform validations for post values
     */
    function pre_confirmation_check() {
		global $order;
		$post = $_REQUEST;
        $_SESSION['novalnet'][$this->code . '_newpin'] = $post[$this->code . '_new_pin'];
        //To check the basic fraud module requirements
        $this->fraud_module_status = NovalnetHelper::setFraudModuleStatus($this->code, $this->fraud_module,$_SESSION['novalnet']['novalnet_invoice']['nn_order_amount']);        
        // if javascript not enable in system then show the error message
        if (!empty($post['js_enabled'])) {
			$payment_error_return = 'payment_error=' . $this->code . '&error_message=' . MODULE_PAYMENT_NOVALNET_NO_SCRIPT;
             xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
		}
		//To validate the fraud module pin field
        if ($this->fraud_module_status && !empty($_SESSION['novalnet'][$this->code]['tid']) && $_SESSION['novalnet'][$this->code . '_newpin'] != 1 && empty($_SESSION['novalnet'][$this->code]['guarantee'])) {
            NovalnetHelper::validateUserInputsOnCallback($this->code, $post, $this->fraud_module);
        } else if (empty($_SESSION['novalnet'][$this->code]['tid'])) { // To performing the first call process
			// To validate the fraud module tel/sms fields
            if ($this->fraud_module_status && empty($_SESSION['novalnet'][$this->code]['guarantee']) && ($order->info['total']*100 >= MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT)) {
                $error_message = NovalnetHelper::validateCallbackFields($post, $this->fraud_module, $this->code);
                if ($error_message != '') {
					$payment_error_return = 'payment_error=' . $this->code . '&error_message=' . trim($error_message);
                    xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
                }
            }
           //To validate the gurantee payment field
         $error_message= '';
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
		$minimum_amount_gurantee    = trim(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT) != '' ? trim(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT) : '999';
		$order_amount = NovalnetHelper::getPaymentAmount();
            //To validate the gurantee payment field
            if (!empty($_SESSION['novalnet'][$this->code]['guarantee']) && !empty($_SESSION['novalnet'][$this->code]['guarantee_error'])) {
				if ($delivery_address !== $billing_address) {
					$error_message = MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_ADDRESS;
				   }else if(!in_array(strtoupper($order->billing['country']['iso_code_2']), array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' ))){
					$error_message = MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_COUNTRY;
				  }else if ($order->info['currency'] != 'EUR'){
				    $error_message = MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_CURRENCY;
				  }else if($order_amount <= $minimum_amount_gurantee ){
					$amount = MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT !='' ? number_format((MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT /100), 2, ',', ' ') : '9,99';
					$error_message =  MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_AMOUNT.$amount.' EUR )';
				  }
			} elseif (!empty($_SESSION['novalnet'][$this->code]['guarantee']) && (($order->billing['company'] == '') || ($order->billing['company'] != '' && MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B == 'false'))) {
				$current_age = date('Y-m-d',strtotime($post['novalnet_invoice_dob']));
				if ($post['novalnet_invoice_dob'] == '') {
					$error_message = MODULE_PAYMENT_GUARANTEE_PAYMENT_DOB_EMPTY_ERROR_MESSAGE;
				} else if ($post['novalnet_invoice_dob'] != '') {
					if(time() < strtotime('+18 years', strtotime($current_age)) && MODULE_PAYMENT_NOVALNET_INVOICE_FORCE == 'false') {
						$error_message = MODULE_PAYMENT_GUARANTEE_PAYMENT_AGE_ERROR_MESSAGE;
					} 
				}
			}
			if($error_message != '' && MODULE_PAYMENT_NOVALNET_INVOICE_FORCE == 'false'){
				unset($_SESSION['payment'],$_SESSION['novalnet'][$this->code]['guarantee']);				
				xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error_message=' . $error_message, 'SSL', true, false));
			}
        }
    }

    /**
     * Core Function : confirmation()
     *
     * Displays confirmation page
     * @param none
     * @return boolean
     */
    function confirmation() {
		global $order;
		// Assign order amount in session
        $_SESSION['novalnet'][$this->code]['payment_amount'] = NovalnetHelper::getPaymentAmount(); 
        // To check the basic fraud module requirements
        $this->fraud_module_status = NovalnetHelper::setFraudModuleStatus($this->code, $this->fraud_module,$_SESSION['novalnet']['novalnet_invoice']['nn_order_amount']);
        if ($this->fraud_module_status && !empty($_SESSION['novalnet'][$this->code]['tid']) && $_SESSION['novalnet'][$this->code . '_newpin'] != 1 && empty($_SESSION['novalnet'][$this->code]['guarantee'])) {
			NovalnetHelper::validateAmountOnCallback($this->code, $this->fraud_module);
        }
        
    }

    /**
     * Core Function : process_button()
     *
     * Payments redirects from shop to payment site
     */
    function process_button() {
        $post = $_REQUEST;
        // To generate the new pin for fraud module

        if (isset($post[$this->code . '_new_pin']) && $post[$this->code . '_new_pin'] == 1 && empty($_SESSION['novalnet'][$this->code]['guarantee']) ) { // Generate the new pin for fraud module
            $new_pin_response     = NovalnetHelper::doCallbackRequest('TRANSMIT_PIN_AGAIN', $this->code);
            $response             = simplexml_load_string($new_pin_response);
            $response             = json_decode(json_encode($response), true);
            $response_text        = isset($response['status_message']) ? $response['status_message'] : (isset($response['pin_status']['status_message']) ? $response['pin_status']['status_message'] : $response['status_text']);
            $payment_error_return = 'payment_error=' . $this->code . '&error_message=' . $response_text;
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        } elseif (isset($_SESSION['novalnet'][$this->code]['payment_amount'])) { //Store the values in session
            $novalnet_order_details = !empty($_SESSION['novalnet'][$this->code]) ? $_SESSION['novalnet'][$this->code] : array();
            $_SESSION['novalnet'][$this->code] = array_merge($novalnet_order_details, $post, array(
                'payment_amount' => $_SESSION['novalnet'][$this->code]['payment_amount']
            ));
        } else { // Payment redirect to checkout page if amount invaild
            $payment_error_return = 'payment_error=' . $this->code . '&error_message=' . MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE;
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }
    }

    /**
     * Core Function : before_process()
     *
     * Send params to Novalnet server (Note : if the payment uses curl request)
     */
    function before_process() {
        global $order;
        $order_amount              = NovalnetHelper::getPaymentAmount();
        $this->fraud_module_status = NovalnetHelper::setFraudModuleStatus($this->code, $this->fraud_module,$_SESSION['novalnet']['novalnet_invoice']['nn_order_amount']);
        $param_inputs              = array_merge((array) $order, $_SESSION['novalnet'][$this->code], array(
            'fraud_module'        => $this->fraud_module,
            'fraud_module_status' => $this->fraud_module_status
        ));
        // Fraud module call request send to the novalnet server
        if (!empty($_SESSION['novalnet'][$this->code]['tid']) && $_SESSION['novalnet'][$this->code . '_newpin'] != 1 && $this->fraud_module != 'false' && empty($_SESSION['novalnet'][$this->code]['guarantee'])) {
			
            $callback_response = NovalnetHelper::doCallbackRequest('PIN_STATUS', $this->code);
            $callback_response = simplexml_load_string($callback_response);
            $callback_response = json_decode(json_encode($callback_response), true);
            $_SESSION['novalnet'][$this->code]['gateway_response']['tid_status'] = $callback_response['tid_status'];
            $callback_response_text = isset($callback_response['status_message']) ? $callback_response['status_message'] : (isset($callback_response['pin_status']['status_message']) ? $callback_response['pin_status']['status_message'] : $callback_response['status_text']);
            if ($callback_response['status'] != 100) {
                if ($callback_response['status'] == '0529006') {
                    $_SESSION[$this->code . '_payment_lock_nn']      = true;
                    $_SESSION[$this->code . '_callback_max_time_nn'] = time() + (30 * 60);
                }
                $payment_error_return = 'payment_error=' . $this->code . '&error_message=' . $callback_response_text;
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
            }
            $urlparam                 = NovalnetHelper::getCommonParms($param_inputs);
            $form_comments = NovalnetHelper::checkPaymentStatus($this->code, $_SESSION['novalnet'][$this->code]['gateway_response'],$urlparam);
        }
         else { // Normal payment request send to the novalnet server
			
            $urlparam                 = NovalnetHelper::getCommonParms($param_inputs);
            $urlparam['invoice_type'] = 'INVOICE';
            $urlparam['payment_type'] = 'INVOICE_START';
            // To send the invoice due date to novalnet server
            if (!empty(trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE)) && preg_match('/^\d+$/',trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE))) {
                $urlparam['due_date'] = date("Y-m-d", strtotime(trim(MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE) . ' days'));
            }
            // To process on hold product
            if ((trim(MODULE_PAYMENT_NOVALNET_INVOICE_MANUAL_CHECK_LIMIT) > 0 && $order_amount >= trim(MODULE_PAYMENT_NOVALNET_INVOICE_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE == 'true')) || (empty (MODULE_PAYMENT_NOVALNET_INVOICE_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE == 'true'))){ // If condition true then order proceed on-hold transaction
                $urlparam['on_hold'] = 1;
            }
            //Fraud module param send to the novalnet server
            $fraud_module_active = false;
            if ($this->fraud_module_status && empty($_SESSION['novalnet'][$this->code]['guarantee']) && ($order->info['total']*100 >= MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT)) {
				$fraud_module_active = true; // Allow fraud module param if enabled
                if ($this->fraud_module == 'CALLBACK') { // if fraud module callback enabled
                    $urlparam['tel']             = trim($_SESSION['novalnet'][$this->code]['novalnet_invoice_fraud_tel']);
                    $urlparam['pin_by_callback'] = '1';
                } else { // if fraud module sms enabled
                    $urlparam['mobile']     = trim($_SESSION['novalnet'][$this->code]['novalnet_invoice_fraud_mobile']);
                    $urlparam['pin_by_sms'] = '1';
                }
            }
            //The gurantee payment param send to the novalnet server
			if ((!empty($_SESSION['novalnet'][$this->code]['guarantee']) && !empty($_SESSION['novalnet'][$this->code]['novalnet_invoice_dob']) && (time() > strtotime('+18 years', strtotime(date('Y-m-d',strtotime($_SESSION['novalnet'][$this->code]['novalnet_invoice_dob'])))))) || (!empty($_SESSION['novalnet'][$this->code]['guarantee']) && $order->billing['company'] != '')) { // To process guarantee payments 
				$urlparam['key']          = 41;
				$urlparam['payment_type'] = 'GUARANTEED_INVOICE';
			if(MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B == 'true' && !empty($_SESSION['novalnet'][$this->code]['guarantee']) && $order->billing['company'] !=''){
	        }else{
			   $urlparam['birth_date']   = (date('Y-m-d', strtotime($_SESSION['novalnet'][$this->code]['novalnet_invoice_dob'])));
			   unset($urlparam['company']);
		    }
			}
            // Payment call request send to the novalnet server
            $response = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparam);
            parse_str($response, $data);
            // Form the novalnet transaction comments and transaction details are stored in session variable
            $form_comments = NovalnetHelper::checkPaymentStatus($this->code, $data,$urlparam);
            // To process the fraud module on first call
            if ($fraud_module_active) {
            NovalnetHelper::gotoPaymentOnCallback($this->fraud_module, $this->fraud_module_status, $this->code);
		}
        }
        // Novalnet transaction details update the orders 
        $order->info['comments'] = !empty($_SESSION['novalnet'][$this->code]['gateway_response']['novalnet_comments']) ? $_SESSION['novalnet'][$this->code]['gateway_response']['novalnet_comments'] : $form_comments['novalnet_comments'];
    }

    /**
     * Core Function : after_process()
     *
     * Send post back params(acknowledgement) to Novalnet server
     */
    function after_process() {
        global $insert_id;
        // Update the order status in shop
        $payment_id = $_SESSION['novalnet'][$this->code]['gateway_response']['payment_id'];
        NovalnetHelper::updateOrderStatus($insert_id, $this->code,$payment_id);
        // Perform paygate second call for transaction confirmations / order_no update
        NovalnetHelper::doSecondCallProcess(array(
            'payment_type' => $this->code,
            'order_no' => $insert_id
        ));
    }

    /**
     * Core Function : get_error()
     *
     * Show message in front-end
     * return array
     */
    function get_error() {
		// Get error message from shop
		$error_message = (isset($_GET['payment_message']) ? $_GET['payment_message'] : $_GET['error_message']); 
		// Show the Error message in front end
		return array(
			'title' => $this->code,
			'error' => $error_message
		);
    }

    /**
     * Core Function : javascript_validation()
     *
     * Javascript validation takes place
     * @return boolean
     */
    function javascript_validation() {
        return false;
    }

    /**
     * Core Function : check()
     *
     * Checks for payment installation status
     * @return boolean
     */
    function check() {
		$result = xtc_db_query("SHOW TABLES LIKE 'gx_configurations'");
        $gx_config = $result->num_rows; 
        $_SESSION['GX'] = $gx_config;
        if($_SESSION['GX'] == '1'){
			  $check_query  = xtc_db_query("select `value` from `gx_configurations` where `key` = 'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALLOWED'");
              $this->_check = xtc_db_num_rows($check_query);
		    }else{
               if (!isset($this->_check)) {
				$check_query  = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_INVOICE_ALLOWED'");
                $this->_check = xtc_db_num_rows($check_query);
            }
	    }
        return $this->_check;
    }

    /**
     * Core Function : install()
     *
     * Payment module installation
     */
    function install() {
		if($_SESSION['GX'] == '1'){
		 xtc_db_query("insert into `gx_configurations` ( `key`, `value`,  `legacy_group_id`, `sort_order`, `type`, `last_modified`) values 
		('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALLOWED', '', '6', '1', '', now()),
		('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS', 'NN_INV', '6', '0', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_STATUS','false', '6', '2', 'switcher',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE','false', '6', '3', 'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE','false', '6', '4' ,'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_MANUAL_CHECK_LIMIT', '', '6', '7', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_FRAUDMODULE_CALLBACK','false', '6', '4',  'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_FRAUDMODULE_SMS','false', '6', '4',  'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT', '', '6', '5','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE', '', '6', '6','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B','true', '6', '4' ,'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT', '', '6', '7','',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO', '', '6', '8','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER', '0', '6', '9', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS', '0',  '6', '10', 'order-status', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS', '0',  '6', '11', 'order-status', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE', '0', '6', '12', 'geo-zone',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE','false', '6', '18', 'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT', '', '6', '18','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS', '0',  '6', '19', 'order-status', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_FORCE','true', '6', '20', 'switcher', now())
		 ");
	 }else{
		xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_key, configuration_value, configuration_group_id, sort_order,set_function, use_function, date_added)
      VALUES ('MODULE_PAYMENT_NOVALNET_INVOICE_ALLOWED', '', '6', '1', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS', 'NN_INV', '6', '0', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_STATUS','false', '6', '2', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_STATUS\'," .'MODULE_PAYMENT_NOVALNET_INVOICE_STATUS'. ",' , '',now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE','false', '6', '3', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE\'," .'MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE'. ",' , '', now()),
         ('MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE','capture', '6', '4', 'xtc_mod_select_option(array(\'capture\' => MODULE_PAYMENT_NOVALNET_CAPTURE,\'authorize\' => MODULE_PAYMENT_NOVALNET_AUTHORIZE),\'MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE\'," .'MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_MANUAL_CHECK_LIMIT', '', '6', '7', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE','false', '6', '4', 'xtc_mod_select_option(array(\'false\' => MODULE_PAYMENT_NOVALNET_OPTION_NONE,\'CALLBACK\' => MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK,\'SMS\' => MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS,),\'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE\'," .'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT', '', '6', '5','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE', '', '6', '6','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B', 'true', '6', '7', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B\'," .'MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT', '', '6', '7','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO', '', '6', '8','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER', '0', '6', '9', '',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS', '0',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS', '0',  '6', '11', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE', '0', '6', '12', 'xtc_cfg_pull_down_zone_classes(', 'xtc_get_zone_class_title',now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE','false', '6', '18', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE\'," .'MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT', '', '6', '18','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS', '0',  '6', '19', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
        ('MODULE_PAYMENT_NOVALNET_INVOICE_FORCE','true', '6', '20', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INVOICE_FORCE\'," .'MODULE_PAYMENT_NOVALNET_INVOICE_FORCE'. ",' , '', now())");
    }
}
    /**
     * Core Function : remove()
     *
     * Payment module uninstallation
     * @param none
     * @return boolean
     */
    function remove() {
		if($_SESSION['GX'] == '1' ){
			xtc_db_query("delete from `gx_configurations` where `key` in ('" . implode("', '", array_merge($this->keys(),array('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS'))) . "')");
		}else{
		   xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", array_merge($this->keys(),array('MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS'))) . "')");
	    }
    }

    /**
     * Core Function : keys()
     *
     * Return keys to display in payment configuration (Backend)
     */
    function keys() {
        global $gx_version; // Get teh gambio version
		if($_SERVER['REQUEST_URI'] === DIR_WS_CATALOG.'admin/modules.php?set=payment&module=novalnet_invoice&action=edit'){
		echo '<script src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet.js" type="text/javascript"></script>';
		 if($_SESSION['GX'] == '1' ){
			 $auth = MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE;
			 echo '<input type="hidden" id="invoice_auth" value= '.$auth.' />';
		 }
	    }
	    if($_SESSION['GX'] == '1' ){
				 echo '<input type="hidden" id="gx_configurations" value="1" />';
			 $alias_menu = array();
		    if($gx_version >= '3.1.1.0' ){
			    $alias_menu = array_merge($alias_menu,array('configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS'));
		    }
		   $return_key = array_merge(array(
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALLOWED',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_MANUAL_CHECK_LIMIT',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_FRAUDMODULE_CALLBACK',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_FRAUDMODULE_SMS',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_FORCE',
        ),$alias_menu);
          $alice_val = 'configuration/MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS';
		}else{
			$alias_menu = array();
		   if($gx_version >= '3.1.1.0' ) {
			   $alias_menu = array_merge($alias_menu,array('MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS'));
		      }
		 $return_key = array_merge(array(
            'MODULE_PAYMENT_NOVALNET_INVOICE_ALLOWED',
            'MODULE_PAYMENT_NOVALNET_INVOICE_STATUS',
            'MODULE_PAYMENT_NOVALNET_INVOICE_TEST_MODE',
            'MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE',
            'MODULE_PAYMENT_NOVALNET_INVOICE_MANUAL_CHECK_LIMIT',
            'MODULE_PAYMENT_NOVALNET_INVOICE_ENABLE_FRAUDMODULE',
            'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_LIMIT',
            'MODULE_PAYMENT_NOVALNET_INVOICE_DUE_DATE',
            'MODULE_PAYMENT_NOVALNET_INVOICE_ALLOW_B2B',
            'MODULE_PAYMENT_NOVALNET_INVOICE_VISIBILITY_BY_AMOUNT',
            'MODULE_PAYMENT_NOVALNET_INVOICE_ENDCUSTOMER_INFO',
            'MODULE_PAYMENT_NOVALNET_INVOICE_SORT_ORDER',
            'MODULE_PAYMENT_NOVALNET_INVOICE_ORDER_STATUS',
            'MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS',
            'MODULE_PAYMENT_NOVALNET_INVOICE_PAYMENT_ZONE',
            'MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE',
            'MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT',
            'MODULE_PAYMENT_NOVALNET_INVOICE_PENDING_ORDER_STATUS',
            'MODULE_PAYMENT_NOVALNET_INVOICE_FORCE',
        ),$alias_menu);
          $alice_val = 'MODULE_PAYMENT_NOVALNET_INVOICE_ALIAS';
		}
	    
        foreach ($return_key as $key => $value) {
            if( ($gx_version >= '3.1.1.0' && $value == $alice_val)) {
				unset($return_key[$key]);
                break;
			}
        }
        return $return_key;
    }

    /**
     * To Proceed and validate Guarantee payment basic requirements in checkout
     *
     * @param integer $orderamount
     * return string
     */
	function proceedToInvoiceGuranteePayment($orderamount) {
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
		$minimum_amount_gurantee    = trim(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT) != '' ? trim(MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT) : '999';
		$order_amount = NovalnetHelper::getPaymentAmount();
		// Check guarantee payment
		if (MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE == 'true') {
		    	if ($orderamount >= $minimum_amount_gurantee && in_array(strtoupper($order->billing['country']['iso_code_2']), array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' )) && $order->info['currency'] == 'EUR' && $delivery_address === $billing_address) {
				if (isset($_SESSION['novalnet'][$this->code]['guarantee_error'])) {
					unset($_SESSION['novalnet'][$this->code]['guarantee_error']);
				}
				$_SESSION['novalnet'][$this->code]['guarantee'] = TRUE;
			} else if (MODULE_PAYMENT_NOVALNET_INVOICE_FORCE == 'true') {
				if (isset($_SESSION['novalnet'][$this->code]['guarantee'])) {
					unset($_SESSION['novalnet'][$this->code]['guarantee']);
				}
			} else {								
				$_SESSION['novalnet'][$this->code]['guarantee'] = TRUE;
				$_SESSION['novalnet'][$this->code]['guarantee_error'] = TRUE;
				if ($delivery_address !== $billing_address) {
					return MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_ADDRESS;
				   }else if(!in_array(strtoupper($order->billing['country']['iso_code_2']), array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HU', 'HR', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK' ))){
					return MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_COUNTRY;
				   }else if ($order->info['currency'] != 'EUR'){
				    return MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_CURRENCY;
				   }else if($order_amount <= $minimum_amount_gurantee ){
					$amount = MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT !='' ? number_format((MODULE_PAYMENT_NOVALNET_INVOICE_GUARANTEE_MINIMUM_ORDER_AMOUNT /100), 2, ',', ' ') : '9,99';
					return MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_AMOUNT.$amount.' EUR )';
				  }
			}
		} else {
			if(isset($_SESSION['novalnet'][$this->code]['guarantee'])) {
				unset($_SESSION['novalnet'][$this->code]['guarantee']);
			}
		}		
	}
}

