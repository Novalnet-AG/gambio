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
 * Script : novalnet_config.php
 *
 */
require_once(DIR_FS_CATALOG . 'ext/novalnet/NovalnetHelper.class.php');
class novalnet_config {
	var $code,$title,$description,$sort_order,$enabled;

	/**
     * Core Function : Constructor()
     *
     */
    public function __construct() {
        $this->code        = 'novalnet_config';
        $this->title       = defined('MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_TITLE') ? MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_TITLE : '';
        $this->description = defined('MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_DESCRIPTION') ? MODULE_PAYMENT_NOVALNET_CONFIG_TEXT_DESCRIPTION : '';
		$this->sort_order  = 0;
        $this->enabled     = false;
    }

    /**
     * Checks for payment installation status
     *
     * @return boolean
     */
    function check() {
		$result = xtc_db_query("SHOW TABLES LIKE 'gx_configurations'");
        $gx_config = $result->num_rows; 
        $_SESSION['GX'] = $gx_config;
        if($_SESSION['GX'] == '1'){
			  $check_query  = xtc_db_query("select `value` from `gx_configurations` where `key` = 'configuration/MODULE_PAYMENT_NOVALNET_PUBLIC_KEY'");
              $this->_check = xtc_db_num_rows($check_query);
		    }else{
               if (!isset($this->_check)) {
				$check_query  = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOVALNET_PUBLIC_KEY'");
                $this->_check = xtc_db_num_rows($check_query);
            }
	    }
        return $this->_check;
    }

