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
 * Script : novalnet_invoice.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEXT_TITLE', 'Ratenzahlung per Rechnung');
define('MODULE_PAYMENT_NOVALNET_INV_PRE_DESC','Nachdem Sie die Bestellung abgeschickt haben, erhalten Sie eine Email mit den Bankdaten, um die Zahlung durchzuf&uuml;hren.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED_TITLE','Zugelassene Gebiete');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOWED_DESC','Diese Zahlungsart wird nur f&uuml;r die aufgef&uuml;hrten Gebiete zugelassen. Geben Sie die Gebiete in folgendem Format ein, z.B. DE, AT, CH etc. Falls das Feld leer ist, sind alle Gebiete zugelassen.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS_TITLE','Zahlungsart aktivieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE_TITLE','Testmodus aktivieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_TEST_MODE_DESC','Die Zahlung wird im Testmodus durchgef&uuml;hrt, daher wird der Betrag f&uuml;r diese Transaktion nicht eingezogen');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO_TITLE','Benachrichtigung des K&auml;ufers');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ENDCUSTOMER_INFO_DESC','Der eingegebene Text wird auf der Checkout-Seite angezeigt');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER_TITLE','Geben Sie eine Sortierreihenfolge an');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_SORT_ORDER_DESC','Diese Zahlungsart wird unter anderen Zahlungsarten (in aufsteigender Richtung) anhand der angegebenen Nummer f&uuml;r die Sortierung eingeordnet');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ORDER_STATUS_TITLE','Abschluss-Status der Bestellung');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PENDING_ORDER_STATUS_TITLE','Bestellstatus der ausstehenden Zahlung');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PENDING_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE_TITLE','Zahlungsgebiet');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PAYMENT_ZONE_DESC','Diese Zahlungsart wird f&uuml;r die angegebenen Gebiete angezeigt');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE_TITLE','<h3>Voraussetzungen für die Ratenzahlung</h3>Zugelassene Staaten: AT, DE, CH <br>Zugelassene Währung: EUR <br> Mindestbetrag der Bestellung >= 19,98 EUR <br> Bitte beachten Sie, dass der Betrag einer Rate mindestens 9,99 EUR betragen muss und Raten, die diese Kriterien nicht erfüllen, nicht im Ratenplan angezeigt werden. <br>Mindestalter des Endkunden >= 18 Jahre <br> Rechnungsadresse und Lieferadresse müssen übereinstimmen');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_BLOCK_TITLE','<b>Kauf auf Rechnung Konfiguration</b>');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_DUE_DATE_ERROR','Geben Sie bitte ein g&uuml;ltiges F&auml;lligkeitsdatum ein');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE_ERROR','Die Zahlung kann nicht verarbeitet werden, weil die grundlegenden Anforderungen nicht erfüllt wurden.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_GUARANTEE_MINIMUM_AMOUNT_ERROR','Der Mindestbetrag sollte bei mindestens 19,98 EUR');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_PUBLIC_TITLE', 'Kauf auf Rechnung  '.(((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true') ? '<a href="https://www.novalnet.de" target="_blank"/><img title="Kauf auf Rechnung " alt="Kauf auf Rechnung " src="'.DIR_WS_ICONS.'payment/novalnet_invoice.png" width=8% ></a>':''));
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT_TITLE', 'Mindesttransaktionsbetrag für die Autorisierung (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_MANUAL_CHECK_LIMIT_DESC', 'Übersteigt der Bestellbetrag das genannte Limit, wird die Transaktion, bis zu ihrer Bestätigung durch Sie, auf on hold gesetzt. Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE_TITLE','Anzahl der Raten');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_CYCLE_DESC','Wählen Sie die verschiedenen Anzahlen der Raten aus, die Sie erlauben wollen (Mehrfachnennung möglich).');

define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_VISIBILITY_BY_AMOUNT_TITLE','Mindestwarenwert (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_VISIBILITY_BY_AMOUNT_DESC','Geben Sie den Mindestwarenwert ein, von dem ab die Zahlungsart f&uuml;r den Kunden beim Checkout angezeigt wird');

define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B_TITLE','B2B-Kunden erlauben');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_ALLOW_B2B_DESC','B2B-Kunden erlauben, Bestellungen aufzugeben');

