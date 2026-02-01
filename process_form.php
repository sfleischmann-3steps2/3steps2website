<?php
// Fehlerberichterstattung aktivieren (während der Entwicklung)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Fehleranzeige im Browser deaktivieren

// Fehlerprotokoll aktivieren
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Funktion zum direkten Aufruf von sendmail
function sendMailDirect($to, $subject, $message, $headers) {
    // E-Mail-Inhalt vorbereiten
    $email = "To: $to\r\n";
    $email .= "Subject: $subject\r\n"; // Betreff explizit hinzufügen
    $email .= $headers;
    $email .= "\r\n";
    $email .= $message;
    
    // Temporäre Datei erstellen
    $tempfile = tempnam(sys_get_temp_dir(), 'mail');
    file_put_contents($tempfile, $email);
    
    // Sendmail direkt aufrufen
    $cmd = "/usr/sbin/sendmail -fcontact@3steps2.com -t -i < " . escapeshellarg($tempfile);
    $output = [];
    $returnVar = 0;
    exec($cmd, $output, $returnVar);
    
    // Temporäre Datei löschen
    unlink($tempfile);
    
    return [
        'success' => ($returnVar === 0),
        'command' => $cmd,
        'output' => $output,
        'returnVar' => $returnVar
    ];
}

// Debug-Logging starten
$logFile = 'form_debug.log';
file_put_contents($logFile, "\n" . str_repeat('-', 50) . "\n", FILE_APPEND);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Neue Formular-Anfrage empfangen\n", FILE_APPEND);
file_put_contents($logFile, "SERVER: " . print_r($_SERVER['SERVER_NAME'], true) . "\n", FILE_APPEND);
file_put_contents($logFile, "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
file_put_contents($logFile, "POST-Daten: " . print_r($_POST, true) . "\n", FILE_APPEND);

// Alle verfügbaren Mail-Konfigurationen protokollieren
file_put_contents($logFile, "Mail-Konfiguration:\n", FILE_APPEND);
file_put_contents($logFile, "- SMTP: " . ini_get('SMTP') . "\n", FILE_APPEND);
file_put_contents($logFile, "- smtp_port: " . ini_get('smtp_port') . "\n", FILE_APPEND);
file_put_contents($logFile, "- sendmail_path: " . ini_get('sendmail_path') . "\n", FILE_APPEND);
file_put_contents($logFile, "- mail function exists: " . (function_exists('mail') ? 'Ja' : 'Nein') . "\n", FILE_APPEND);

// Sicherstellen, dass vor jeder Ausgabe die Header gesetzt werden
ob_start();

try {
    // CORS Einstellungen für lokale Entwicklung
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");

    // Antwortformat auf JSON setzen
    header('Content-Type: application/json');

    // Überprüfen, ob das Formular abgesendet wurde
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Nur POST-Anfragen sind erlaubt.');
    }

    // Formularfelder prüfen und extrahieren
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefon = filter_input(INPUT_POST, 'telefon', FILTER_SANITIZE_STRING);
    $unternehmen = filter_input(INPUT_POST, 'unternehmen', FILTER_SANITIZE_STRING);
    $nachricht = filter_input(INPUT_POST, 'nachricht', FILTER_SANITIZE_STRING);

    // Protokolliere die empfangenen Daten
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Daten validiert und extrahiert:\n", FILE_APPEND);
    file_put_contents($logFile, "- Name: $name\n", FILE_APPEND);
    file_put_contents($logFile, "- Email: $email\n", FILE_APPEND);
    file_put_contents($logFile, "- Telefon: " . ($telefon ?: 'Nicht angegeben') . "\n", FILE_APPEND);
    file_put_contents($logFile, "- Unternehmen: " . ($unternehmen ?: 'Nicht angegeben') . "\n", FILE_APPEND);

    // Interesse (Checkboxes) verarbeiten
    $interesse = [];
    if (isset($_POST['interesse']) && is_array($_POST['interesse'])) {
        $interesse = $_POST['interesse'];
    } elseif (isset($_POST['interesse']) && !is_array($_POST['interesse'])) {
        // Falls nur ein Wert übergeben wird (nicht als Array)
        $interesse = [$_POST['interesse']];
    }
    
    // Interesse als lesbaren Text formatieren
    $interesseLabels = [
        'erstgespraech' => 'Kostenloses Erstgespräch',
        'workshop' => 'KI-Workshop'
    ];
    
    $interesseFormatiert = [];
    foreach ($interesse as $i) {
        $interesseFormatiert[] = isset($interesseLabels[$i]) ? $interesseLabels[$i] : $i;
    }
    
    $interesseText = !empty($interesseFormatiert) ? implode(', ', $interesseFormatiert) : 'Nicht angegeben';

    // Datenschutz-Checkbox prüfen
    $datenschutz = isset($_POST['datenschutz']) ? true : false;
    file_put_contents($logFile, "- Datenschutz akzeptiert: " . ($datenschutz ? 'Ja' : 'Nein') . "\n", FILE_APPEND);

    // Validierung
    if (empty($name) || empty($email) || !$datenschutz) {
        throw new Exception('Bitte füllen Sie alle Pflichtfelder aus und akzeptieren Sie die Datenschutzerklärung.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
    }

    // Empfänger-E-Mail
    $to = 'contact@3steps2.com';

    // Betreff der E-Mail mit Namen des Absenders
    $subject = "Kontaktanfrage von $name über das 3steps2 Formular";

    // E-Mail-Nachricht erstellen
    $message = "
    Neue Anfrage über das Kontaktformular
    ------------------------------------

    Name: $name
    E-Mail: $email
    Telefon: " . ($telefon ?: 'Nicht angegeben') . "
    Unternehmen: " . ($unternehmen ?: 'Nicht angegeben') . "
    Interesse an: $interesseText

    Nachricht:
    " . ($nachricht ?: 'Keine Nachricht') . "

    ------------------------------------
    Datenschutzerklärung akzeptiert: Ja
    Diese E-Mail wurde automatisch vom Kontaktformular auf 3steps2.com gesendet.
    ";

    // E-Mail-Header
    $headers = [
        'From' => 'contact@3steps2.com',
        'Reply-To' => "$name <$email>",
        'Return-Path' => 'contact@3steps2.com',
        'X-Mailer' => 'PHP/' . phpversion(),
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=utf-8'
    ];

    // Header-String erstellen
    $headerString = '';
    foreach ($headers as $key => $value) {
        $headerString .= "$key: $value\r\n";
    }

    // Lokales Debugging - E-Mail-Simulierung
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Versuche, E-Mail zu senden:\n", FILE_APPEND);
    file_put_contents($logFile, "- To: $to\n", FILE_APPEND);
    file_put_contents($logFile, "- Subject: $subject\n", FILE_APPEND);
    file_put_contents($logFile, "- Headers: $headerString\n", FILE_APPEND);
    file_put_contents($logFile, "- Message:\n$message\n", FILE_APPEND);

    // E-Mail mit direktem Sendmail-Aufruf senden statt mail()
    $result = sendMailDirect($to, $subject, $message, $headerString);
    $mailSent = $result['success'];
    
    // Protokolliere das Ergebnis
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - E-Mail-Versand " . ($mailSent ? "erfolgreich" : "fehlgeschlagen") . "\n", FILE_APPEND);
    
    // Zusätzliche Details protokollieren
    file_put_contents($logFile, "- Sendmail-Befehl: " . $result['command'] . "\n", FILE_APPEND);
    if (!empty($result['output'])) {
        file_put_contents($logFile, "- Ausgabe: " . implode("\n", $result['output']) . "\n", FILE_APPEND);
    }
    if (!$mailSent) {
        file_put_contents($logFile, "- Rückgabewert: " . $result['returnVar'] . "\n", FILE_APPEND);
    }

    // Antwort erstellen
    echo json_encode(['success' => true, 'message' => 'Vielen Dank für Ihre Anfrage! Wir werden uns zeitnah bei Ihnen melden.']);

} catch (Exception $e) {
    // Fehler protokollieren
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - FEHLER: " . $e->getMessage() . "\n", FILE_APPEND);
    if ($e->getTraceAsString()) {
        file_put_contents($logFile, "Trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    }
    
    // Fehlerantwort zurückgeben
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Buffer leeren und sicherstellen, dass alles gesendet wurde
ob_end_flush();
?> 