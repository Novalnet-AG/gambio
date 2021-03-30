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
 * Script : novalnet_cc.php
 *
 */
require_once(DIR_FS_CATALOG . 'ext/novalnet/NovalnetHelper.class.php');
class novalnet_cc {
    var $code, $title, $public_title, $sort_order, $description, $form_action_url, $enabled, $test_mode;

    /**
     * Core Function : Constructor()
     *
     */
    function novalnet_cc() {
        global $order;
        $post               = $_REQUEST;
        $this->code         = 'novalnet_cc';
        $this->title        = defined('MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE') ? 'Novalnet '. MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE : '';
        $this->public_title = defined('MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE') ? MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE :'';
        $this->sort_order   = 0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) { // Allow if payment enable only
			$this->enabled   = ((defined('MODULE_PAYMENT_NOVALNET_CC_STATUS') && MODULE_PAYMENT_NOVALNET_CC_STATUS == 'true') ? true : false);
			if (((defined('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE') && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'true') || (defined('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE') &&MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE =='true' )) && (empty($post['novalnet_ccchange_account']))) {
				$this->tmpOrders       = true;
				$this->tmpStatus       = defined('MODULE_PAYMENT_NOVALNET_PAYMENT_PENDING_STATUS') ? MODULE_PAYMENT_NOVALNET_PAYMENT_PENDING_STATUS : '';
				$this->form_action_url =  'https://payport.novalnet.de/pci_payport';
			}
			$this->sort_order   = defined('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER') && MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER != '' ? MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER : 0;
			$this->test_mode = ((defined('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE') && MODULE_PAYMENT_NOVALNET_CC_TEST_MODE == 'true') ? true : false); // To check the test mode
			$result = xtc_db_query("SHOW TABLES LIKE 'gx_configurations'");
            $gx_config = $result->num_rows; 
            $_SESSION['GX'] = $gx_config;
			//shop type
            if($_SESSION['GX'] == '1' ){
				 $this->shop_type = MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK == 'true' ? 'ONECLICK' : ( MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT == 'true' ? 'ZEROAMOUNT' : 'false');
		    }else{
               $this->shop_type = ((defined('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE')) ? MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE : '' );
		    }
			NovalnetHelper::getLastSuccessPayment($this->code); // By default last transaction payment select in checkout page
        }
       
