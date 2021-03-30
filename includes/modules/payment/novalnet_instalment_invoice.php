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
 * Script : novalnet_instalment_invoice.php
 *
 */
require_once(DIR_FS_CATALOG . 'ext/novalnet/NovalnetHelper.class.php');
class novalnet_instalment_invoice {
    var $code, $title, $public_title, $sort_order, $enabled, $test_mode;

    /**
     * Core Function : Constructor()
     *
     */
    public function __construct() {
        global $order;
        $this->code         = 'novalnet_instalment_invoice';
        $this->title        = defined('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEXT_TITLE') ? 'Novalnet '. MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEXT_TITLE : '';
        $this->public_title = defined('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PUBLIC_TITLE') ? MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PUBLIC_TITLE : '';
        $this->sort_order   =  0;
        if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false) {
			$this->sort_order   = defined('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER') && trim(MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER) != '' ? trim(MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER) : 0;
            $this->enabled      = ((defined('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS') && MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS == 'true') ? true : false);
            $result = xtc_db_query("SHOW TABLES LIKE 'gx_configurations'");
            $gx_config = $result->num_rows; 
            $_SESSION['GX'] = $gx_config; 
            $this->test_mode    = ((defined('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE') && MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE == 'true') ? true : false); // To check the test mode
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
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE > 0)) {
            $check_flag  = false;
            $check_query = xtc_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
			//instalment validation
			if(empty(NovalnetHelper::novalnet_instalment_validation($order_amount))){
			   return false;
			}
			
			// To check the test mode in shop
			$test_mode                 = $this->test_mode == 1 ? MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG : '';
			// Merchant can show the information for end customer in checkout page
			$endcustomerinfo           = trim(strip_tags(MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO)) != '' ? trim(strip_tags(MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO)) : '';
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
					'description' => MODULE_PAYMENT_NOVALNET_INV_PRE_DESC . '<br>' . $test_mode .'<br><span id ="notification_buyer_wrap">' . $endcustomerinfo.'</span><br><span style="color:red">'.'</span><br><input type="hidden" id="nn_root_invoice_catalog" value="' . DIR_WS_CATALOG . '"/> <script src="https://code.jquery.com/jquery-1.12.4.js"></script><link rel="stylesheet" type="text/css" href="' . DIR_WS_CATALOG . 'ext/novalnet/css/novalnet.css' . '">
							<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script><script type= text/javascript src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet_instalment_invoice.js"></script><noscript><input type="hidden" name="js_enabled" value=1 ><br /></noscript>');
			if($shop_version > '3810') {
				if((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true'){
					$selection['logo_url'] = xtc_href_link('images/icons/payment/novalnet_invoice.png', '', 'SSL', false, false, false, true, true);
				}else {
					$selection['logo_url'] = '';
					$selection['logo_alt'] = '';
				}
			}
			if (($order->billing['company'] == '') || ($order->billing['company'] != '' && MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B == 'false')) {
				$selection['fields'][] = array(
							'title' => MODULE_PAYMENT_GUARANTEE_FIELD,
							'field' => '<div>'.xtc_draw_input_field($this->code.'_dob','', 'id="'.$this->code.'_dob" AUTOCOMPLETE=off class=form-control form-type-date hasDatepicker').xtc_draw_hidden_field('', $_SESSION['language_code'], 'id="nn_shop_lang"').'</div>
							<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
							<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
							<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
							<script src="https://cdn.novalnet.de/js/v2/NovalnetUtility.js"></script>
							<script type= text/javascript src="' . DIR_WS_CATALOG . 'ext/novalnet/js/datepicker-'.$_SESSION['language_code'].'.js"></script>
							<script type="text/javascript">
								var j = jQuery.noConflict();
								jQuery(document).ready(function() {
									j("#novalnet_instalment_invoice_dob").keydown(function(){
									  return NovalnetUtility.isNumericBirthdate( this, event )
									});
								});
						</script><br/>'
				);
			}	
				//instalment cylces
        $novalnet_instalment_cycle = explode('|',MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE );
		$novalnet_recurring_period_cycle = '1m';
		$selection['fields'][] = array(
		'title' => MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT_TITLE,
	    'field' => MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT_DESC
		);
				$selection['fields'][] = array(
							'title' => MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT.' '.($order_amount/100).$order->info['currency'],
							'field' => '<select class="form-control" name="novalnet_global_recurring_period_cycles" id ="novalnet_global_recurring_period_cycles" >
      '.NovalnetHelper::novalnet_get_instalment_cycles($order_amount/100,$novalnet_instalment_cycle).'</select><br><table id="novalnet_instalment_table_invoice" style="display:none; "><thead></thead><tbody></tbody></table><input type ="hidden" id="order_amount" name="order_amount" value="'.$order_amount/'100'.'"><input type ="hidden" id="nn_instalment_date" name="nn_instalment_date" value="'.NovalnetHelper::instalment_date($novalnet_instalment_cycle,$novalnet_recurring_period_cycle).'">',
				);
			if(NovalnetHelper::novalnet_get_instalment_cycles($order_amount/100,$novalnet_instalment_cycle) == '0'){
					return false;
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
		$_SESSION['novalnet'][$this->code]['novalnet_instalment_invoice_dob'] = date('Y-m-d',strtotime($post['novalnet_instalment_invoice_dob']));
		$_SESSION['novalnet'][$this->code]['novalnet_global_recurring_period_cycles'] = $post['novalnet_instalment_invoice_dob'];
		$current_age = date('Y-m-d',strtotime($post['novalnet_instalment_invoice_dob']));
		if (($order->billing['company'] == '') || ($order->billing['company'] != '' && MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B == 'false')){
			if ($post['novalnet_instalment_invoice_dob'] == '') {
				$error_message = MODULE_PAYMENT_GUARANTEE_PAYMENT_DOB_EMPTY_ERROR_MESSAGE;
			} else if ($post['novalnet_instalment_invoice_dob'] != '') {
				if(time() < strtotime('+18 years', strtotime($current_age))) {
					$error_message = MODULE_PAYMENT_GUARANTEE_PAYMENT_AGE_ERROR_MESSAGE;
				} 	
			}
		}
		if ( $error_message != '') {
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error_message=' . $error_message, 'SSL', true, false));		
		}
		
        // if javascript not enable in system then show the error message
        if (!empty($post['js_enabled'])) {
			$payment_error_return = 'payment_error=' . $this->code . '&error_message=' . MODULE_PAYMENT_NOVALNET_NO_SCRIPT;
             xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
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
    }

    /**
     * Core Function : process_button()
     *
     * Payments redirects from shop to payment site
     */
    function process_button() {
        $post = $_REQUEST;
       if (isset($_SESSION['novalnet'][$this->code]['payment_amount'])) { //Store the values in session
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
           $param_inputs              = array_merge((array) $order, $_SESSION['novalnet'][$this->code]);
           $urlparam                  = NovalnetHelper::getCommonParms($param_inputs);
            // To process on hold product
            if ((trim(MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT) > 0 && $order_amount >= trim(MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE == 'true')) || (empty (MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT) && (MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE === 'authorize' || MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE == 'true'))){ // If condition true then order proceed on-hold transaction
                $urlparam['on_hold'] = 1;
            }
            if (($order->billing['company'] == '') || ($order->billing['company'] != '' && MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B == 'false')) {
			    $urlparam['birth_date']    = (date('Y-m-d', strtotime($_SESSION['novalnet'][$this->code]['novalnet_instalment_invoice_dob'])));
			   unset($urlparam['company']);
	        }else{
			 unset($urlparam['birth_date']);
		    }
           $urlparam['payment_type']  = 'INSTALMENT_INVOICE';
           $urlparam['instalment_cycles'] = $_SESSION['novalnet'][$this->code]['novalnet_global_recurring_period_cycles'];
		   $urlparam['instalment_period'] = '1m';
            // Payment call request send to the novalnet server
            $response = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparam);
            parse_str($response, $data);
           
            // Form the novalnet transaction comments and transaction details are stored in session variable
            $form_comments = NovalnetHelper::checkPaymentStatus($this->code, $data,$urlparam);
           
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
			  $check_query  = xtc_db_query("select `value` from `gx_configurations` where `key` = 'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED'");
              $this->_check = xtc_db_num_rows($check_query);
		    }else{
               if (!isset($this->_check)) {
				$check_query  = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED'");
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
		('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED', '', '6', '1', '', now()),
		('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS', 'NN_INV', '6', '0', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS','false', '6', '2', 'switcher',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE','false', '6', '3', 'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE','2', '6', '18', 'multiselect' , now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE','false', '6', '4' ,'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT', '', '6', '7', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B','true', '6', '4' ,'switcher', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_VISIBILITY_BY_AMOUNT', '1998', '6', '10','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO', '', '6', '8','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER', '0', '6', '9', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ORDER_STATUS', '0',  '6', '10', 'order-status', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PENDING_ORDER_STATUS', '0',  '6', '11', 'order-status', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE', '0', '6', '12', 'geo-zone',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE','false', '6', '18', 'switcher', now())
        
		 ");
	 }else{
		xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
        (configuration_key, configuration_value, configuration_group_id, sort_order,set_function, use_function, date_added)
      VALUES ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED', '', '6', '1', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS', 'NN_INV', '6', '0', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS','false', '6', '2', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS\'," .'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS'. ",' , '',now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE','false', '6', '3', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE\'," .'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE','2', '6', '18', '' ,'', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE','capture', '6', '4', 'xtc_mod_select_option(array(\'capture\' => MODULE_PAYMENT_NOVALNET_CAPTURE,\'authorize\' => MODULE_PAYMENT_NOVALNET_AUTHORIZE),\'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE\'," .'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT', '', '6', '7', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B', 'true', '6', '7', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE),\'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B\'," .'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B'. ",' , '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO', '', '6', '8','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_VISIBILITY_BY_AMOUNT', '1998', '6', '8','',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER', '0', '6', '9', '',  '', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ORDER_STATUS', '0',  '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PENDING_ORDER_STATUS', '0',  '6', '11', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE', '0', '6', '12', 'xtc_cfg_pull_down_zone_classes(', 'xtc_get_zone_class_title',now()),
        ('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE','', '6', '18', '' ,'', now())
       ");
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
			xtc_db_query("delete from `gx_configurations` where `key` in ('" . implode("', '", array_merge($this->keys(),array('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS'))) . "')");
		}else{
		   xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", array_merge($this->keys(),array('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS'))) . "')");
	    }
    }

    /**
     * Core Function : keys()
     *
     * Return keys to display in payment configuration (Backend)
     */
    function keys() {
        global $gx_version; // Get teh gambio version
		if($_SERVER['REQUEST_URI'] === DIR_WS_CATALOG.'admin/modules.php?set=payment&module=novalnet_instalment_invoice&action=edit'){
			echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
				<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
				<link rel="stylesheet" type="text/css" href="javascript/tab/tab.css">
				<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />';
		    echo '<script src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet.js" type="text/javascript"></script>';
		    echo '<style> ul.select2-selection__rendered{ height: 60px; overflow: scroll !important; } </style>';
	        if($_SESSION['GX'] == '1' ){
			 $auth = MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE;
			 echo '<input type="hidden" id="instalment_invoice_auth" value= '.$auth.' />';
		    }
			 $cycles = MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE != '' ? MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE : '';
			 echo '<input type="hidden" name="novalnet_instalment_invoice_selected_cycle[]" id="novalnet_instalment_invoice_selected_cycle" value= '.$cycles.' />';
	   
	    }
	    if($_SESSION['GX'] == '1' ){
				 echo '<input type="hidden" id="gx_configurations" value="1" />';
			 $alias_menu = array();
		    if($gx_version >= '3.1.1.0' ){
			    $alias_menu = array_merge($alias_menu,array('configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS'));
		    }
		   $return_key = array_merge(array(
		    'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_VISIBILITY_BY_AMOUNT',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ORDER_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PENDING_ORDER_STATUS',
            'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE',
        ),$alias_menu);
          $alice_val = 'configuration/MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS';
		}else{
			$alias_menu = array();
		   if($gx_version >= '3.1.1.0' ) {
			   $alias_menu = array_merge($alias_menu,array('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS'));
		      }
		 $return_key = array_merge(array(
		    'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_VISIBILITY_BY_AMOUNT',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ORDER_STATUS',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PENDING_ORDER_STATUS',
            'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE',
        ),$alias_menu);
          $alice_val = 'MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALIAS';
		}
	    
        foreach ($return_key as $key => $value) {
            if( ($gx_version >= '3.1.1.0' && $value == $alice_val)) {
				unset($return_key[$key]);
                break;
			}
        }
        return $return_key;
    }

}