    /**
     * Payment module installation
     *
     * @return boolean
     */
    function install() {
		$novalnet_tmp_status_id  =  $this->installQuery();
		if($_SESSION['GX'] == '1'){
		 xtc_db_query("insert into `gx_configurations` ( `key`, `value`,  `legacy_group_id`, `sort_order`, `type`, `last_modified`) values 
		('configuration/MODULE_PAYMENT_NOVALNET_PUBLIC_KEY', '', '6', '1', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS', 'NN_CONFIG', '6', '0', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_VENDOR_ID', '', '6', '2', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_AUTHCODE', '', '6', '3', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_PRODUCT_ID', '', '6', '4', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_TARIFF_ID', '', '6', '5', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY', '', '6', '6', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION','false', '6', '9', 'switcher',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY','true', '6', '13', 'switcher',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE', '0',  '6', '14', 'order-status', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED', '0',  '6', '15', 'order-status',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE','false', '6', '21', 'switcher',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND','false', '6', '22', 'switcher',now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO', '', '6', '23','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC', '', '6', '24','', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_PAYMENT_PENDING_STATUS', '". $novalnet_tmp_status_id ."', '6', '26', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED', '', '6', '25', '', now()),
        ('configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_URL', '" . ((defined('ENABLE_SSL_CATALOG') && ENABLE_SSL_CATALOG === true) ? HTTPS_SERVER : HTTP_SERVER . DIR_WS_CATALOG) . 'callback/novalnet/callback.php' . "','6', '26','', now())
		 ");
	 }else{
		xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "(configuration_key, configuration_value, configuration_group_id, sort_order,set_function, use_function, date_added) VALUES
        ('MODULE_PAYMENT_NOVALNET_PUBLIC_KEY', '', '6', '1', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS', 'NN_CONFIG', '6', '0', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_VENDOR_ID', '', '6', '2', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_AUTHCODE', '', '6', '3', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_PRODUCT_ID', '', '6', '4', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_TARIFF_ID', '', '6', '5', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY', '', '6', '6', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION','false', '6', '9', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION\'," .'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION'. ",' ,'',now()),
        ('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY','true', '6', '13', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY\'," .'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY'. ",' ,'',now()),
        ('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE', '0',  '6', '14', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
        ('MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED', '0',  '6', '15', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name',now()),
        ('MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE','false', '6', '21', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE\'," .'MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE'. ",','',now()),
        ('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND','false', '6', '22', 'xtc_mod_select_option(array(\'true\' => MODULE_PAYMENT_NOVALNET_TRUE,\'false\' => MODULE_PAYMENT_NOVALNET_FALSE,),\'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND\'," .'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND'. ",' ,'',now()),
        ('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO', '', '6', '23','','', now()),
        ('MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC', '', '6', '24','','', now()),
        ('MODULE_PAYMENT_NOVALNET_PAYMENT_PENDING_STATUS', '". $novalnet_tmp_status_id ."', '6', '26', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED', '', '6', '25', '', '', now()),
        ('MODULE_PAYMENT_NOVALNET_CALLBACK_URL', '" . ((defined('ENABLE_SSL_CATALOG') && ENABLE_SSL_CATALOG === true) ? HTTPS_SERVER : HTTP_SERVER . DIR_WS_CATALOG) . 'callback/novalnet/callback.php' . "','6', '26','','', now())");
    }
}
    /**
     * Payment module uninstallation
     *
     * @return boolean
     */
    function remove() {
		if($_SESSION['GX'] == '1' ){
			xtc_db_query("delete from `gx_configurations` where `key` in ('" . implode("', '", array_merge($this->keys(),array('configuration/MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED','configuration/MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS','configuration/MODULE_PAYMENT_NOVALNET_PAYMENT_PENDING_STATUS'))) . "')");
		}else{
		   xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", array_merge($this->keys(),array('MODULE_PAYMENT_NOVALNET_CONFIG_ALLOWED','MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS','MODULE_PAYMENT_NOVALNET_PAYMENT_PENDING_STATUS'))) . "')");
	    }
    }

    /**
     * Return keys to display in payment configuration (Backend)
     *
     * @return boolean
     */
    function keys() {
		global $gx_version; // Get gambio version
		if (strpos(MODULE_PAYMENT_INSTALLED, $this->code) !== false && !$_SESSION['novalnet']['api'] && $_GET['module'] == $this->code) {
			$_SESSION['novalnet']['api'] = true;	
			$server_ip = $_SERVER['SERVER_ADDR'];
			$remote_ip = $_SERVER['REMOTE_ADDR'];
			echo '<input type="hidden" id="server_ip" value="' . $server_ip . '" /><input type="hidden" id="remote_ip" value="' . $remote_ip . '" /><input type="hidden" id="nn_api_shoproot" value="' . DIR_WS_CATALOG . '" /><input type="hidden" id="nn_api_config_call" value="1"> <input type="hidden" id="nn_language" value="' . strtoupper($_SESSION['language_code']) . '" /> <script src="' . DIR_WS_CATALOG . 'ext/novalnet/js/novalnet_api.js" type="text/javascript"></script>';
			if($_SESSION['GX'] == '1' ){
			 echo '<input type="hidden" id="gx_configurations" value="1" />';
		 }
		}
		if(!empty($_SESSION['novalnet']['api']) && empty($_REQUEST['action'])) {
			unset($_SESSION['novalnet']['api']);
		}
		if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && isset($_SESSION['flag'])) {
            unset($_SESSION['flag']);
        }
        // Validate the merchant configuration in shop backend    
        if (!$_SESSION['flag']) {
            NovalnetHelper::validateMerchantConfiguration();
        }
		 if($_SESSION['GX'] == '1' ){
			 $alias_menu = array();
		    if($gx_version >= '3.1.1.0' ){
			    $alias_menu = array_merge($alias_menu,array('configuration/MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS'));
		    }
		  $return_key = array_merge(array(
            'configuration/MODULE_PAYMENT_NOVALNET_PUBLIC_KEY',
            'configuration/MODULE_PAYMENT_NOVALNET_VENDOR_ID',
            'configuration/MODULE_PAYMENT_NOVALNET_AUTHCODE',
            'configuration/MODULE_PAYMENT_NOVALNET_PRODUCT_ID',
            'configuration/MODULE_PAYMENT_NOVALNET_TARIFF_ID',
            'configuration/MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY',
            'configuration/MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION',
            'configuration/MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY',
            'configuration/MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE',
            'configuration/MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED',
            'configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE',
            'configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND',
            'configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO',
            'configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC',
            'configuration/MODULE_PAYMENT_NOVALNET_CALLBACK_URL'
         ),$alias_menu);
          $alice_val = 'configuration/MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS';
		}else{
			$alias_menu = array();
		   if($gx_version >= '3.1.1.0' ) {
			   $alias_menu = array_merge($alias_menu,array('MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS'));
		      }
        $return_key = array_merge(array(
            'MODULE_PAYMENT_NOVALNET_PUBLIC_KEY',
            'MODULE_PAYMENT_NOVALNET_VENDOR_ID',
            'MODULE_PAYMENT_NOVALNET_AUTHCODE',
            'MODULE_PAYMENT_NOVALNET_PRODUCT_ID',
            'MODULE_PAYMENT_NOVALNET_TARIFF_ID',
            'MODULE_PAYMENT_NOVALNET_PAYMENT_ACCESS_KEY',
            'MODULE_PAYMENT_NOVALNET_LAST_SUCCESSFULL_PAYMENT_SELECTION',
            'MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY',
            'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_COMPLETE',
            'MODULE_PAYMENT_NOVALNET_ONHOLD_ORDER_CANCELLED',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_TEST_MODE',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_SEND',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_TO',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_MAIL_BCC',
            'MODULE_PAYMENT_NOVALNET_CALLBACK_URL'
        ),$alias_menu);
          $alice_val = 'MODULE_PAYMENT_NOVALNET_CONFIG_ALIAS';
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
     * Installing Novalnet tables
     * 
     * @return string
     */
    function installQuery() {
		$novalnet_check      = xtc_db_query('DESC admin_access');
        $novalet_alter_table = false;
        
        // Check novalnet column in admin access table
        while ($check_column = xtc_db_fetch_array($novalnet_check)) {
            if (in_array($check_column['Field'], array('novalnet_extension_helper','novalnet'))) {
                $novalet_alter_table = true;
                break;
            }
        }
        if (!$novalet_alter_table) {
            xtc_db_query("ALTER TABLE admin_access ADD novalnet int(1) NOT NULL DEFAULT '1',COMMENT='Novalnet Admin page'");
            xtc_db_query("ALTER TABLE admin_access ADD novalnet_extension_helper int(1) NOT NULL DEFAULT '1',COMMENT='Novalnet Admin extension'");
		}
		// Import Novalnet package SQL tables
		$sql_file     = DIR_FS_CATALOG . 'ext/novalnet/sql/db.sql';
		$sql_lines    = file_get_contents($sql_file);
		$sql_linesArr = explode(";", $sql_lines);
		foreach ($sql_linesArr as $sql) {
			if (trim($sql) > '') {
				xtc_db_query($sql);
			}
		}
		// Check wheather novalnet table exist in shop 
        $nn_check      = xtc_db_query('DESC ' . 'novalnet_callback_history');
        $nn_alter_table = false;
        while ($checks_column = xtc_db_fetch_array($nn_check)) {
            if (in_array($checks_column['Field'] ,array('callback_datetime','original_tid','order_amount','callback_amount'))) {
                $nn_alter_table = true;
                break;
            }
        }
        // Alter the novalnet table if already exists in shop
        if (!$nn_alter_table) {
			xtc_db_query('ALTER TABLE novalnet_callback_history CHANGE org_tid original_tid bigint(20) unsigned COMMENT "Original Transaction ID"');
			xtc_db_query('ALTER TABLE novalnet_callback_history CHANGE amount order_amount int(20) COMMENT "Amount in minimum unit of currency. E.g. enter 100 which is equal to 1.00"');
			xtc_db_query('ALTER TABLE novalnet_callback_history CHANGE total_amount callback_amount int(11) COMMENT "Amount in minimum unit of currency. E.g. enter 100 which is equal to 1.00"');
			xtc_db_query('ALTER TABLE novalnet_callback_history CHANGE `date` callback_datetime datetime COMMENT "Callback excute DATE TIME"');
		 }
		return $this->createNovalnetOrderStatus();
    }
        
    /**
     * Create the Novalnet pending status
     *
     * @return int
     */
    function createNovalnetOrderStatus() {
		$languages = xtc_db_query("select * from " . TABLE_LANGUAGES . " order by sort_order");

		$query = xtc_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
		$status = xtc_db_fetch_array($query);

		$status_id = $status['status_id'];

		while($language = xtc_db_fetch_array($languages)) {

			if(file_exists(DIR_FS_LANGUAGES . $language['directory'].'/modules/payment/novalnet.php')) {
				include_once(DIR_FS_LANGUAGES . $language['directory'].'/modules/payment/novalnet.php');
			}
			if(empty($novalnet_temp_status_text)) {
				$novalnet_temp_status_text = 'NN payment pending';
			}
 
			$query = xtc_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = '" . $novalnet_temp_status_text . "' AND language_id='".$language['languages_id']."' limit 1");
			if(xtc_db_num_rows($query) < 1) {
				$status_id = $status['status_id']+1;
				$insert_values = array(
					'orders_status_id' => $status_id,
					'language_id' => $language['languages_id'],
					'orders_status_name' => $novalnet_temp_status_text,
				);
				xtc_db_perform(TABLE_ORDERS_STATUS, $insert_values);
			}
		}
		return ($status_id != '') ? $status_id : DEFAULT_ORDERS_STATUS_ID;
	}
}

