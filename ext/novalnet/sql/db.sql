CREATE TABLE IF NOT EXISTS novalnet_callback_history (
  id int(10) unsigned AUTO_INCREMENT COMMENT 'Auto increment',
  callback_datetime datetime COMMENT 'Callback execution date and time',
  payment_type varchar(40) COMMENT 'Callback payment type',
  original_tid bigint(20) unsigned COMMENT 'Original transaction id',
  callback_tid bigint(20) unsigned COMMENT 'Callback reference transaction id',
  order_amount int(10) unsigned COMMENT 'Order amount in minimum unit of currency',
  callback_amount int(10) unsigned COMMENT 'Callback amount in minimum unit of currency',
  order_no int(10) unsigned COMMENT 'Order no from shop',
  PRIMARY KEY (id),
  KEY order_no (order_no),
  KEY original_tid (original_tid)
) COMMENT='Novalnet callback history';
CREATE TABLE IF NOT EXISTS novalnet_subscription_detail (
  id int(10) unsigned AUTO_INCREMENT COMMENT 'Auto increment',
  order_no int(10) unsigned COMMENT 'Order no from shop',
  subs_id int(10) unsigned COMMENT 'Subscription id',
  tid bigint(20) unsigned COMMENT 'Transaction id',
  signup_date datetime COMMENT 'Subscription signup date and time',
  termination_reason varchar(50) COMMENT 'Subscription termination reason',
  termination_at datetime COMMENT 'Subscription terminated date and time',
  PRIMARY KEY (id),
  KEY order_no (order_no),
  KEY tid (tid)
) COMMENT='Novalnet subscription transaction history';

CREATE TABLE IF NOT EXISTS novalnet_transaction_detail (
  id int(10) unsigned AUTO_INCREMENT COMMENT 'Auto increment',
  tid bigint(20) unsigned COMMENT 'Transaction id',
  vendor int(10) unsigned COMMENT 'Vendor id',
  product int(10) unsigned COMMENT 'Product id',
  auth_code varchar(40) COMMENT 'Vendor Authcode',
  tariff_id int(10) unsigned COMMENT 'Tariff id',
  subs_id int(10) unsigned COMMENT 'Subscription id',
  payment_id int(10) unsigned COMMENT 'Payment key',
  payment_type varchar(40) COMMENT 'Payment type',
  amount int(10) unsigned COMMENT 'Amount',
  currency char(10) COMMENT 'Currency',
  gateway_status int(10) unsigned COMMENT 'Transaction status',
  test_mode enum('0','1') COMMENT 'Test mode status',
  customer_id int(10) unsigned COMMENT 'Customer no from shop',
  order_no int(10) unsigned COMMENT 'Order no from shop',
  novalnet_order_date datetime COMMENT 'Transaction date',
  process_key varchar(100) COMMENT 'Process key',
  reference_transaction enum('0','1') COMMENT 'Reference order notification',
  payment_ref text COMMENT 'Payment reference for Invoice/Prepayment',
  next_payment_date datetime COMMENT 'Subscription next cycle date',
  payment_details text COMMENT 'Masked account/card details of customer',
  refund_amount int(11) COMMENT 'Refund amount',
  PRIMARY KEY (id),
  KEY tid (tid),
  KEY payment_type (payment_type),
  KEY order_no (order_no)
) COMMENT='Novalnet transaction history';

CREATE TABLE IF NOT EXISTS  novalnet_aff_account_detail (
  id int(10)unsigned AUTO_INCREMENT COMMENT 'Auto increment',
  vendor_id int(10) unsigned COMMENT 'Vendor id',
  vendor_authcode varchar(40) COMMENT 'Vendor authcode' ,
  product_id int(10) unsigned COMMENT 'Product id',
  product_url varchar(60) COMMENT 'Product URl',
  activation_date datetime COMMENT 'Affiliate activation date',
  aff_id int(10) unsigned COMMENT 'Affiliate id',
  aff_authcode varchar(40) COMMENT 'Affiliate authcode',
  aff_accesskey varchar(40)COMMENT 'Affiliate access key',
  PRIMARY KEY (id),
  KEY vendor_id (vendor_id),
  KEY aff_id (aff_id)
) COMMENT='Novalnet merchant / affiliate account information';

CREATE TABLE IF NOT EXISTS  novalnet_aff_user_detail (
  id int(10) unsigned AUTO_INCREMENT COMMENT 'Auto increment',
  aff_id int(10) unsigned COMMENT 'Affiliate id',
  customer_id int(10)unsigned COMMENT 'Customer no from shop',
  aff_order_no int(10)unsigned COMMENT 'Affiliate order no',
  PRIMARY KEY (id),
  KEY customer_id (customer_id)
) COMMENT='Novalnet affiliate customer account information';
CREATE TABLE IF NOT EXISTS novalnet_version_detail (
  version varchar(10) COMMENT 'Novalnet payment module current version',
  KEY version (version)
) COMMENT='Novalnet version information';
INSERT INTO novalnet_version_detail VALUES ('11.2.0');
