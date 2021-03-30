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
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_TITLE', 'Kreditkarte');
define('MODULE_PAYMENT_NOVALNET_REDIRECT_DESC','Nach der erfolgreichen &Uuml;berpr&uuml;fung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen.<br>Bitte schlie&szlig;en Sie den Browser nach der erfolgreichen Zahlung nicht, bis Sie zum Shop zur&uuml;ckgeleitet wurden.');
define('MODULE_PAYMENT_NOVALNET_CC_TEXT_DESCRIPTION','Der Betrag wird von Ihrer Kreditkarte abgebucht, sobald die Bestellung abgeschickt wird');
define('MODULE_PAYMENT_NOVALNET_CC_REDIRECTION_TEXT_DESCRIPTION','Nach der erfolgreichen &Uuml;berpr&uuml;fung werden Sie auf die abgesicherte Novalnet-Bestellseite umgeleitet, um die Zahlung fortzusetzen.<br>Bitte schlie&szlig;en Sie den Browser nach der erfolgreichen Zahlung nicht, bis Sie zum Shop zur&uuml;ckgeleitet wurden. ');
define('MODULE_PAYMENT_NOVALNET_CC_ALLOWED_TITLE','Zugelassene Gebiete');
define('MODULE_PAYMENT_NOVALNET_CC_ALLOWED_DESC','Diese Zahlungsart wird nur f&uuml;r die aufgef&uuml;hrten Gebiete zugelassen. Geben Sie die Gebiete in folgendem Format ein, z.B. DE, AT, CH etc. Falls das Feld leer ist, sind alle Gebiete zugelassen.');
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_TITLE','Zahlungsart aktivieren');
define('MODULE_PAYMENT_NOVALNET_CC_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_TITLE','Testmodus aktivieren');
define('MODULE_PAYMENT_NOVALNET_CC_TEST_MODE_DESC','Die Zahlung wird im Testmodus durchgef&uuml;hrt, daher wird der Betrag f&uuml;r diese Transaktion nicht eingezogen');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_TITLE','3D-Secure aktivieren');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_DESC','3D-Secure wird f&uuml;r Kreditkarten aktiviert. Die kartenausgebende Bank fragt vom K&auml;ufer ein Passwort ab, welches helfen soll, betr&uuml;gerische Zahlungen zu verhindern. Dies kann von der kartenausgebenden Bank als Beweis verwendet werden, dass der K&auml;ufer tats&auml;chlich der Inhaber der Kreditkarte ist. Damit soll das Risiko von Chargebacks verringert werden');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE_TITLE','3D-Secure-Zahlungen unter vorgegebenen Bedingungen durchführen');
define('MODULE_PAYMENT_NOVALNET_CC_3D_SECURE_FORCE_DESC','Wenn 3D-Secure in dem darüberliegenden Feld nicht aktiviert ist, sollen 3D-Secure-Zahlungen nach den Einstellungen zum Modul im Novalnet Admin-Portal unter "3D-Secure-Zahlungen durchführen (gemäß vordefinierten Filtern und Einstellungen)" durchgeführt werden. Wenn die vordefinierten Filter und Einstellungen des Moduls "3D-Secure durchführen" zutreffen, wird die Transaktion als 3D-Secure-Transaktion durchgeführt, ansonsten als Nicht-3D-Secure-Transaktion. Beachten Sie bitte, dass das Modul "3D-Secure-Zahlungen durchführen (gemäß vordefinierten Filtern und Einstellungen)" im Novalnet Admin-Portal konfiguriert sein muss, bevor es hier aktiviert wird. Für weitere Informationen sehen Sie sich bitte die Beschreibung dieses Betrugsprüfungsmoduls an (unter dem Reiter "Betrugsprüfungsmodule" unterhalb des Menüpunkts "Projekte" für das ausgewähte Projekt im Novalnet Admin-Portal) oder kontaktieren Sie das Novalnet-Support-Team.');
define('MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT_TITLE', 'Mindesttransaktionsbetrag für die Autorisierung(in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_CC_MANUAL_CHECK_LIMIT_DESC', 'Übersteigt der Bestellbetrag das genannte Limit, wird die Transaktion, bis zu ihrer Bestätigung durch Sie, auf on hold gesetzt. Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.');
define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_TITLE','AMEX-Logo anzeigen');
define('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT_DESC','AMEX-Logo auf der Checkout-Seite anzeigen');
define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_TITLE','Maestro-Logo anzeigen');
define('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT_DESC','Maestro-Logo auf der Checkout-Seite anzeigen');
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_TITLE','Einkaufstyp');
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_DESC','Einkaufstyp ausw&auml;hlen ');
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_TITLE','Mindestwarenwert (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_CC_VISIBILITY_BY_AMOUNT_DESC','Geben Sie den Mindestwarenwert ein, von dem ab die Zahlungsart f&uuml;r den Kunden beim Checkout angezeigt wird');
define('MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO_TITLE','Benachrichtigung des K&auml;ufers');
define('MODULE_PAYMENT_NOVALNET_CC_ENDCUSTOMER_INFO_DESC','Der eingegebene Text wird auf der Checkout-Seite angezeigt');
define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_TITLE','Geben Sie eine Sortierreihenfolge an');
define('MODULE_PAYMENT_NOVALNET_CC_SORT_ORDER_DESC','Diese Zahlungsart wird unter anderen Zahlungsarten (in aufsteigender Richtung) anhand der angegebenen Nummer f&uuml;r die Sortierung eingeordnet');
define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_TITLE','Abschluss-Status der Bestellung');
define('MODULE_PAYMENT_NOVALNET_CC_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_TITLE','Zahlungsgebiet');
define('MODULE_PAYMENT_NOVALNET_CC_PAYMENT_ZONE_DESC','Diese Zahlungsart wird f&uuml;r die angegebenen Gebiete angezeigt');
define('MODULE_PAYMENT_NOVALNET_CC_ONE_CLICK','Kauf mit einem Klick');
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT','Transaktionen mit Betrag 0');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_TYPE','Kreditkartentyp');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_HOLDER','Name des Karteninhabers');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_NO','Kreditkartennummer');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_VALID_DATE','Ablaufdatum');
define('MODULE_PAYMENT_NOVALNET_CC_FORM_CARD_CVC','CVC/CVV/CID');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_HINT','Was ist das?');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_ERROR','Ihre Kreditkartendaten sind ungültig');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_HOLDER_INPUT_TEXT','Name auf der Kreditkarte');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_NUMBER_INPUT_TEXT','XXXX XXXX XXXX XXXX');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_EXPIRYDATE_INPUT_TEXT','MM / YYYY');
define('MODULE_PAYMENT_NOVALNET_CC_IFRAME_CVC_INPUT_TEXT','XXX');
define('MODULE_PAYMENT_NOVALNET_VALID_CC_DETAILS', 'Ihre Kreditkartendaten sind ung&uuml;ltig.');
define('MODULE_PAYMENT_NOVALNET_CC_NEW_ACCOUNT', 'Neue Kartendaten eingeben');
define('MODULE_PAYMENT_NOVALNET_CC_GIVEN_ACCOUNT','Eingegebene Kartendaten');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_TITLE','<h2>CSS-Einstellungen für den iFrame mit Kreditkartendaten</h2><span style="font-weight:normal;">Beschriftung</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT_TITLE','<span style="font-weight:normal;">Eingabe</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_STANDARD_STYLE_INPUT_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT_TITLE','<span style="font-weight:normal;">Text für das CSS</span>');
define('MODULE_PAYMENT_NOVALNET_CC_CSS_TEXT_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_PUBLIC_TITLE', 'Kreditkarte '. (((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'false') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'false')) ?'<a href="https://www.novalnet.de" target="_blank"/><img title="Kreditkarte" alt="Kreditkarte" src="'.DIR_WS_ICONS.'payment/novalnet_cc.png" height="30px"></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'true') &&  ((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'false')) ?'<a href="https://www.novalnet.de" target="_blank"/><img title="Kreditkarte" alt="Kreditkarte" src="'.DIR_WS_ICONS.'payment/novalnet_visa_master_amex.png" height="30px"></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'true') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'false')) ?'<a href="https://www.novalnet.de" target="_blank"/><img title="Kreditkarte" alt="Kreditkarte" src="'.DIR_WS_ICONS.'payment/novalnet_visa_master_maestro.png" height="30px"></a>':'').(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') && (((!defined('MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_MAESTRO_ACCEPT == 'true') && ((!defined('MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT')) || MODULE_PAYMENT_NOVALNET_CC_AMEX_ACCEPT == 'true'))?'<a href="https://www.novalnet.de" target="_blank"/><img title="Kreditkarte" alt="Kreditkarte" src="'.DIR_WS_ICONS.'payment/novalnet_visa_master_amex_maestro.png" height="30px"></a>':''));
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK_TITLE','Einkaufstyp');
define('MODULE_PAYMENT_NOVALNET_CC_SHOP_TYPE_CLICK_DESC','Kauf mit einem Klick');
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT_TITLE','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_CC_ZERO_AMOUNT_DESC','Transaktionen mit Betrag 0');
