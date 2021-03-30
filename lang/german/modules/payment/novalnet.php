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
 * Script : novalnet.php
 *
 */
define('MODULE_PAYMENT_NOVALNET_TRUE', 'Wahr');
define('MODULE_PAYMENT_NOVALNET_FALSE', 'Falsch');
define('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_TITLE','<b>Novalnet Haupteinstellungen</b> (V_11.2.0)');
define('MODULE_PAYMENT_NOVALNET_SELECT','-- Ausw&auml;hlen -- ');
define('MODULE_PAYMENT_NOVALNET_NO_SCRIPT','Aktivieren Sie bitte JavaScript in Ihrem Browser, um die Zahlung fortzusetzen');
define('MODULE_PAYMENT_NOVALNET_OPTION_NONE','Keiner');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONCALLBACK','PIN-by-Callback');
define('MODULE_PAYMENT_NOVALNET_FRAUD_OPTIONSMS', 'PIN-by-SMS');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_CALLBACK_INPUT_TITLE','Telefonnummer');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_INPUT_TITLE','Mobiltelefonnummer');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_INFO' ,'In Kürze erhalten Sie einen Telefonanruf mit der PIN zu Ihrer Transaktion, um die Zahlung abzuschließen');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_PIN_INFO','In Kürze erhalten Sie eine SMS mit der PIN zu Ihrer Transaktion, um die Zahlung abzuschließen.');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_REFUND_CONFIRM','Sind Sie sicher, dass Sie den Betrag zurückerstatten möchten?');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_ZERO_AMOUNT_BOOK_CONFIRM','Sind Sie sich sicher, dass Sie den Bestellbetrag buchen wollen?');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_CAPTURE_CONFIRM','Sind Sie sicher, dass Sie die Zahlung einziehen möchten?');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_VOID_CONFIRM','Sind Sie sicher, dass Sie die Zahlung stornieren wollen?');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_REQUEST_DESC', 'PIN zu Ihrer Transaktion');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_NEW_PIN', '&nbsp; PIN vergessen?');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_EMPTY', 'PIN eingeben');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_PIN_NOTVALID', 'Die von Ihnen eingegebene PIN ist falsch');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_ERROR', 'Der Bestellbetrag hat sich geändert, setzen Sie bitte die neue Bestellung fort');
define('MODULE_PAYMENT_NOVALNET_CONFIG_BLOCK_FUNC_ERROR','Erw&auml;hnt PHP-Paket (e) in diesem Server nicht verf&uuml;gbar ist. Bitte aktivieren Sie sie.<br/>');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TELEPHONE_ERROR','Geben Sie bitte Ihre Telefonnummer ein');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_SMS_ERROR', 'Geben Sie bitte Ihre Mobiltelefonnummer ein.');
define('MODULE_PAYMENT_NOVALNET_MENTION_PAYMENT_CATEGORY','Diese Transaktion wird mit Zahlungsgarantie verarbeitet');
define('MODULE_PAYMENT_NOVALNET_MENTION_GUARANTEE_PAYMENT_PENDING_TEXT','Ihre Bestellung ist unter Bearbeitung. Sobald diese bestätigt wurde, erhalten Sie alle notwendigen Informationen zum Ausgleich der Rechnung. Wir bitten Sie zu beachten, dass dieser Vorgang bis zu 24 Stunden andauern kann.');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ID','Novalnet Transaktions-ID: ');
define('MODULE_PAYMENT_NOVALNET_TEST_ORDER_MESSAGE','Testbestellung');
define('MODULE_PAYMENT_NOVALNET_IBAN','IBAN');
define('MODULE_PAYMENT_GUARANTEE_FIELD','Ihr Geburtsdatum');
define('MODULE_PAYMENT_NOVALNET_ACCOUNT_HOLDER','Kontoinhaber');
define('MODULE_PAYMENT_NOVALNET_TEST_MODE_MSG','<span style="color:red;">Die Zahlung wird im Testmodus durchgef&uuml;hrt, daher wird der Betrag f&uuml;r diese Transaktion nicht eingezogen.</span>');
define('MODULE_PAYMENT_NOVALNET_VALID_DUEDATE_MESSAGE','Das Datum sollte in der Zukunft liegen.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_DOB_ERROR_MESSAGE','Geben Sie ein gültiges Geburtsdatum ein');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_ERROR_MESSAGE','Ungültiger Betrag');
define('MODULE_PAYMENT_NOVALNET_VALID_MERCHANT_CREDENTIALS_ERROR','F&uuml;llen Sie bitte alle Pflichtfelder aus.');
define('MODULE_PAYMENT_NOVALNET_VALID_ACCOUNT_CREDENTIALS_ERROR','Ihre Kontodaten sind ungültig.');
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_TITLE','Ablauf der Buchung steuern');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_TEXT', 'Wählen Sie bitte einen Status aus');
define('MODULE_PAYMENT_NOVALNET_INVOICE_ON_HOLD_CONFIRM_TEXT','Die Transaktion mit der TID: %s wurde erfolgreich bestätigt und das Fälligkeitsdatum auf %s gesetzt.'); 
define('MODULE_PAYMENT_NOVALNET_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','Die Buchung wurde am %s um %s Uhr bestätigt.');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_TRANS_CONFIRM_SUCCESSFUL_MESSAGE','Novalnet-Callback-Nachricht erhalten: Die Buchung wurde am %s um %s Uhr bestätigt.');
define('MODULE_PAYMENT_NOVALNET_TRANS_DEACTIVATED_MESSAGE','Die Transaktion wurde am %s um %s Uhr storniert.');
define('MODULE_PAYMENT_NOVALNET_REFUND_AMT_TITLE','Geben Sie bitte den erstatteten Betrag ein');
define('MODULE_PAYMENT_NOVALNET_REFUND_TITLE','Ablauf der R&uuml;ckerstattung');
define('MODULE_PAYMENT_NOVALNET_REFUND_PARENT_TID_MSG','Die Rückerstattung wurde für die TID: %s mit dem Betrag %s durchgeführt.');
define('MODULE_PAYMENT_NOVALNET_REFUND_CHILD_TID_MSG',' Ihre neue TID für den erstatteten Betrag: %s');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_EX',' (in der kleinsten Währungseinheit, z.B. 100 Cent = entsprechen 1.00 EUR)');
define('MODULE_PAYMENT_NOVALNET_AMOUNT', 'Betrag: ');
define('MODULE_PAYMENT_NOVALNET_CONFIRM_TEXT','Best&auml;tigen');
define('MODULE_PAYMENT_NOVALNET_BACK_TEXT', 'Zur&uuml;ck');
define('MODULE_PAYMENT_NOVALNET_UPDATE_TEXT','&Auml;ndern');
define('MODULE_PAYMENT_NOVALNET_CANCEL_TEXT','Stornieren');
define('MODULE_PAYMENT_NOVALNET_SELECT_STATUS_OPTION', '--Ausw&auml;hlen--');
define('MODULE_PAYMENT_NOVALNET_BOOK_TITLE','Transaktion durchf&uuml;hren');
define('MODULE_PAYMENT_NOVALNET_BOOK_AMT_TITLE','Buchungsbetrag der Transaktion');
define('MODULE_PAYMENT_NOVALNET_TRANS_BOOKED_MESSAGE','Ihre Bestellung wurde mit einem Betrag von %s gebucht. Ihre neue TID für den gebuchten Betrag: %s');
define('MODULE_PAYMENT_NOVALNET_PAYMENTTYPE_NONE', 'Keiner');
define('MODULE_PAYMENT_NOVALNET_SEPA_TEXT','Lastschrift SEPA');
define('MODULE_PAYMENT_NOVALNET_BIC', 'BIC');
define('MODULE_PAYMENT_NOVALNET_INV_PRE_ACCOUNT_HOLDER','Kontoinhaber: ');
define('MODULE_PAYMENT_NOVALNET_DUE_DATE','Fälligkeitsdatum: ');
define('MODULE_PAYMENT_NOVALNET_MAP_PAGE_HEADER','Loggen Sie sich hier mit Ihren Novalnet H&auml;ndler-Zugangsdaten ein.Um neue Zahlungsarten zu aktivieren, kontaktieren Sie bitte <a href="mailto:support@novalnet.de">support@novalnet.de</a>');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_ERROR','Die Zahlung war nicht erfolgreich. Ein Fehler trat auf.');
define('MODULE_PAYMENT_NOVALNET_REFUND_REFERENCE_TEXT', 'Referenz f&uuml;r die R&uuml;ckerstattung');
define('MODULE_PAYMENT_NOVALNET_TRANSACTION_REDIRECT_ERROR', 'Während der Umleitung wurden einige Daten geändert. Die Überprüfung des Hashes schlug fehl.');
define('MODULE_PAYMENT_NOVALNET_BANK','Bank: ');
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF','Verwendungszweck: ');
define('MODULE_PAYMENT_NOVALNET_INVPRE_REF_MULTI','Verwendungszweck%s : ');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_SINGLE_TEXT','Bitte verwenden Sie nur den unten angegebenen Verwendungszweck für die Überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:');
define('MODULE_PAYMENT_NOVALNET_PAYMENT_MULTI_TEXT','Bitte verwenden Sie einen der unten angegebenen Verwendungszwecke für die Überweisung, da nur so Ihr Geldeingang zugeordnet werden kann:');
define('MODULE_PAYMENT_NOVALNET_ORDER_NUMBER',' Bestellnummer ');
define('MODULE_PAYMENT_NOVALNET_INVOICE_COMMENTS_PARAGRAPH', 'Überweisen Sie bitte den Betrag an die unten aufgeführte Bankverbindung unseres Zahlungsdienstleisters Novalnet.');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_TITLE', 'Betrugspr&uuml;fung aktivieren');
define('MODULE_PAYMENT_NOVALNET_FRAUDMODULE_DESC', 'Um den K&auml;ufer einer Transaktion zu authentifizieren, werden die PIN automatisch generiert und an den K&auml;ufer geschickt. Dieser Dienst wird nur f&uuml;r Kunden aus DE, AT und CH angeboten');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_AGE_ERROR_MESSAGE','Sie müssen mindestens 18 Jahre alt sein');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_DOB_EMPTY_ERROR_MESSAGE','Geben Sie bitte Ihr Geburtsdatum ein');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_INVAILD_DOB_ERROR_MESSAGE','Ungültiges Datumsformat');
define('MODULE_PAYMENT_NOVALNET_TRANS_DUE_DATE_TITLE','F&auml;lligkeitsdatum der Transaktion');
define('MODULE_PAYMENT_NOVALNET_TRANS_SLIP_EXPIRY_DATE','Verfallsdatum des Zahlscheins ');
define('MODULE_PAYMENT_NOVALNET_NEAREST_STORE_DETAILS','Barzahlen-Partnerfiliale in Ihrer Nähe');
define('MODULE_PAYMENT_NOVALNET_TRANS_AMOUNT_TITLE', 'Transaktionsbetrag');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_DUE_DATE_TITLE' ,'Betrag / F&auml;lligkeitsdatum &auml;ndern');
define('MODULE_PAYMENT_NOVALNET_SLIP_DATE_CHANGE_TITLE' ,'Betrag / Verfallsdatum des Zahlscheins ändern');
define('MODULE_PAYMENT_NOVALNET_AMOUNT_CHANGE_TITLE','Betrag &auml;ndern');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_UPDATE_TEXT','Sind Sie sich sicher, dass Sie den Bestellbetrag ändern wollen?');
define('MODULE_PAYMENT_NOVALNET_CHANGE_SLIP_DATE','Sind Sie sicher, dass Sie das Ablaufdatum des Zahlscheins ändern wollen?');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_DATE_UPDATE_TEXT','Sind Sie sich sicher, dass Sie den Betrag / das Fälligkeitsdatum der Bestellung ändern wollen?');
define('MODULE_PAYMENT_NOVALNET_ORDER_AMT_SLIP_DATE_UPDATE_TEXT','Sind Sie sicher, dass sie den Bestellbetrag / das Ablaufdatum des Zahlscheins ändern wollen?');
define('MODULE_PAYMENT_NOVALNET_TRANS_UPDATED_MESSAGE','Die Transaktion wurde mit dem Betrag %s und dem Fälligkeitsdatum %s aktualisiert.');
define('MODULE_PAYMENT_NOVALNET_SEPA_TRANS_AMOUNT_UPDATED_MESSAGE','Der Betrag der Transaktion %s wurde am %s um %s Uhr erfolgreich geändert.');
define('MODULE_PAYMENT_DUE_DATE_INVAILD','Ungültiges Fälligkeitsdatum');
define('MODULE_PAYMENT_INVOICE_CREDIT_COMMENTS','Novalnet-Callback-Skript erfolgreich ausgeführt für die TID: %s mit dem Betrag %s am %s um %s Uhr. Bitte suchen Sie nach der bezahlten Transaktion in unserer Novalnet-Händleradministration mit der TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGEBACK_COMMENTS' ,'Novalnet-Callback-Nachricht erhalten: Chargeback erfolgreich importiert für die TID: %s Betrag: %s am %s um %s. TID der Folgebuchung: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_BOOKBACK_COMMENTS' ,'Novalnet-Callback-Meldung erhalten: Rückerstattung / Bookback erfolgreich ausgeführt für die TID: %s Betrag: %s am %s & %s. TID der Folgebuchung: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_UPDATE_COMMENTS' ,'Novalnet-Callback-Skript erfolgreich ausgeführt für die TID: %s mit dem Betrag %s am %s um %s Uhr.');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_CHARGING_DATE_COMMENTS' ,'Nächstes Belastungsdatum: %s ');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_REFERENCE_TID_COMMENTS',' Verwendungszweck TID: %s');
define('MODULE_PAYMENT_NOVALNET_CALLBACK_RECURRING_COMMENTS','Novalnet-Callback-Skript erfolgreich ausgeführt für die TID: %s mit dem Betrag %s am , um %s %s Uhr.Bitte suchen Sie nach der bezahlten Transaktion in unserer Novalnet-Händleradministration mit der TID: %s.');
define('MODULE_PAYMENT_NOVALNET_CANCEL_ORDER_MESSAGE' ,'Die Transaktion wurde storniert. Grund: %s');
define('MODULE_PAYMENT_NOVALNET_TEST_TRANSACTION_EMAIL_HEADING' ,'Benachrichtigung zu Novalnet-Testbestellung-%s');
define('MODULE_PAYMENT_NOVALNET_TEST_TRANSACTION_EMAIL_CONTENT' ,'Sehr geehrte Kundin, <br/> <p> &emsp;wir möchten Sie darüber informieren, dass eine Testbestellung %s kürzlich in Ihrem Shop durchgeführt wurde. Stellen Sie bitte sicher, dass für Ihr Projekt im Novalnet-Händleradministrationsportal der Live-Modus gesetzt wurde und Zahlungen über Novalnet in Ihrem Shopsystem aktiviert sind. Ignorieren Sie bitte diese E-Mail, falls die Bestellung von Ihnen zu Testzwecken durchgeführt wurde. </p> <br/>Mit freundlichen Grüßen <br/> Novalnet AG');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_PENDING_TO_HOLD_MESSAGE','Novalnet-Callback-Nachricht erhalten: Der Status der Transaktion mit der TID: %s wurde am %s um %s Uhr  von ausstehend auf ausgesetzt geändert.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_MESSAGE','Wir freuen uns Ihnen mitteilen zu können, dass Ihre Bestellung bestätigt wurde.');
define('MODULE_PAYMENT_ONLINE_TRANSFER_CREDIT_COMMENTS','Der Betrag von (amount_currency) für die Bestellung (order_no) wurde bezahlt. Überprüfen Sie bitten den erhaltenen Betrag und die Details zur TID und aktualisieren Sie den Status der Bestellung entsprechend.');
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_MAIL_SUBJECT','Bestellbestätigung – Ihre Bestellung %s bei %s wurde bestätigt!');
define('MODULE_PAYMENT_NOVALNET_ORDER_MAIL_SUBJECT', 'Ihre Bestellung %s, am %s, %s');
$novalnet_temp_status_text = 'Zahlung über NN steht noch aus';
define('MODULE_PAYMENT_GUARANTEE_PAYMENT_CANCELLED_MESSAGE','Novalnet-Callback-Nachricht erhalten: Die Transaktion wurde am %s um %s Uhr storniert');
define('MODULE_PAYMENT_NOVALNET_ZERO_AMOUNT_TEXT', '<span style="color:red">Diese Bestellung wird als Nullbuchung verarbeitet. Ihre Zahlungsdaten werden für zukünftige Online-Einkäufe gespeichert.</span>');