        if (is_object($order))
            $this->update_status();
    }

    /**
     * check if zone is allowed to see module
     *
     * Core Function : update_status()
     */
    function update_status() {
        global $order;
        if ($_SESSION['shipping']['id'] == 'selfpickup_selfpickup') {
            $this->enabled = false;
        }
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE > 0)) {
            $check_flag  = false;
            $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = xtc_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1 || ($check['zone_id'] == $order->delivery['zone_id']) ) {
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
     * Core Function : javascript_validation()
     *
     * Javascript validation takes place
     * @return boolean
     */
    function javascript_validation() {
        return false;
    }

    /**
     * Core Function : selection()
     *
     * Display checkout form in chekout payment page
     * @return payment form
     */
    function selection()     {
		// Get ordet total amount
		$order_amount = NovalnetHelper::getPaymentAmount();
		// Payment hide in checkout page when condition was true
        if (NovalnetHelper::merchantValidate($this->code) || !NovalnetHelper::hidePaymentVisibility($this->code, $order_amount) || strpos(MODULE_PAYMENT_INSTALLED, 'novalnet_config') === false) { // Payment hide in checkout page
            if (!empty($_SESSION['payment']) && $_SESSION['payment'] == $this->code) {
                unset($_SESSION['payment']);
            }
            return false;
        }
        // Unset the novalnet session variable
        if (!empty($_SESSION['payment']) && isset($_SESSION['novalnet'][$this->code]['tid']) && $_SESSION['payment'] != $this->code) { // Unset tid
            unset($_SESSION['novalnet'][$this->code]['tid']);
        }
        // Get the end customer serialize details from database for one click shopping
        $payment_details = NovalnetHelper::getPaymentDetails($_SESSION['customer_id'], $this->code);
        $payment_details = unserialize($payment_details['payment_details']);
        // To check the test mode in shop
        $test_mode       = $this->test_mode == 1 ? MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG : '';
        // zero amount shop type text
        $test_mode      .=  $this->shop_type == 'ZEROAMOUNT'  ? '<br>'.MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT_TEXT : '';
        // Merchant can show the information for end customer in checkout page
        $endcustomerinfo = trim(strip_tags(MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO)) != '' ? trim(strip_tags(MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO)) : '';
        $this->description = (MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'true' || MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE == 'true') ? MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_TEXT_DESCRIPTION :  MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION;
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
		$selection = array('id'       => $this->code,
							'module'      => $title,
							'description' => $this->description .'<br>'. $test_mode . '<br><span id ="notification_buyer_wrap">' . $endcustomerinfo.'</span><br>'); // Displaying notification message and payment description
		if($shop_version > '3810') {
			if((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true'){
				 if(((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'true') &&  ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'false')){
						$selection['logo_url'] .= xtc_href_link('images/icons/payment/novalnet_visa_master_amex.png', '', 'SSL', false, false, false, true, true);
				}else if(((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'true') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'false')){
						$selection['logo_url'] .= xtc_href_link('images/icons/payment/novalnet_visa_master_maestro.png', '', 'SSL', false, false, false, true, true);
				}else if(((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'true') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'true')){
						$selection['logo_url'] .= xtc_href_link('images/icons/payment/novalnet_visa_master_amex_maestro.png', '', 'SSL', false, false, false, true, true);
				} else if(((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'false') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'false')){
						$selection['logo_url'] .= xtc_href_link('images/icons/payment/novalnet_cc.png', '', 'SSL', false, false, false, true, true);
				}
			}else {
				$selection['logo_url'] = '';
				$selection['logo_alt'] = '';
			}
		}
        // To process the normal iframe in checkout page
		if ($this->shop_type != 'ONECLICK' || ($this->shop_type == 'ONECLICK'  && empty($payment_details) || (MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'true' || MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE == 'true'))) { // Displaying iframe form type
				$selection['fields'] = array( array('title'=> $this->getIframecontent().'<script src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet_cc.js' . '" type="text/javascript"></script><br>'.NovalnetHelper::enableJavascript()));
			return $selection;
        } else if (!empty($payment_details)) {
			$form_show = isset($_SESSION['novalnet'][$this->code]['novalnet_ccchange_account']) ? $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] : 1;
            $oneclick  = ($this->shop_type == 'ONECLICK'  && !empty($payment_details)) ? '<nobr><span id ="novalnet_cc_new_acc" style="color:blue"><u><b>' . MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT . '</b></u></span></nobr>' : '';
            $selection['fields'][] = array('title' => $oneclick,
										'field' => '');
            $selection['fields'][] =   array( 'title' =>'<div class= nn_cc_ref_details>'. MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE.'</div>',
												  'field' => '<div class= nn_cc_ref_details>'. $payment_details['cc_card_type'].'</div>'
            );
            $selection['fields'][] = array(
                        'title' => '<div class= nn_cc_ref_details>'. MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER.'</div>',
                        'field' =>'<div class= nn_cc_ref_details><nobr>'. $payment_details['cc_holder'].'</nobr></div>'
			);
            $selection['fields'][] = array( 'title' => '<div class= nn_cc_ref_details>'. MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO .'</div>',
												'field' => '<div class= nn_cc_ref_details>'.$payment_details['cc_no'] . '<input type="hidden" id="nn_payment_ref_tid_cc" name="nn_payment_ref_tid" value="' . $payment_details['tid'] . '"/><input type="hidden" name="novalnet_ccchange_account" id="novalnet_ccchange_account" value="' . $form_show . '"/></div>'
            );
            $selection['fields'][] = array( 'title' => '<div class= nn_cc_ref_details>'. MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE.'</div>',
												'field' => '<div class= nn_cc_ref_details>'. $payment_details['cc_exp_month'] . ' / ' . $payment_details['cc_exp_year'] . '</div>'.xtc_draw_hidden_field('', DIR_WS_CATALOG, 'id="nn_root_cc_catalog"').'<script src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet_cc.js' . '" type="text/javascript"></script>',
            );
            $selection['fields'][] = array(  'title' => '',
												 'field' => '<div class="nn_cc_acc" style="display:none">
												 '.$this->getIframecontent().'
												 <input type="hidden" id="nn_lang_cc_new_account" value="' . MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT . '"/><input type="hidden" id="nn_lang_cc_given_account" value="' . MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT . '"/><input type="hidden" id="nn_cc_iframe_load" name="nn_cc_iframe_load" value="" /></div>'.'<br>'.NovalnetHelper::enableJavascript()
			);
        }
        return $selection;
    }

    /**
     * Core Function : pre_confirmation_check()
     *
     * Perform validations for post values
     * @return boolean
     */
    function pre_confirmation_check() {
        $post = $_REQUEST;
        // Get the payment type
        $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] = ($this->shop_type == 'ONECLICK'  && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'false' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE =='false'  ) ? $post['novalnet_ccchange_account'] : '0';
        $_SESSION['novalnet'][$this->code]['nn_cc_pan_hash'] = !empty($post['nn_pan_hash']) ? $post['nn_pan_hash'] : '';
        $_SESSION['novalnet'][$this->code]['nn_cc_unique_id'] = !empty($post['nn_cc_uniqueid']) ? $post['nn_cc_uniqueid'] : '';
        // if javascript not enable in system then show the error message
		if (!empty($post['nn_js_enabled'])) {
			$payment_error_return = 'payment_error=' . $this->code . '&error_message=' . MODULE_PAYMENT_NOVALNET_NO_SCRIPT;
             xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
		}
		// To validate the pan hash and unique id
        if ($post['novalnet_ccchange_account'] != '1' && (empty($post['nn_pan_hash']) || empty($post['nn_cc_uniqueid']))) {
			$payment_error_return = 'payment_error=' . $this->code . '&error_message=' . MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS;
             xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
		}

		// Assign the reference transaction tid in session
        if (isset($post['nn_payment_ref_tid'])) {
			$_SESSION['novalnet'][$this->code]['nn_payment_ref_tid'] = $post['nn_payment_ref_tid'];
			// if cc_3d enable unset the reference transaction details
			if ((MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'true' || MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE == 'true') || $post['novalnet_ccchange_account'] == 0) {
				unset($_SESSION['novalnet'][$this->code]['nn_payment_ref_tid']);
			}
        }
    }

    /**
     * Core Function : confirmation()
     *
     * Displays confirmation page
     * @return boolean
     */
    function confirmation() {
		 // Assign order amount in session
        $_SESSION['novalnet'][$this->code]['payment_amount'] = NovalnetHelper::getPaymentAmount();
    }

    /**
     * Core Function : process_button()
     *
     * Payments redirects from shop to payment site (Note : if the payment is redirect)
     * @return boolean
     */
    function process_button() {
		return false;
    }

    /**
     * Core Function : before_process()
     *
     * Send params to Novalnet server (Note : if the payment uses curl request)
     */
    function before_process() {
        global $order;
        $post         = $_REQUEST;
        $param_inputs = array_merge((array) $order, $_SESSION['novalnet'][$this->code]);
        // To process the CC_3D response validation and to update the transaction details in shop
        if (isset($post['tid'])) {
            // To validate the response
            $before_process_response = NovalnetHelper::validateRedirectResponse($this->code, $post);
            // Novalnet transaction details update the orders
            $order->info['comments'] = !empty($_SESSION['novalnet'][$this->code]['novalnet_comments']) ? $_SESSION['novalnet'][$this->code]['novalnet_comments'] : $before_process_response['novalnet_comments'];
			xtc_db_perform(TABLE_ORDERS, array(
				'comments' 		=> $order->info['comments']
			), "update", "orders_id=".$post['order_no']);
			xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, array(
				'comments' 		   => $order->info['comments']
			), "update", "orders_id=".$post['order_no']);

        } else if (!isset($post['tid']) && $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] != '1' && (MODULE_PAYMENT_NOVALNET_CC_3D_SECURE != 'true' && MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE != 'true') && empty($_SESSION['novalnet'][$this->code]['nn_payment_ref_tid'])) { // To process credit card iframe with curl call request
			//To get novalnet common payment params
            $urlparam = NovalnetHelper::getCommonParms($param_inputs, $this->test_mode);
            // To process on hold product
             if ((trim(MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) > 0 && $urlparam['amount'] >= trim(MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'true' )) || (empty (MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'true' ))){ // If condition true then order proceed on-hold transaction
                $urlparam['on_hold'] = 1;
            }
            // The create payment ref params send to novalnet server
            if(in_array( $this->shop_type, array( 'ONECLICK', 'ZEROAMOUNT'))) {
				$urlparam['create_payment_ref'] = 1;
			}
            // Get the vendor configuration details
            $vendor_details = NovalnetHelper::getVendorDetails();
            // To process the zero amount booking  and send the parms to novalnet server
            if ($this->shop_type == 'ZEROAMOUNT') {
				if($vendor_details['tariff_type'] == 2) {
					unset($vendor_details['tariff_type'],$urlparam['on_hold']);
					$urlparam['amount']                                   = 0;
					$_SESSION['novalnet'][$this->code]['serialize_data']  = serialize($urlparam);
					$_SESSION['novalnet'][$this->code]['reference_transaction'] = 1;
				}else {
					unset($urlparam['create_payment_ref']);
				}
            }
            $urlparam['nn_it']         = 'iframe';
			$urlparam['pan_hash']      = $_SESSION['novalnet'][$this->code]['nn_cc_pan_hash'];
			$urlparam['unique_id']     = $_SESSION['novalnet'][$this->code]['nn_cc_unique_id'];
			// Payment call request send to the novalnet server
			$response                  = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparam);
            parse_str($response, $payment_response);
            if ($payment_response['status'] == '100') { //Payment success
				NovalnetHelper::getPostValues($this->code, $urlparam, $payment_response);
            } else { // Payment failed
				unset($_SESSION['novalnet'][$this->code]['reference_transaction']);
                $stauts_failed = NovalnetHelper::getServerResponse($payment_response);
                $payment_error_return = 'payment_error=' . $this->code . '&error_message=' . utf8_decode($stauts_failed);
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
            }
        } else if ($this->shop_type == 'ONECLICK'  && $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] == '1' && !empty($_SESSION['novalnet'][$this->code]['nn_payment_ref_tid'])) { // To process the one click shopping with reference transaction
            $urlparam                = NovalnetHelper::getCommonParms($param_inputs); // Get the novalnet common payment params
           if ((trim(MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) > 0 && $urlparam['amount'] >= trim(MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'true' )) || (empty (MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'true' ) )){ // If condition true then order proceed on-hold transaction
                $urlparam['on_hold'] = 1;
            }
            $urlparam['payment_ref'] = $_SESSION['novalnet'][$this->code]['nn_payment_ref_tid'];
            $_SESSION['novalnet'][$this->code]['reference_transaction'] = 1;
            // Payment call request send to the novalnet server
            $response                = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparam);
            parse_str($response, $payment_response);
            if ($payment_response['status'] == '100') { // Payment success
				// To store the server response in session and form novalnet transaction details in novalnet table
				NovalnetHelper::getPostValues($this->code, $urlparam, $payment_response);
            } else { // Payment failed
				unset($_SESSION['novalnet'][$this->code]['reference_transaction']);
                $stauts_failed = NovalnetHelper::getServerResponse($payment_response);
                $payment_error_return = 'payment_error=' . $this->code . '&error_message=' . utf8_decode($stauts_failed);
                // Redirect to checkout page
                xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
            }
        } else {
			$order->info['order_status'] = MODULE_PAYMENT_NOVALNET_PAYMENT_PENDING_STATUS;
		}
    }
   
    /**
     * Core Function : payment_action()
     *
     * Send params to Novalnet server
     */
    function payment_action(){
		 global $order,$insert_id;
        // To process the CC_3D form
        if ((MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'true' || MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE == 'true' )&& $_SESSION['novalnet'][$this->code]['novalnet_ccchange_account'] != '1') { // To process redirection method
            $param_inputs = array_merge((array) $order, $_SESSION['novalnet'][$this->code]);
            //To get novalnet common payment params
            $urlparam     = NovalnetHelper::getCommonParms($param_inputs);
            // To process the on hold process
            if ((trim(MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) > 0 && $urlparam['amount'] >= trim(MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'true' )) || (empty (MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE == 'true' ))){ // If condition true then order proceed on-hold transaction
                $urlparam['on_hold'] = 1;
            }
            // Get the vendor configuration details
            $vendor_details = NovalnetHelper::getVendorDetails();
            // To process the zero amount booking and send params to novalnet server
            if (!in_array($vendor_details['tariff_type'], array( 1, 3, 4 )) && $this->shop_type == 'ZEROAMOUNT' ) {
                unset($urlparam['on_hold'],$vendor_details['tariff_type']);
                $urlparam['create_payment_ref'] = 1;
                $urlparam['amount']             = 0;
                $_SESSION['novalnet'][$this->code]['serialize_data']  = serialize($urlparam);
                $_SESSION['novalnet'][$this->code]['reference_transaction'] = 1;
            }
            // To encode the vendor configuration and get the hash value
			 NovalnetHelper::getRedirectParams($urlparam);
            if(MODULE_PAYMENT_NOVALNET_CC_3D_SECURE == 'true')
				$urlparam['cc_3d'] = 1;
            $urlparam['pan_hash']        = $_SESSION['novalnet'][$this->code]['nn_cc_pan_hash'];
			$urlparam['unique_id']       = $_SESSION['novalnet'][$this->code]['nn_cc_unique_id'];
            $urlparam['return_method']   = $urlparam['error_return_method'] = 'POST';
            $urlparam['return_url']      = $urlparam['error_return_url'] = xtc_href_link('checkout_novalnet_confirmation.php', '', 'SSL');
            $urlparam['payment_type']    = 'CREDITCARD';
            $urlparam['order_no']        = $insert_id;
            $urlparam['nn_it']        = 'iframe';
            $_SESSION['novalnet'][$this->code]['urlparam'] = $urlparam;
            xtc_redirect(xtc_href_link('checkout_novalnet_confirmation.php', '', 'SSL', true, false));            
        }
	}
    /**
     * Core Function : after_process()
     *
     * Send params to Novalnet server (Note : if the payment uses curl request)
     * @param none
     * @return void
     */
    function after_process() {
        global $insert_id;
        // Update the order status in shop
        NovalnetHelper::updateOrderStatus($insert_id, $this->code); // Update the order status
        NovalnetHelper::doSecondCallProcess(array(
            'payment_type'  => $this->code,
            'order_no' => $insert_id
        )); // Perform paygate second call for transaction confirmations / order_no update
    }

    /**
     * Core Function : get_error()
     *
     * Show validation / error message
     * @return array
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
     *
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
			  $check_query  = xtc_db_query("select `value` from `gx_configurations` where `key` = 'configuration/MODULE_PAYMENT_NOVALNET_CC_ALLOWED'");
              $this->_check = xtc_db_num_rows($check_query);
		    }else{
               if (!isset($this->_check)) {
				$check_query  = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_CC_ALLOWED'");
                $this->_check = xtc_db_num_rows($check_query);
            }
	    }
        return $this->_check;
    }

    /**
     *
     * Core Function : install()
     *
     * Payment module installation
     * @return boolean
     */
    function install() {
        $label = "font-weight:normal;font-family:Roboto,Arial,sans-serif;font-size:13px;line-height:1.42857";
		$input_field = "height:30px;border:1px solid #ccc;";
	if($_SESSION['GX'] == '1' ){
		 xtc_db_query("insert into `gx_configurations` ( `key`, `value`,  `legacy_group_id`, `sort_order`, `type`, `last_modified`) values 
		('configuration/MODULE_PAYMENT_NOVALNET_CC_ALIAS',     'NN_CC', '6', '0', '',  now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_ALLOWED',   '', '6', '1', '',  now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_STATUS',   '', '6', '2', 'switcher' ,now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_TEST_MODE', '', '6', '3', 'switcher' , now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE' , 'false', '6', '4', 'switcher' , now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT', '', '6', '7', '',  now()),
		('configuration/MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE','', '6', '4', 'switcher' ,now()),        
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_3D_SECURE','', '6', '4', 'switcher' ,now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT','', '6', '5', 'switcher' ,now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT','', '6', '6', 'switcher' ,now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK','' , '6', '1', 'switcher' , now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT','' , '6', '1', 'switcher' , now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE', '.$label.', '6', '10','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT', '.$input_field.', '6', '10','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT', '.$label.', '6', '10','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT', '', '6', '10','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO', '', '6', '11','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER', '0', '6', '12', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS', '0',  '6', '13', 'order-status', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE', '0', '6', '14', 'geo-zone',now())
		 ");
	 }else{
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_key, configuration_value, configuration_group_id, sort_order,set_function, use_function, date_added)
        VALUES
        ('MODULE_PAYMENT_NOVALNET_CC_ALIAS', 'NN_CC', '6', '0', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_ALLOWED', '', '6', '1', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_STATUS','false', '6', '2', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_CC_STATUS\'," . 'MODULE_PAYMENT_NOVALNET_CC_STATUS' . ",' , '',now()),
        ('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE','false', '6', '3', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_CC_TEST_MODE\'," . 'MODULE_PAYMENT_NOVALNET_CC_TEST_MODE' . ",' , '', now()),
         ('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE','capture', '6', '4', 'xtc_mod_select_option(array(\'capture\' => MODULE_PAYMENT_NOVALNET_CAPTURE,\'authorize\' => MODULE_PAYMENT_NOVALNET_AUTHORIZE),\'MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE\'," .'MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT', '', '6', '7', '', '', now()),
		('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE','false', '6', '4', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE\'," . 'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE' . ",' ,'',now()),        
        ('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE','false', '6', '4', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE\'," . 'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE' . ",' ,'',now()),
        ('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT','false', '6', '5', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT\'," . 'MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT' . ",' ,'',now()),
        ('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT','false', '6', '6', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT\'," .'MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT'. ",' ,'',now()),
        ('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE','false', '6', '8', 'xtc_mod_select_option(array(\'false\' => MODULE_PAYMENT_NOVALNET_OPTION_NONE,\'ONECLICK\' => MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK,\'ZEROAMOUNT\' => MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT,),\'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE\'," . 'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE' . ",' ,'',now()),
        ('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE', '.$label.', '6', '10','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT', '.$input_field.', '6', '10','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT', '.$label.', '6', '10','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT', '', '6', '10','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO', '', '6', '11','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER', '0', '6', '12', '',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS', '0',  '6', '13', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
        ('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE', '0', '6', '14', 'xtc_cfg_pull_down_zone_classes(', 'xtc_get_zone_class_title',now())
        ");
	}
    }

   /**
     * Core Function : remove()
     *
     * Payment module uninstallation
     * @return boolean
     */
    function remove() {
		if($_SESSION['GX'] == '1' ){
			xtc_db_query("delete from `gx_configurations` where `key` in ('" . implode("', '", array_merge($this->keys(),array('configuration/MODULE_PAYMENT_NOVALNET_CC_ALIAS'))) . "')");
		}else{
		   xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", array_merge($this->keys(),array('MODULE_PAYMENT_NOVALNET_CC_ALIAS'))) . "')");
	    }
		
    }

    /**
     * Core Function : keys()
     *
     * Return keys to display in payment configuration (Backend)
     * @return boolean
     */
    function keys() {
		global $gx_version; // Get teh gambio version
		if($_SERVER['REQUEST_URI'] === DIR_WS_CATALOG.'admin/modules.php?set=payment&module=novalnet_cc&action=edit'){
		 echo '<script src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet.js" type="text/javascript"></script>';
		  if($_SESSION['GX'] == '1' ){
			 $auth = MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE;
			 echo '<input type="hidden" id="cc_auth" value= '.$auth.' />';
		 }
	    }
	     if($_SESSION['GX'] == '1' ){
				 echo '<input type="hidden" id="gx_configurations" value="1" />';
				 $alias_menu = array();
		   if($gx_version >= '3.1.1.0' ){
			    $alias_menu = array_merge($alias_menu,array('configuration/MODULE_PAYMENT_NOVALNET_CC_ALIAS'));
		    }
		    $return_key = array_merge(array(
			'configuration/MODULE_PAYMENT_NOVALNET_CC_ALLOWED',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_TEST_MODE',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_3D_SECURE',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT',
            'configuration/MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT'
        ),$alias_menu);
          $alice_val = 'configuration/MODULE_PAYMENT_NOVALNET_CC_ALIAS';
		}else{
			$alias_menu = array();
		  if($gx_version >= '3.1.1.0' ) {
			  $alias_menu = array_merge($alias_menu,array('MODULE_PAYMENT_NOVALNET_CC_ALIAS'));
		      }
		  $return_key = array_merge(array(
			'MODULE_PAYMENT_NOVALNET_CC_ALLOWED',
            'MODULE_PAYMENT_NOVALNET_CC_STATUS',
            'MODULE_PAYMENT_NOVALNET_CC_TEST_MODE',
            'MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE',
            'MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT',
            'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE',
            'MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE',
            'MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT',
            'MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT',
            'MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE',
            'MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT',
            'MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO',
            'MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER',
            'MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS',
            'MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE',
            'MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE',
            'MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT',
            'MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT'
        ),$alias_menu);
        $alice_val = 'MODULE_PAYMENT_NOVALNET_CC_ALIAS';
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
     * To get Iframe content in checkout page
     * 
     * @return void
     */
    function getIframecontent() {
        // Get the language for load iframe
		$lang = ((isset($_SESSION['language'])) && $_SESSION['language'] == 'english') ? 'en' : 'de';
		$server_ip = $_SERVER['SERVER_ADDR'];		
        // To form encoded merchant api with vendor details and server ip to set iframe source
		$api_key  = base64_encode( 'vendor=' . trim(MODULE_PAYMENT_NOVALNET_VENDOR_ID) . '&product=' . trim(MODULE_PAYMENT_NOVALNET_VENDOR_ID) . "&server_ip=$server_ip" );
		$shop_type = $this->shop_type;
		$iframe_path = 'https://secure.novalnet.de/cc?api='. $api_key . "&ln=$lang";
		return '<iframe scrolling="off" id="nnIframe" style ="margin: 0% 0% 0% -2%;background-color: transparent;background-image:none;border:none;" width="481px" src="'.$iframe_path.'" onload="getIframeForm()" frameBorder="0"></iframe>'.
		xtc_draw_hidden_field('', DIR_WS_CATALOG, 'id="nn_root_cc_catalog"').
		xtc_draw_hidden_field('nn_pan_hash', '', 'id="nn_pan_hash"').
		xtc_draw_hidden_field('nn_cc_uniqueid', '', 'id="nn_cc_uniqueid"').
		xtc_draw_hidden_field('', trim(MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE), 'id="nn_css_standard"').xtc_draw_hidden_field('', trim(MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT), 'id="nn_css_standard_input"').
		xtc_draw_hidden_field('', trim(MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT), 'id="nn_css_text"').xtc_draw_hidden_field('', trim($shop_type), 'id="nn_form_mode"')	.xtc_draw_hidden_field('', trim(defined('MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_HINT') ? MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_HINT : '' ), 'id="nn_iframe_cvc_hint"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_IFRAME_ERROR') ? trim(MODULE_PAYMENT_NOVALNET_CC_IFRAME_ERROR) : '', 'id="nn_iframe_error"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER') ? trim(MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER) : '', 'id="nn_iframe_holder_label"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_IFRAME_HOLDER_INPUT_TEXT')? trim(MODULE_PAYMENT_NOVALNET_CC_IFRAME_HOLDER_INPUT_TEXT) : '', 'id="nn_iframe_holder_input"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO') ? trim(MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO) : '', 'id="nn_iframe_number_label"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_IFRAME_NUMBER_INPUT_TEXT') ? trim(MODULE_PAYMENT_NOVALNET_CC_IFRAME_NUMBER_INPUT_TEXT) : '', 'id="nn_iframe_number_input"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE') ? trim(MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE): '', 'id="nn_iframe_expire_label"').xtc_draw_hidden_field('',defined('MODULE_PAYMENT_NOVALNET_CC_IFRAME_EXPIRYDATE_INPUT_TEXT') ? trim(MODULE_PAYMENT_NOVALNET_CC_IFRAME_EXPIRYDATE_INPUT_TEXT) : '', 'id="nn_iframe_expire_input"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC') ? trim(MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC) :'', 'id="nn_iframe_cvc_label"').xtc_draw_hidden_field('', defined('MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_INPUT_TEXT') ? trim(MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_INPUT_TEXT) : '', 'id="nn_iframe_cvc_input"');
	}
}

