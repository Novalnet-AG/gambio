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
 * Script : novalnet_extension.php
 *
 */
require_once(DIR_FS_CATALOG . 'ext/novalnet/NovalnetHelper.class.php');
include_once(DIR_FS_LANGUAGES . $_SESSION['language']."/modules/payment/novalnet.php");
/**
 * To append novalnet extension features in admin
 *
 * @param integer $order_id
 */
function appendNovalnetOrderProcess($order_id) {
$request = $_REQUEST;
$message = NovalnetHelper::getServerResponse($request);
			
$class_message = ($request['status'] == 100 ) ? "success" : "danger " ; 
		if(!empty($request['status_desc']) || !empty($request['status_text'])) {?>
		<div class ="message_stack_container hidden breakpoint-large">
			<div class = "alert alert-<?php echo $class_message ?>">
			<?php echo $message; // To display error message
			?><button type="button" class="close" data-dismiss="alert">×</button>
			</div>
		</div>
<?php
	}
	$datas = NovalnetHelper::getNovalnetTransDetails($order_id); // To get transaction details from database
	$callback_info = xtc_db_fetch_array(xtc_db_query("SELECT sum(callback_amount) AS callback_total_amount, order_amount FROM novalnet_callback_history WHERE original_tid = '" . xtc_db_input($datas['tid']) . "'ORDER BY id DESC LIMIT 1"));
	$order_total = xtc_db_fetch_array(xtc_db_query("SELECT value FROM " . TABLE_ORDERS_TOTAL . " where class = 'ot_total' AND orders_id = " . xtc_db_input($order_id)));
	if (isset($datas) && $datas['amount'] == 0 && in_array($datas['payment_id'],array(6,37,34))  && $datas['gateway_status'] != 103) { // Zero amount booking transaction process
?>
<!-- zero amount booking block -->
<div class="content article-table grid">
	<div class="span12 remove-padding">
		<div class ="frame-wrapper">
			<div class="frame-head">
					<label class="title" ><?php echo MODULE_PAYMENT_NOVALNET_BOOK_TITLE; ?></label>
			</div>
				<?php echo xtc_draw_form('novalnet_book_amount', 'novalnet_extension_helper.php');  ?>
				<label style="margin:0% 0% 0% 1%"><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></label>
				<?php echo xtc_draw_hidden_field('oID', $request['oID']);?>
				<table style="width=31%">
					<tr>
						<td>
							<?php
								$amount = $order_total['value']*100;
								echo MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE; 
								echo xtc_draw_input_field('book_amount',$amount,'id="book_amount" autocomplete="off" style="margin:0% 0% 0% 2%"'); echo MODULE_PAYMENT_NOVALNET_AMOUNT_EX;
							?>
						</td>
					</tr><br><span id="nn_zero_amount_error" style="color:red"></span>
					<tr>
						<td>
							<?php 							
							echo xtc_draw_input_field('nn_book_confirm',html_entity_decode(MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT), 'onclick="return zero_amount_validationt();" style="float:left"',false,'submit');
							echo "<a class='button' style='float:left' href='" . xtc_href_link('orders.php') . "'>" . MODULE_PAYMENT_NOVALNET_BACK_TEXT . "</a>";							
							?>
						</td>		
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<?php
	}

	if (in_array($datas['gateway_status'], array(98,99,91,85)) && in_array($datas['payment_id'], array('6','27','34','37','40','41','96','97')))  { // To process on-hold transaction
?>
<!--  Transaction management block  -->
<div class="content article-table grid">
	<div class="span12 remove-padding">
		<div class ="frame-wrapper">
			<div class="frame-head">
					<label class="title"><?php echo MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_TITLE; ?></label>
			</div>
				<?php echo xtc_draw_form('novalnet_status_change', 'novalnet_extension_helper.php');  ?>
					<br><label style='margin:0% 0% 0% 1%'> <?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></label>
				<?php echo xtc_draw_hidden_field('oID', $request['oID']);
				echo xtc_draw_hidden_field('nn_capture_update', MODULE_PAYMENT_NOVALNET_PAYMENT_CAPTURE_CONFIRM);
				echo xtc_draw_hidden_field('nn_void_update', MODULE_PAYMENT_NOVALNET_PAYMENT_VOID_CONFIRM);?>
				<table style="width:33%">
					<tr>
						<td>
							<?php echo MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT; ?>
						</td>
						<td>
							<select name="trans_status" id='trans_status' onclick="return remove_void_capture_error_message()">
								<option value=''><?php echo MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION; ?></option>
								<option value='100'><?php echo MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT; ?></option>
								<option value='103'><?php echo MODULE_PAYMENT_NOVALNET_CANCEL_TEXT; ?></option>
							</select>
						</td><br><span id="nn_void_capture_error" style="color:red"></span>
					</tr>
					<tr>
						<td>
							<?php 							
							echo xtc_draw_input_field('nn_manage_confirm',html_entity_decode(MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT), 'onclick="return void_capture_status();" style="float:left"',false,'submit');
							echo "<a class='button' style='float:left' href='" . xtc_href_link('orders.php') . "'>" . MODULE_PAYMENT_NOVALNET_BACK_TEXT . "</a>";							
							?>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<?php
	}
		
	if (isset($datas) && $datas['amount'] != 0 && $datas['gateway_status'] == 100 && !in_array($datas['payment_id'],array(96,97))) {
?>
<!-- Refund block -->
<div class="content article-table grid">
	<div class="span12 remove-padding">
		<div class ="frame-wrapper">
			<div class="frame-head">
				<label class="title" ><?php echo MODULE_PAYMENT_NOVALNET_REFUND_TITLE; ?></label>
			</div>
				<?php echo xtc_draw_form('novalnet_trans_refund', 'novalnet_extension_helper.php');  ?>
				<label style='margin:0% 0% 0% 1%'> <?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></label>
				<?php echo xtc_draw_hidden_field('oID', $request['oID']);
				echo xtc_draw_hidden_field('nn_refund_amount', MODULE_PAYMENT_NOVALNET_PAYMENT_REFUND_CONFIRM);?><br><span id="nn_refund_error" style="color:red"></span>
				<table style="width:100%" >
					<?php
						$order_date = strtotime(date("Y-m-d",strtotime($datas['novalnet_order_date'])));
						if ( strtotime(date('Y-m-d')) > $order_date ) { // date comparasion ?>
					<tr>
						<td>
							<?php echo MODULE_PAYMENT_NOVALNET_REFUND_REFERENCE_TEXT; ?>
							<?php echo xtc_draw_input_field('refund_ref','','id="refund_ref" autocomplete="off" style="width:174px;margin:0 0 0 2%;"');?>
						</td>
					</tr>
					<?php } ?>
				</table>
				<table style="width=31%">
					<tr>
						<td>
							<?php echo MODULE_PAYMENT_NOVALNET_REFUND_AMT_TITLE;?>
							<?php echo xtc_draw_input_field('refund_trans_amount',$datas['amount'],'id="refund_trans_amount"  style="width:100px;margin:0 0 0 2%" autocomplete="off"'); echo MODULE_PAYMENT_NOVALNET_AMOUNT_EX;?>
						</td>
					</tr>
					<tr>
						<td>
							<?php 							
							echo xtc_draw_input_field('nn_refund_confirm',html_entity_decode(MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT), 'onclick="return refund_amount_validation();" style="float:left"',false,'submit');
							echo "<a class='button' style='float:left' href='" . xtc_href_link('orders.php') . "'>" . MODULE_PAYMENT_NOVALNET_BACK_TEXT . "</a>";							
							?>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<?php
	}  // instalment table
		if (isset($datas) && in_array($datas['payment_id'],array(96,97)) && $datas['gateway_status'] == 100 && ($datas['payment_details'] != 'DEACTIVATE')) {
			$instalment_details = unserialize($datas['payment_details']);
?>
<div class="content article-table grid">
	<div class="span12 remove-padding">
		<script>
		function myFunction(cycle) {
		  var refund_id = document.getElementById("instalment_refund_"+cycle);
		  if (refund_id.style.display === "none") {
			refund_id.style.display = "block";
		  } else {
			refund_id.style.display = "none";
		  }
		}
		</script>
		<div class ="frame-wrapper">
			<div class="frame-head">
				<label class="title" ><?php echo 'Instalment Summary'; ?></label>
			</div>
			<!-- instalment cancel button -->
				<br><div align="right">
				<?php echo xtc_draw_form('nn_instalment_cancel', 'novalnet_extension_helper.php'); 
				      echo xtc_draw_hidden_field('oID', $request['oID']); 
					  echo xtc_draw_input_field('nn_instalment_cancel',html_entity_decode(MODULE_PAYMENT_NOVALNET_INSTALMENT_CANCEL_ADMIN_TEXT), 'onclick="return refund_amount_validation();" style="float:left"',false,'submit');							
							?>
			</form>
			</div>


	<?php
	echo "<table><tr><td>S.No</td><td>Amount</td><td>Paid date</td><td>Next Instalment Date</td><td>Status</td><td>Reference</td></tr>";
	$sno = 1;
	foreach ($instalment_details as $key => $instalment_details_data){
		$status = $instalment_details_data['reference_tid'] == '' ? "<span id='status_color_1' style='color: red;'>Pending</span>": "<span id='status_color_1' style='color: green;'>Paid</span>";
		$href = $instalment_details_data['reference_tid'] != '' ? "<button id='nn_refund'  onclick='myFunction($key)'>Refund</button>" : '';
		$instalment_amount = !empty($instalment_details_data['instalment_cycle_amount']) ? xtc_format_price_order($instalment_details_data['instalment_cycle_amount'], 1, $datas['currency']) : '';
		echo "<tr><td>".$sno++."</td><td>".$instalment_amount.' '.$href."</td>
		<td>".$instalment_details_data['paid_date']."</td><td>".$instalment_details_data['next_instalment_date']."</td><td>$status</td><td>".$instalment_details_data['reference_tid']."</td><td>".    
		  xtc_draw_form('nn_refund_confirm', 'novalnet_extension_helper.php');  
		   echo '<div id= instalment_refund_'.$key.' style="display: none;">' ;
		   echo xtc_draw_hidden_field('oID', $request['oID']); 
		   echo xtc_draw_hidden_field('tid', $instalment_details_data['reference_tid']); 
		   echo xtc_draw_hidden_field('current_cycle_instalment', ''.$key.''); 
		   echo xtc_draw_input_field('refund_trans_amount',$instalment_details_data['instalment_cycle_amount']*100,'id="refund_trans_amount"  style="width:100px;margin:0 0 0 2%" autocomplete="off"');
           echo xtc_draw_input_field('nn_refund_confirm',html_entity_decode(MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT), 'onclick="return refund_amount_validation();" style="float:left"',false,'submit');
		   echo "<a class='button' style='float:left' href='" . xtc_href_link('orders.php?oID='.$request['oID'].'&action=edit') . "'>" . MODULE_PAYMENT_NOVALNET_INSTALMENT_CANCEL_TEXT . "</a>"; ?>
		   </div></form></td></tr><?php } echo "</table>"; ?>
		</div>
	</div>
</div>
<?php
	}
	if(($datas['payment_id'] == 37 && $datas['gateway_status'] == 99) || (in_array($datas['payment_id'],array(27,59)) && $datas['gateway_status'] == 100 && $callback_info['callback_total_amount'] <  $callback_info['order_amount'])) {
		$orderInfo = unserialize($datas['payment_details']);
?>
	<!-- Amount update block -->
<div class="content article-table grid">
	<div class="span12 remove-padding">
		<div class ="frame-wrapper">
			<div class="frame-head">
				<label class="title" ><?php 
				echo ($datas['payment_id'] == 37) ? MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE : (($datas['payment_id'] == 27) ? MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_TITLE : MODULE_PAYMENT_NOVALNET_SLIP_DATE_CHANGE_TITLE); ?></label>
			</div>
				<?php echo xtc_draw_form('novalnet_amount_change', 'novalnet_extension_helper.php');
				echo xtc_draw_hidden_field('nn_root_amount_update', DIR_WS_CATALOG);
				echo xtc_draw_hidden_field('novalnet_amount_update_value', 1);
				echo xtc_draw_hidden_field('oID', $request['oID']);				
				?>
				<table>
					<label style='margin:0% 0% 0% 1%'><?php echo MODULE_PAYMENT_NOVALNET_TRANSACTION_ID . $datas['tid']; ?></label>
					<tr>
						<td>
							<?php echo MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TITLE; ?>
						</td>
						<td>
							<?php echo xtc_draw_input_field('new_amount',$datas['amount'],'id="new_amount"  style="margin:0 0 0 -9%" autocomplete="off"'); echo MODULE_PAYMENT_NOVALNET_AMOUNT_EX;?>
						</td>
					</tr>
					<?php
						$invoice_prepayment_payment = 0;
						if (in_array($datas['payment_id'],array(27,59))) {
							$input_day = $input_month = $input_year = '';
							if ($orderInfo['due_date'] != '0000-00-00') {
								$input_date = strtotime($orderInfo['due_date']);
								$input_day = date('d',$input_date);
								$input_month = date('m',$input_date);
								$input_year = date('Y',$input_date);
							}
							$invoice_prepayment_payment = 1;
						?>
					<tr>
						<td>
							<?php echo $datas['payment_id'] == 59 ? MODULE_PAYMENT_NOVALNET_TRANS_SLIP_EXPIRY_DATE :MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_TITLE; ?>
						</td>
						<td>
							<select style='margin:0% 0% 0% -9%' name='amount_change_day' id='amount_change_day'>
								<?php for ($i = 1; $i <= 31; $i++) { ?>
									<option <?php echo (($input_day == $i)?'selected':''); ?> value="<?php echo (($i < 10)?'0'.$i:$i); ?>"><?php echo (($i < 10)?'0'.$i:$i); ?></option>
								<?php } ?>
							</select>
							<select name='amount_change_month' id='amount_change_month'>
								<?php for($i = 1; $i <= 12; $i++) { ?>
									<option <?php echo (($input_month == $i)?'selected':''); ?> value="<?php echo (($i < 10)?'0'.$i:$i); ?>"><?php echo (($i < 10)?'0'.$i:$i); ?></option>
								<?php } ?>
							</select>
							<select name='amount_change_year' id='amount_change_year'>
								<?php $year_val = date('Y');
									for($i = $year_val; $i <= ($year_val+1); $i++) { ?>
								<option <?php echo (($input_year == $i)?'selected':''); ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
								<?php } ?>
							</select>
						</td>
						</tr>
						<?php } ?><br><span id="nn_amount_update_error" style="color:red"></span>
						<?php $date_change = $datas['payment_id'] == 59 ? MODULE_PAYMENT_NOVALNET_ORDER_AMT_SLIP_DATE_UPDATE_TEXT : MODULE_PAYMENT_NOVALNET_ORDER_AMT_DATE_UPDATE_TEXT ;
						 echo xtc_draw_hidden_field('invoice_payment_due_date', $orderInfo['due_date']);
							  echo xtc_draw_hidden_field('invoice_payment', $invoice_prepayment_payment);
							  echo xtc_draw_hidden_field('nn_duedate_update', $date_change);
						      echo xtc_draw_hidden_field('nn_order_amount_update', MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT);?>
						<tr>
							<td>
							<?php
								echo xtc_draw_input_field('nn_amount_update_confirm',html_entity_decode(MODULE_PAYMENT_NOVALNET_UPDATE_TEXT), 'onclick="return validate_amount_update();" style="float:left"',false,'submit');
								echo "<a class='button' style='float:left' href='" . xtc_href_link('orders.php') . "'>" . MODULE_PAYMENT_NOVALNET_BACK_TEXT . "</a>";							
							?>
							</td>
						</tr>
						
				</table>
				
			</form>
		</div>
	</div>
</div>
	<?php
 }
}
	echo '<script type="text/javascript" src="' . DIR_WS_ADMIN . 'html/assets/javascript/modules/novalnet/novalnet_extension.js"></script>';
	echo xtc_draw_hidden_field('nn_valid_account', MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR);
	echo xtc_draw_hidden_field('nn_amount_error', MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE);
	echo xtc_draw_hidden_field('nn_duedate_feature_error', MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE);
	echo xtc_draw_hidden_field('nn_duedate_error', MODULE_PAYMENT_DUE_DATE_INVAILD);
	echo xtc_draw_hidden_field('nn_refund_amount_confirm', MODULE_PAYMENT_NOVALNET_PAYMENT_REFUND_CONFIRM);	
	echo xtc_draw_hidden_field('nn_zero_amount_book_confirm', MODULE_PAYMENT_NOVALNET_PAYMENT_ZERO_AMOUNT_BOOK_CONFIRM);	
	echo xtc_draw_hidden_field('nn_select_status', MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT);
	
?>
