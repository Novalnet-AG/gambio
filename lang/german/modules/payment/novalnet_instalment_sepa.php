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
 * Script : novalnet_sepa.php
 *
 */
require_once (dirname(__FILE__).'/novalnet.php');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_TEXT_TITLE', 'Ratenzahlung per SEPA-Lastschrift');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_DESC','Ihr Konto wird nach Abschicken der Bestellung belastet');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ALLOWED_TITLE','Zugelassene Gebiete');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ALLOWED_DESC','Diese Zahlungsart wird nur f&uuml;r die aufgef&uuml;hrten Gebiete zugelassen. Geben Sie die Gebiete in folgendem Format ein, z.B. DE, AT, CH etc. Falls das Feld leer ist, sind alle Gebiete zugelassen.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_STATUS_TITLE','Zahlungsart aktivieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_TEST_MODE_TITLE','Testmodus aktivieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_TEST_MODE_DESC','Die Zahlung wird im Testmodus durchgef&uuml;hrt, daher wird der Betrag f&uuml;r diese Transaktion nicht eingezogen');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ENABLE_FRAUDMODULE_TITLE','Betrugspr&uuml;fung aktivieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ENABLE_FRAUDMODULE_DESC','Um den K&auml;ufer einer Transaktion zu authentifizieren, werden die PIN automatisch generiert und an den K&auml;ufer geschickt. Dieser Dienst wird nur f&uuml;r Kunden aus DE, AT und CH angeboten');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_VISIBILITY_BY_AMOUNT_TITLE','Mindestwarenwert (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_MANUAL_CHECK_LIMIT_TITLE', 'Mindesttransaktionsbetrag für die Autorisierung
 (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_MANUAL_CHECK_LIMIT_DESC', 'Übersteigt der Bestellbetrag das genannte Limit, wird die Transaktion, bis zu ihrer Bestätigung durch Sie, auf on hold gesetzt. Sie können das Feld leer lassen, wenn Sie möchten, dass alle Transaktionen als on hold behandelt werden.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_VISIBILITY_BY_AMOUNT_DESC','Geben Sie den Mindestwarenwert ein, von dem ab die Zahlungsart f&uuml;r den Kunden beim Checkout angezeigt wird');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ENDCUSTOMER_INFO_TITLE','Benachrichtigung des K&auml;ufers');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ENDCUSTOMER_INFO_DESC','Der eingegebene Text wird auf der Checkout-Seite angezeigt');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_SORT_ORDER_TITLE','Geben Sie eine Sortierreihenfolge an');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_SORT_ORDER_DESC','Diese Zahlungsart wird unter anderen Zahlungsarten (in aufsteigender Richtung) anhand der angegebenen Nummer f&uuml;r die Sortierung eingeordnet');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ORDER_STATUS_TITLE','Abschluss-Status der Bestellung');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ORDER_STATUS_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PENDING_ORDER_STATUS_TITLE','Bestellstatus der ausstehenden Zahlung');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PENDING_ORDER_STATUS_DESC','&nbsp;');

define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PAYMENT_ZONE_TITLE','Zahlungsgebiet');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PAYMENT_ZONE_DESC','Diese Zahlungsart wird f&uuml;r die angegebenen Gebiete angezeigt');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ONE_CLICK','Kauf mit einem Klick');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_NEW_ACCOUNT', 'Neue Kontodaten eingeben');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_GIVEN_ACCOUNT','Eingegebene Kontodaten');
define('MODULE_PAYMENT_NOVALNET_BANK_COUNTRY' ,'Land der Bank');
define('MODULE_PAYMENT_NOVALNET_ACCOUNT_OR_IBAN','IBAN');
define('MODULE_PAYMENT_NOVALNET_BANKCODE_OR_BIC','BIC oder Bankleitzahl');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_FORM_MANDATE_CONFIRM_TEXT', 'Ich erteile hiermit das SEPA-Lastschriftmandat (elektronische Übermittlung) und bestätige, dass die Bankverbindung korrekt ist.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PAYMENT_DUE_DATE_TITLE','Abstand (in Tagen) bis zum SEPA-Einzugsdatum');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PAYMENT_DUE_DATE_DESC','Geben Sie die Anzahl der Tage ein, nach denen die Zahlung vorgenommen werden soll (muss zwischen 2 und 14 Tagen liegen).');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_GUARANTEE_TITLE','<h3>Voraussetzungen für die Ratenzahlung</h3>Zugelassene Staaten: AT, DE, CH <br>Zugelassene Währung: EUR <br> Mindestbetrag der Bestellung >= 19,98 EUR <br> Bitte beachten Sie, dass der Betrag einer Rate mindestens 9,99 EUR betragen muss und Raten, die diese Kriterien nicht erfüllen, nicht im Ratenplan angezeigt werden.
<br>Mindestalter des Endkunden >= 18 Jahre <br> Rechnungsadresse und Lieferadresse müssen übereinstimmen ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_GUARANTEE_DESC','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_BLOCK_TITLE','<b>Instalment SEPA Konfiguration</b>');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_DUE_DATE_ERROR','SEPA F&auml;lligkeitsdatum Ung&uuml;ltiger');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_MANDATE_CONFIRM_ERROR','Akzeptieren Sie bitte das SEPA-Lastschriftmandat');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_SELECT_COUNTRY','Wählen Sie bitte ein Land aus');
define('MODULE_PAYMENT_NOVALNET_SELECT_PAYMENT_METHOD','Wählen Sie bitte die Zahlungsart aus');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_GUARANTEE_ERROR','Die Zahlung kann nicht verarbeitet werden, weil die grundlegenden Anforderungen nicht erfüllt wurden.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_GUARANTEE_MINIMUM_AMOUNT_ERROR','Der Mindestbetrag sollte bei mindestens 19,98 EUR');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_PUBLIC_TITLE', 'Lastschrift SEPA '. (((!defined('MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY')) || MODULE_PAYMENT_NOVALNET_PAYMENT_LOGO_DISPLAY == 'true')?'<a href="https://www.novalnet.de" target="_blank"/><img title="Lastschrift SEPA" alt="Lastschrift SEPA" src="'.DIR_WS_ICONS.'payment/novalnet_sepa.png" height="30px"></a>':''));
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_GUARANTEE_PAYMENT_PENDING_TEXT','Ihre Bestellung wird derzeit überprüft. Wir werden Sie in Kürze über den Bestellstatus informieren. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ABOUT_MANDATE_TEXT','Ich ermächtige den Zahlungsempfänger, Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von dem Zahlungsempfänger auf mein Konto gezogenen Lastschriften einzulösen.<br><br><strong style="text-align:center">Gläubiger-Identifikationsnummer: DE53ZZZ00000004253</strong><br><br><strong>Hinweis:  </strong>Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_FRAUDMODULE_CALLBACK_TITLE','Betrugsprüfung aktivieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_FRAUDMODULE_CALLBACK_DESC','PIN-by-Callback');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_FRAUDMODULE_SMS_TITLE','&nbsp;');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_FRAUDMODULE_SMS_DESC','PIN-by-SMS');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_CYCLE_TITLE','Anzahl der Raten');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_CYCLE_DESC','Wählen Sie die verschiedenen Anzahlen der Raten aus, die Sie erlauben wollen (Mehrfachnennung möglich).');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_DATE_ERROR','SEPA F&auml;lligkeitsdatum Ung&uuml;ltiger');

define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ALLOW_B2B_TITLE','B2B-Kunden erlauben');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_ALLOW_B2B_DESC','B2B-Kunden erlauben, Bestellungen aufzugeben');
