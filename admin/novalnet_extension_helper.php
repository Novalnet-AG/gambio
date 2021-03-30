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
 * Script : novalnet_extension_helper.php
 *
 */
require ('includes/application_top.php');
include_once(DIR_FS_CATALOG."ext/novalnet/NovalnetHelper.class.php");
include_once(DIR_FS_LANGUAGES . $_SESSION['language']."/modules/payment/novalnet.php");
include_once(DIR_FS_INC . 'xtc_format_price_order.inc.php');
include_once(DIR_FS_CATALOG . 'admin/includes/classes/order.php');
$request   = $_POST;
$datas     = NovalnetHelper::getNovalnetTransDetails($_POST['oID']);
$request['remote_ip'] = xtc_get_ip_address();
 
	if ((!empty($request['nn_refund_confirm']) && $request['refund_trans_amount'] != '' ) || (!empty($request['nn_instalment_cancel']))) { // To process refund process
            $refund_params = array(
                'vendor' 		=> $datas['vendor'],
                'product' 		=> $datas['product'],
                'key' 			=> $datas['payment_id'],
                'tariff' 		=> $datas['tariff_id'],
                'auth_code' 	=> $datas['auth_code'],
                'refund_request'=> '1',
                'remote_ip'     => $request['remote_ip'],
                'tid' 			=> $datas['tid'],
                'refund_param' 	=> $request['refund_trans_amount']
            );
            if (in_array($datas['payment_id'] ,array('96','97')) && !empty($request['tid'])){
				 $refund_params['tid'] = $request['tid'];
				if($request['refund_trans_amount'] == $datas['amount']){
				   $refund_params['refund_param'] = 'DEACTIVATE';
			     } 
			}
			if(!empty($request['nn_instalment_cancel'])){
				  $refund_params['refund_param'] = 'DEACTIVATE';
		    }
            if (!empty($request['refund_ref'])) { // Assigning refund ref tid
                $refund_params['refund_ref'] = $request['refund_ref'];
            }
            // Send the request to Novalnet server
            $response = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $refund_params);
            parse_str($response, $data);
            $order_status            = '';
            $param['gateway_status'] = $data['tid_status'];
            if ($data['status'] == 100) { // Payment success
				if($refund_params['refund_param'] == 'DEACTIVATE' && $data['tid_status'] == 100 && in_array($datas['payment_id'] ,array('96','97'))){
						$instalment_cycle_amount = unserialize($datas['payment_details']);
						 $amount = $instalment_cycle_amount['0']['instalment_cycle_amount']*100;
				 }else{
					 $amount = $request['refund_trans_amount'];
				 }
				
                $message .= PHP_EOL . sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG), $datas['tid'], xtc_format_price_order(( $amount/ 100), 1, $datas['currency']));
                $newtid = !empty($data['tid']) ? $data['tid'] : (!empty($data['paypal_refund_tid']) ? $data['paypal_refund_tid'] : '');
                if (!empty($newtid)) { // Get new tid for refund process
                    $message .= sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG), $newtid);
                }
                $message .= PHP_EOL;
                $order_status       = xtc_db_fetch_array(xtc_db_query("SELECT orders_status from " . TABLE_ORDERS . " where orders_id = " . xtc_db_input($request['oID'])));
                $order_status_value = $order_status['orders_status'];
                $param['refund_amount'] = ($datas['refund_amount']+$request['refund_trans_amount']);
                // Transaction details update the shop Novalnet table
                xtc_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $datas['tid'] . "'");
                
                 if (in_array($datas['payment_id'] ,array('96','97'))){
					$instalment_cycle_data_after_update   = unserialize($datas['payment_details']);
                    $instalment_cycle_amount_after_update = $instalment_cycle_data_after_update[$request['current_cycle_instalment']]['instalment_cycle_amount']*100 - $param['refund_amount'];
                     $instalment_cycle_data_after_update[$request['current_cycle_instalment']]['instalment_cycle_amount'] = $instalment_cycle_amount_after_update/100;
                    $param['payment_details'] = serialize($instalment_cycle_data_after_update);
					xtc_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $datas['tid'] . "'");
				 }
				 //deactivate updated in db
				 if (in_array($datas['payment_id'] ,array('96','97'))){
					 if($refund_params['refund_param'] == 'DEACTIVATE' && $data['tid_status'] == 100){
						 $param['payment_details'] = 'DEACTIVATE';
						 xtc_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $datas['tid'] . "'");
					 }
				 }
                
                // Update the void status
                if ($param['gateway_status'] != 100) { // Process for onhold
                    $order_status_value = MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED;
                    xtc_db_perform(TABLE_ORDERS, array(
                        'orders_status' => $order_status_value
                    ), 'update', 'orders_id="' . $request['oID'] . '"');
                }
                // Update the order status in shop
                updateOrderStatus($request['oID'], $order_status_value, utf8_decode($message));
            }
			$response = http_build_query($data);
            // Redirect to shop
            xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&'.$response.'&oID=' . (int)$request['oID']));
    }
    
     else if (!empty($request['new_amount']) && isset($request['nn_amount_update_confirm'])) { // To process amount update field
			$orderInfo_details = unserialize($datas['payment_details']);
			$due_date = $orderInfo_details['due_date'];
			if (!empty($request['amount_change_year']) && !empty($request['amount_change_month']) && !empty($request['amount_change_day'])) {
				$due_date = $request['amount_change_year'].'-'.$request['amount_change_month'].'-'.$request['amount_change_day'];
			}
            $amount_change_request = array(
                'vendor' 			=> $datas['vendor'],
                'product' 			=> $datas['product'],
                'key' 				=> $datas['payment_id'],
                'tariff' 			=> $datas['tariff_id'],
                'auth_code' 		=> $datas['auth_code'],
                'edit_status' 		=> '1',
                'remote_ip'         => $request['remote_ip'],
                'tid' 				=> $datas['tid'],
                'status' 			=> 100,
                'update_inv_amount' => '1',
                'amount' 			=> $request['new_amount']
            );
            if (in_array($datas['payment_id'], array(27,59)) && !empty($due_date)) { // Due_date added for payment invoice ,prepayment only
                $amount_change_request['due_date'] = date('Y-m-d', strtotime($due_date));
            }
			// Send the request to Novalnet server
            $response = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $amount_change_request);
            parse_str($response, $data);
            if ($data['status'] == 100) { // Amount update success in shop
                $message                 = PHP_EOL . sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE), xtc_format_price_order(($request['new_amount'] / 100), 1, $datas['currency']), date('d.m.Y', strtotime($due_date)), date('H:i:s')) . PHP_EOL;
                $orderInfo               = xtc_db_fetch_array(xtc_db_query("SELECT orders_status from " . TABLE_ORDERS . " where orders_id = " . xtc_db_input($request['oID'])));
                $param                   = array();
                $param =  array (
						'amount'        => $request['new_amount']);
                if ($datas['payment_id'] == 37) { // Allow only sepa payment
					$message                 = PHP_EOL . sprintf(utf8_encode(MODULE_PAYMENT_NOVALNET_SEPA_TRANS_AMOUNT_UPDATED_MESSAGE), xtc_format_price_order(($request['new_amount'] / 100), 1, $datas['currency']), date('d.m.Y', strtotime(date('Y-m-d'))), date('H:i:s')) . PHP_EOL;

					$callback_param['order_amount'] =$callback_param['callback_amount'] = $request['new_amount'];
					xtc_db_perform('novalnet_callback_history', $callback_param, "update", "original_tid='" . $datas['tid'] . "'");
                }
                if (in_array($datas['payment_id'],array(27,59))) { // Allowed only payment id 27 and 59
                    $orderInfo_comments = NovalnetHelper::transactionCommentsForm($datas,$datas['payment_type']);
                    $transaction_comments     = '';
                    if($datas['payment_id'] == 27 ) {
						// To form Novalnet transaction comments
						$novalnetPaymentReference = NovalnetHelper::formInvoicePrepaymentPaymentReference($datas['payment_ref'],$datas['payment_type'], $datas);
						list($transaction_Details, $bank_details) = NovalnetHelper::formInvoicePrepaymentComments(array(
							'invoice_account_holder'=> $orderInfo_details['account_holder'],
							'invoice_bankname'  => $orderInfo_details['bank_name'],
							'invoice_bankplace' => $orderInfo_details['bank_city'],
							'amount' 			=> sprintf("%.2f", ($request['new_amount'] / 100)),
							'currency' 			=> $orderInfo_details['currency'],
							'tid' 				=> $orderInfo_details['tid'],
							'invoice_iban' 		=> $orderInfo_details['bank_iban'],
							'invoice_bic' 		=> $orderInfo_details['bank_bic'],
							'due_date' 			=> $amount_change_request['due_date']
						));
						$transaction_comments .= $orderInfo_comments . $transaction_Details . $novalnetPaymentReference;
						$param['payment_details'] = serialize($bank_details); // To get bank details
						$orderInfo['comments'] = $transaction_comments;
					} else {
						$orderInfo_comments .= MODULE_PAYMENT_NOVALNET_TRANS_SLIP_EXPIRY_DATE.date('d.m.Y',strtotime($amount_change_request['due_date'])).PHP_EOL;
						$orderInfo_comments .= PHP_EOL.MODULE_PAYMENT_NOVALNET_NEAREST_STORE_DETAILS.PHP_EOL;
						$nearest_store =  NovalnetHelper::getNearestStore($orderInfo_details,'nearest_store');
						$cashpayment_slip_details = array_merge($nearest_store,array('due_date'=> $amount_change_request['due_date']));
						$param['payment_details'] = serialize($cashpayment_slip_details);
						$i = 0;
						foreach ($nearest_store as $key => $values){
							$i++;
							$country_name = xtc_db_fetch_array(xtc_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . $nearest_store['nearest_store_country_'.$i] . "'"));
							if(!empty($nearest_store['nearest_store_title_'.$i])) {
								$orderInfo_comments .= PHP_EOL . $nearest_store['nearest_store_title_'.$i].PHP_EOL;
							}
							if (!empty($nearest_store['nearest_store_street_'.$i])) {
								$orderInfo_comments .= $nearest_store['nearest_store_street_'.$i].PHP_EOL;
							}
							if(!empty($nearest_store['nearest_store_city_'.$i])) {
								$orderInfo_comments .= $nearest_store['nearest_store_city_'.$i].PHP_EOL;
							}
							if(!empty($nearest_store['nearest_store_zipcode_'.$i])) {
								$orderInfo_comments .= $nearest_store['nearest_store_zipcode_'.$i].PHP_EOL;
							}
							if(!empty($nearest_store['nearest_store_country_'.$i])) {
								$orderInfo_comments .= $country_name['countries_name'].PHP_EOL;
							}
						}
						$transaction_comments     = $orderInfo['comments'] = $orderInfo_comments;
					}
					$call_amount['order_amount'] = $request['new_amount'];
					xtc_db_perform('novalnet_callback_history',$call_amount , "update", "original_tid='" . $datas['tid'] . "'");
					xtc_db_perform(TABLE_ORDERS, $orderInfo, 'update', 'orders_id="' . $request['oID'] . '"');
				}
                // Transaction details update the shop Novalnet table
                xtc_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $datas['tid'] . "'");
                // Update the order status in shop
                updateOrderStatus($request['oID'], $orderInfo['orders_status'], utf8_decode($message) . $transaction_comments);
            }
            $response = http_build_query($data);
            // Redirect to shop
            xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&'.$response.'&oID=' . (int)$request['oID']));
	} else if (isset($request['nn_manage_confirm']) && !empty($request['trans_status'])) { //  To process on-hold transaction
		// Send the request to Novalnet server
			$response      = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp',  array(
                'vendor' 		=> $datas['vendor'],
                'product' 		=> $datas['product'],
                'key' 			=> $datas['payment_id'],
                'tariff' 		=> $datas['tariff_id'],
                'auth_code' 	=> $datas['auth_code'],
                'edit_status' 	=> '1',
                'remote_ip'     => $request['remote_ip'],
                'tid' 			=> $datas['tid'],
                'status' 		=> $request['trans_status'] //Status 100 or 103
            ));

            parse_str($response, $data);
             if (in_array($datas['payment_id'], array( '96', '97' ))) {
				$initial_instalment_details = xtc_db_fetch_array(xtc_db_query("SELECT payment_details from novalnet_transaction_detail where order_no = " . xtc_db_input($datas['order_no'])));  
				$initial_instalment_details_unserialized = unserialize($initial_instalment_details['payment_details']);
				$instalment_details = $initial_instalment_details_unserialized['0'];
				$instalment_details['next_instalment_date'] = $data['next_instalment_date'];
				$future_instalment_details[] = $instalment_details;
				$future_instalment_dates = explode('|', $data['future_instalment_dates']);
				array_shift($future_instalment_dates);
					foreach ($future_instalment_dates as $key => $mark) {
						$future_instalment_details[] =  explode('-', $mark, 2);
						$future_instalment_details[$key]['next_instalment_date'] == '' ? $future_instalment_details[$key]['next_instalment_date'] = $future_instalment_details[$key]['1'] : $future_instalment_details[$key]['next_instalment_date'] =$future_instalment_details[$key]['next_instalment_date'];
						unset($future_instalment_details[$key]['1']);
					}
					$param['payment_details']  = serialize($future_instalment_details);
				    xtc_db_perform('novalnet_transaction_detail', $param, "update", "order_no='" . $datas['order_no'] . "'");
			}
            
            if ($data['status'] == 100) { // Payment success
                $param = array('gateway_status'  => $data['tid_status']);
				if($datas['payment_type'] == 'novalnet_paypal') {
					$result = xtc_db_query("SHOW TABLES LIKE 'gx_configurations'");
					$gx_config = $result->num_rows; 
					 if($gx_config == '1' ){
				        $shop_click = MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE_CLICK;
				     }else{
				        $shop_click = MODULE_PAYMENT_NOVALNET_PAYPAL_SHOP_TYPE;
				     }
					if(($shop_click == 'ONECLICK' || $shop_click == 'true') && !empty($data['paypal_transaction_id'])) {
						$param['payment_details'] = serialize(array(
											'paypal_transaction_tid'   => $data['paypal_transaction_id'],
											'novalnet_transaction_tid' => $datas['tid']));
					}
					$amout_update['callback_amount'] = $data['tid_status'] == 90 ? 0 : $datas['amount'];
					xtc_db_perform('novalnet_callback_history', $amout_update, "update", "original_tid='" . $datas['tid'] . "'");
				}
				$comments = sprintf(MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE, date('d.m.Y', strtotime(date('d.m.Y'))), date('H:i:s'));
				if (in_array($datas['payment_id'],array('27','41'))) {
					$datas = NovalnetHelper::getNovalnetTransDetails($request['oID']);
					$serialize_data = unserialize($datas['payment_details']);
					$nn_duedate= date('d.m.Y',strtotime($data['due_date']));
					$comments    = sprintf(MODULE_PAYMENT_NOVALNET_INVOICE_ON_HOLD_CONFIRM_TEXT,$datas['tid'],$nn_duedate);
					$serialize_data['due_date']  = $data['due_date'];
					$on_hold_serialize_data      = serialize($serialize_data);
                    $orderInfo_comments .= PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid'] . ((($datas['test_mode'] == 1) || constant('MODULE_PAYMENT_' . strtoupper($datas['payment_type']) . '_TEST_MODE') == 'true') ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE . PHP_EOL : '');
                    $transaction_comments     = '';
					// To form Novalnet transaction comments
					$novalnetPaymentReference = NovalnetHelper::formInvoicePrepaymentPaymentReference($datas['payment_ref'],$datas['payment_type'], $datas);
					list($transaction_Details) = NovalnetHelper::formInvoicePrepaymentComments(array(
						'invoice_account_holder'   => $serialize_data['account_holder'],
						'invoice_bankname'         => $serialize_data['bank_name'],
						'invoice_bankplace'        => $serialize_data['bank_city'],
						'amount' 			       => sprintf("%.2f", ($serialize_data['amount'] / 100)),
						'currency' 			       => $serialize_data['currency'],
						'tid' 				       => $serialize_data['tid'],
						'invoice_iban' 		       => $serialize_data['bank_iban'],
						'invoice_bic' 		       => $serialize_data['bank_bic'],
						'due_date' 			       => $data['due_date']
					));
					$comments .= $orderInfo_comments . $transaction_Details . $novalnetPaymentReference;
					if($request['trans_status'] == '100'){ 
						$order = new order_ORIGIN ($datas['order_no']);
						NovalnetHelper::guarantee_mail(array(
								'comments' => '<br>' . $comments,
								'order_no' => $request['oID'],
								 'order' =>  $order,
						),$datas);
					}
					$param['payment_details']  =  $on_hold_serialize_data;
				}elseif (in_array($datas['payment_id'], array( '96', '97' ))) {
					$initial_instalment_details = xtc_db_fetch_array(xtc_db_query("SELECT currency, payment_details from novalnet_transaction_detail where order_no = " . xtc_db_input($datas['order_no'])));  
					$initial_instalment_details_unserialized['payment_details'] = unserialize($initial_instalment_details['payment_details']);
					$data['instalment_cycle_amount'] = $initial_instalment_details_unserialized['0']['instalment_cycle_amount'];
					$data['currency'] = $initial_instalment_details['currency'];
					if($datas['payment_id'] == 96){
						$invoice_instalment_details = xtc_db_fetch_array(xtc_db_query("SELECT payment_ref from novalnet_transaction_detail where order_no = " . xtc_db_input($datas['order_no']))); 
						$comments .= unserialize($invoice_instalment_details['payment_ref']);
						$data['key'] = '96';
						$data['tid'] = $datas['tid'];
					}
					$comments .= PHP_EOL.NovalnetHelper::formInstalmentPaymentReference($data);
					if($request['trans_status'] == '100'){ 
						 $email = xtc_db_fetch_array(xtc_db_query("SELECT customers_email_address FROM ".TABLE_ORDERS." WHERE orders_id='". xtc_db_input($datas['order_no']) ."'"));
						 $order = new order_ORIGIN ($datas['order_no']);
						NovalnetHelper::instalment_mail(array(
                            'email'    =>  $email['customers_email_address'],
                            'order_no' =>  $datas['order_no'],
                            'comments'  => $comments,
                            'order' =>  $order
                        ));
					}
			    }
                 $order_status            = ($request['trans_status'] == 100) ? constant('MODULE_PAYMENT_' . strtoupper($datas['payment_type']) . '_ORDER_STATUS') : MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED;
                
                 if($datas['payment_id'] == 41 && $request['trans_status'] == 100) {
					 $order_status  = MODULE_PAYMENT_NOVALNET_INVOICE_CALLBACK_ORDER_STATUS;
				 }
                // Transaction details update the shop novalnet table
                xtc_db_perform('novalnet_transaction_detail', $param, "update", "tid='" . $datas['tid'] . "'"); 
                // Update the order status in shop
                xtc_db_perform(TABLE_ORDERS, array(
                    'orders_status' => $order_status
                ), 'update', 'orders_id="' . $request['oID'] . '"');
                ($request['trans_status'] == 100) ? updateOrderStatus($request['oID'], $order_status, PHP_EOL . $comments . PHP_EOL) : updateOrderStatus($request['oID'], $order_status, PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE, date('d.m.Y', strtotime(date('d.m.Y'))), date('H:i:s')) . PHP_EOL);
            }
			$response = http_build_query($data);
            // Redirect to shop
            xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&'.$response.'&oID=' . (int)$request['oID']));
	} else  if (!empty($request['nn_book_confirm']) && !empty($request['book_amount'])) { // To process zero amount booking transaction
		    $urlparams                = unserialize($datas['payment_details']); 
            $urlparams['amount']      = trim($request['book_amount']);
            $urlparams['order_no']    = $request['oID'];
            $urlparams['payment_ref'] = $datas['tid'];
            unset($urlparams['create_payment_ref']);
            if (!empty($urlparams['sepa_due_date'])) { // Assigning Due date param sepa Payment only
				$due_date   = (defined('MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE')) ? MODULE_PAYMENT_NOVALNET_SEPA_PAYMENT_DUE_DATE : 0;
                $sepa_duedate              = ((trim($due_date) != '' && trim($due_date) >= 2 && trim($due_date) <= 14) ? trim($due_date) : ''); 
                if(!empty($sepa_duedate))
                {
                  $urlparams['sepa_due_date'] = (date('Y-m-d', strtotime('+' . $sepa_duedate . ' days')));
			    }else{
					unset($urlparams['sepa_due_date']);
				}
            }
            // Send the request to Novalnet server
            $response = NovalnetHelper::doPaymentCurlCall('https://payport.novalnet.de/paygate.jsp', $urlparams);
            parse_str($response, $data);
            if ($data['status'] == 100 ) { // Zero amount booking process success
                $orderInfo = xtc_db_fetch_array(xtc_db_query("SELECT orders_status from " . TABLE_ORDERS . " where orders_id = " . xtc_db_input($request['oID'])));
                $test_mode_msg = (isset($data['test_mode']) && $data['test_mode'] == 1) ? PHP_EOL . MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE . PHP_EOL : '';
                $message = PHP_EOL . MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $data['tid'] . $test_mode_msg;
				$message .=  PHP_EOL . sprintf(MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE, xtc_format_price_order(($request['book_amount'] / 100), 1, $datas['currency']), $data['tid']) . PHP_EOL;
                $param['tid'] = $data['tid'];
                
                $callback_param['original_tid'] = $data['tid'];
                $callback_param['callback_amount']= ( $data['status'] == 100 ) ? $urlparams['amount'] : 0 ;
                $callback_param['order_amount'] = $urlparams['amount'];
                xtc_db_perform('novalnet_callback_history', $callback_param, "update", "order_no='" . xtc_db_input($request['oID']) . "'");
                $param =  array(
								'amount'          => $urlparams['amount'],
								'gateway_status'  => $data['tid_status'],
								'tid'  		      => $data['tid'],
				);
				updateOrderStatus($request['oID'], $orderInfo['orders_status'], $message);
				// Transaction details update the shop Novalnet table
                xtc_db_perform('novalnet_transaction_detail', $param, "update", "order_no='" . xtc_db_input($request['oID']) . "'");
            }
			$response = http_build_query($data);
            // Redirect to shop
            xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'action=edit&'.$response.'&oID=' . (int)$request['oID']));
	} 

    /**
     * Update order status in the shop
     *
     * @param integer $order_id
     * @param string $orders_status_id
     * @param string $message
     */
    function updateOrderStatus($order_id, $orders_status_id, $message) {
			xtc_db_perform(TABLE_ORDERS, array(
            'orders_status' => $orders_status_id,
            'comments' 		=> $message
			), "update", "orders_id='$order_id'");
            xtc_db_query("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . " SET orders_id =  '$order_id',orders_status_id = '$orders_status_id', date_added = NOW(), customer_notified = '1', comments = '$message'");
    }
