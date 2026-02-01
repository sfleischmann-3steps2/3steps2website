<?php
/**
 * 3steps2 Kontaktformular - Verarbeitung
 * Produktionsversion fuer Domain Factory
 */

// Fehlerberichterstattung fuer Produktion
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Output Buffer starten
ob_start();

try {
    // CORS Header
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");
    header('Content-Type: application/json; charset=utf-8');

    // Nur POST-Anfragen erlauben
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Nur POST-Anfragen sind erlaubt.');
    }

    // Formularfelder extrahieren und bereinigen
    $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
    $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
    $telefon = isset($_POST['telefon']) ? trim(strip_tags($_POST['telefon'])) : '';
    $unternehmen = isset($_POST['unternehmen']) ? trim(strip_tags($_POST['unternehmen'])) : '';
    $nachricht = isset($_POST['nachricht']) ? trim(strip_tags($_POST['nachricht'])) : '';

    // Interesse (Checkboxes) verarbeiten
    $interesse = [];
    if (isset($_POST['interesse'])) {
        $interesse = is_array($_POST['interesse']) ? $_POST['interesse'] : [$_POST['interesse']];
    }

    $interesseLabels = [
        'erstgespraech' => 'Kostenloses Erstgespraech',
        'workshop' => 'KI-Workshop'
    ];

    $interesseFormatiert = [];
    foreach ($interesse as $i) {
        $interesseFormatiert[] = isset($interesseLabels[$i]) ? $interesseLabels[$i] : $i;
    }
    $interesseText = !empty($interesseFormatiert) ? implode(', ', $interesseFormatiert) : 'Nicht angegeben';

    // Datenschutz-Checkbox pruefen
    $datenschutz = isset($_POST['datenschutz']);

    // Validierung
    if (empty($name)) {
        throw new Exception('Bitte geben Sie Ihren Namen ein.');
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Bitte geben Sie eine gueltige E-Mail-Adresse ein.');
    }

    if (!$datenschutz) {
        throw new Exception('Bitte akzeptieren Sie die Datenschutzerklaerung.');
    }

    // E-Mail Konfiguration
    $to = 's.fleischmann@3steps2.de';
    $subject = "Kontaktanfrage von $name - 3steps2.de";

    // E-Mail-Nachricht erstellen
    $message = "Neue Anfrage ueber das Kontaktformular auf 3steps2.de\n";
    $message .= "============================================\n\n";
    $message .= "Name: $name\n";
    $message .= "E-Mail: $email\n";
    $message .= "Telefon: " . ($telefon ?: 'Nicht angegeben') . "\n";
    $message .= "Unternehmen: " . ($unternehmen ?: 'Nicht angegeben') . "\n";
    $message .= "Interesse an: $interesseText\n\n";
    $message .= "Nachricht:\n";
    $message .= "----------\n";
    $message .= ($nachricht ?: 'Keine Nachricht') . "\n\n";
    $message .= "============================================\n";
    $message .= "Datenschutzerklaerung akzeptiert: Ja\n";
    $message .= "Gesendet am: " . date('d.m.Y H:i:s') . "\n";

    // E-Mail-Header
    $headers = "From: kontakt@3steps2.de\r\n";
    $headers .= "Reply-To: $name <$email>\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    // E-Mail senden
    $mailSent = mail($to, $subject, $message, $headers);

    if ($mailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Vielen Dank fuer Ihre Anfrage! Wir werden uns zeitnah bei Ihnen melden.'
        ]);
    } else {
        throw new Exception('Die E-Mail konnte leider nicht gesendet werden. Bitte versuchen Sie es spaeter erneut oder kontaktieren Sie uns direkt.');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