define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_ADDRESS','Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Die Lieferadresse muss mit der Rechnungsadresse übereinstimmen)');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_COUNTRY','Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Als Land ist nur Deutschland, Österreich oder Schweiz erlaubt)');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_AMOUNT','Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Der Mindestbestellwert beträgt ');
define('MODULE_PAYMENT_NOVALNET_GUARANTEE_INVALID_CURRENCY','Die Zahlung kann nicht verarbeitet werden, da die grundlegenden Anforderungen für die Zahlungsgarantie nicht erfüllt wurden (Als Währung ist nur EUR erlaubt)');
define('MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE_TITLE','Bearbeitungsmaßnahme');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE_TITLE','Bearbeitungsmaßnahme');
define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_TITLE','Bearbeitungsmaßnahme');
define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_TITLE','Bearbeitungsmaßnahme');
define('MODULE_PAYMENT_NOVALNET_SEPA_AUTHENTICATE_TITLE','Bearbeitungsmaßnahme');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_AUTHENTICATE_TITLE','Bearbeitungsmaßnahme');
if($_SESSION['GX'] == '1'){
	define('MODULE_PAYMENT_NOVALNET_INVOICE_AUTHENTICATE_DESC','Zahlung autorisieren');
	define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INVOICE_AUTHENTICATE_DESC','Zahlung autorisieren');
	define('MODULE_PAYMENT_NOVALNET_CC_AUTHENTICATE_DESC','Zahlung autorisieren');
	define('MODULE_PAYMENT_NOVALNET_SEPA_AUTHENTICATE_DESC','Zahlung autorisieren');
	define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_AUTHENTICATE_DESC','Zahlung autorisieren');
	define('MODULE_PAYMENT_NOVALNET_PAYPAL_AUTHENTICATE_DESC','Zahlung autorisieren');
}
define('MODULE_PAYMENT_NOVALNET_CAPTURE','Zahlung einziehen');
define('MODULE_PAYMENT_NOVALNET_AUTHORIZE','Zahlung autorisieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT_TITLE','<b>Wählen Sie Ihren Ratenplan</b>');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT_DESC','<p>Wählen Sie die Finanzierungsoption, die Ihren Bedürfnissen am besten entspricht. Die Raten werden Ihnen entsprechend dem gewählten Ratenplan berechnet</p>');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_TEXT','Netto-Kreditbetrag:');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_CALLBACK_COMMENT','Für Ihre Bestellung Nr. %s  die nächste Rate fällig. Bitte beachten Sie weitere Details unten.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_INSTALMENTS_INFO','Information zu den Raten: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_PROCESSED_INSTALMENTS','Bezahlte Raten: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_DUE_INSTALMENTS','Offene Raten: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_NEXT_INSTALMENT_AMOUNT','Betrag jeder Rate: ');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_PAYMENT_REF','Bitte verwenden Sie bei Ihrer Überweisung nur den folgenden Verwendungszweck, da nur so Ihre Zahlung abgeglichen und Ihrer Bestellung zugeordnet werden kann. Verwendungszweck');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_SEPA_AMOUNT_DEBIT_TEXT','Die nächste Rate in Höhe von %s %s wird in ein bis drei Werktagen von Ihrem Konto abgebucht.');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_CANCEL_TEXT','Stornieren');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_CANCEL_ADMIN_TEXT','Raten stornier');
define('MODULE_PAYMENT_NOVALNET_SEPA_GUARANTEE_PAYMENT_PENDING_TEXT','Ihre Bestellung wird überprüft. Nach der Bestätigung senden wir Ihnen unsere Bankverbindung, an die Sie bitte den Gesamtbetrag der Bestellung überweisen. Bitte beachten Sie, dass dies bis zu 24 Stunden dauern kann');
define('MODULE_PAYMENT_NOVALNET_INSTALMENT_MAIL_SUBJECT','Für Ihren Einkauf bei %s, Bestellnr. %s, ist die nächste Rate fällig.');
