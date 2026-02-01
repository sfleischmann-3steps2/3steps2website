<?php
// Fehlerberichterstattung aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Alternative mail()-Funktion, die direkt sendmail aufruft
function sendMailDirect($to, $subject, $message, $headers) {
    // E-Mail-Inhalt vorbereiten
    $email = "To: $to\r\n";
    $email .= "Subject: $subject\r\n";
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

// Header für HTML-Ausgabe
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formular-Debug</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1, h2 { color: #1da6ef; }
        pre { background-color: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
        .test-form { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Formular-Debug</h1>
    
    <div class="section">
        <h2>PHP-Konfiguration</h2>
        <table>
            <tr>
                <th>PHP-Version</th>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <th>mail() Funktion verfügbar</th>
                <td><?php echo function_exists('mail') ? '<span class="success">Ja</span>' : '<span class="error">Nein</span>'; ?></td>
            </tr>
            <tr>
                <th>sendmail_path</th>
                <td><?php echo ini_get('sendmail_path') ?: '<span class="error">Nicht konfiguriert</span>'; ?></td>
            </tr>
            <tr>
                <th>SMTP-Server</th>
                <td><?php echo ini_get('SMTP') ?: '<span class="error">Nicht konfiguriert</span>'; ?></td>
            </tr>
            <tr>
                <th>SMTP-Port</th>
                <td><?php echo ini_get('smtp_port') ?: '<span class="error">Nicht konfiguriert</span>'; ?></td>
            </tr>
            <tr>
                <th>Server-Name</th>
                <td><?php echo $_SERVER['SERVER_NAME']; ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>Debug-Log</h2>
        <?php
        $debugLogFile = '../form_debug.log';
        if (file_exists($debugLogFile)) {
            $logs = file_get_contents($debugLogFile);
            echo '<pre>' . htmlspecialchars($logs) . '</pre>';
        } else {
            echo '<p class="error">Debug-Log nicht gefunden. Versuche das Formular abzusenden, um das Log zu erstellen.</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>PHP-Fehler</h2>
        <?php
        $errorLogFile = '../php_errors.log';
        if (file_exists($errorLogFile)) {
            $errors = file_get_contents($errorLogFile);
            echo '<pre>' . htmlspecialchars($errors) . '</pre>';
        } else {
            echo '<p>Keine PHP-Fehler gefunden.</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Test-Formular</h2>
        <div class="test-form">
            <form action="process_form.php" method="post">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Name *</label>
                    <input type="text" name="name" required style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">E-Mail *</label>
                    <input type="email" name="email" required style="width: 100%; padding: 8px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px;">Nachricht</label>
                    <textarea name="nachricht" rows="4" style="width: 100%; padding: 8px;"></textarea>
                </div>
                <div style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" name="datenschutz" required> Datenschutz akzeptiert *
                    </label>
                </div>
                <div>
                    <button type="submit" style="background-color: #1da6ef; color: white; border: none; padding: 10px 15px; cursor: pointer;">Test-Anfrage senden</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="section">
        <h2>E-Mail-Test</h2>
        <?php
        if (isset($_GET['testmail'])) {
            $to = "contact@3steps2.com";
            $subject = "Test E-Mail von Debug-Seite";
            $message = "Dies ist eine Test-E-Mail, gesendet von der Debug-Seite am " . date('Y-m-d H:i:s');
            $headers = 'From: contact@3steps2.com' . "\r\n" .
                      'Reply-To: contact@3steps2.com' . "\r\n" .
                      'Return-Path: contact@3steps2.com' . "\r\n" .
                      'X-Mailer: PHP/' . phpversion() . "\r\n" .
                      'MIME-Version: 1.0' . "\r\n" .
                      'Content-Type: text/plain; charset=utf-8';
            
            // Direkte Sendmail-Methode anstelle von mail()
            $result = sendMailDirect($to, $subject, $message, $headers);
            
            if ($result['success']) {
                echo '<p class="success">Test-E-Mail wurde mit direktem Sendmail-Aufruf gesendet. Überprüfe deinen Posteingang.</p>';
                echo '<pre>Befehl: ' . htmlspecialchars($result['command']) . '</pre>';
                if (!empty($result['output'])) {
                    echo '<pre>Ausgabe: ' . htmlspecialchars(implode("\n", $result['output'])) . '</pre>';
                }
            } else {
                echo '<p class="error">Fehler beim Senden der Test-E-Mail mit direktem Sendmail-Aufruf. Rückgabewert: ' . $result['returnVar'] . '</p>';
                echo '<pre>Befehl: ' . htmlspecialchars($result['command']) . '</pre>';
                if (!empty($result['output'])) {
                    echo '<pre>Ausgabe: ' . htmlspecialchars(implode("\n", $result['output'])) . '</pre>';
                }
            }
        } else {
            echo '<p><a href="?testmail=1" style="display: inline-block; background-color: #1da6ef; color: white; text-decoration: none; padding: 10px 15px;">Test-E-Mail senden</a></p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>E-Mail an Absender testen</h2>
        <?php
        if (isset($_GET['testabsender'])) {
            $to = "contact@3steps2.com"; // Absender-Adresse als Empfänger
            $subject = "Test E-Mail an Absender";
            $message = "Dies ist eine Test-E-Mail, die an die Absender-Adresse gesendet wird, um zu prüfen, ob diese E-Mails empfangen kann. Gesendet am " . date('Y-m-d H:i:s');
            $headers = 'From: contact@3steps2.com' . "\r\n" .
                      'Reply-To: contact@3steps2.com' . "\r\n" .
                      'X-Mailer: PHP/' . phpversion() . "\r\n" .
                      'MIME-Version: 1.0' . "\r\n" .
                      'Content-Type: text/plain; charset=utf-8';
            
            // Direkte Sendmail-Methode anstelle von mail()
            $result = sendMailDirect($to, $subject, $message, $headers);
            
            if ($result['success']) {
                echo '<p class="success">Test-E-Mail wurde mit direktem Sendmail-Aufruf an die Absender-Adresse gesendet. Überprüfe den Posteingang von ' . htmlspecialchars($to) . '.</p>';
                echo '<pre>Befehl: ' . htmlspecialchars($result['command']) . '</pre>';
                if (!empty($result['output'])) {
                    echo '<pre>Ausgabe: ' . htmlspecialchars(implode("\n", $result['output'])) . '</pre>';
                }
            } else {
                echo '<p class="error">Fehler beim Senden der Test-E-Mail an die Absender-Adresse mit direktem Sendmail-Aufruf. Rückgabewert: ' . $result['returnVar'] . '</p>';
                echo '<pre>Befehl: ' . htmlspecialchars($result['command']) . '</pre>';
                if (!empty($result['output'])) {
                    echo '<pre>Ausgabe: ' . htmlspecialchars(implode("\n", $result['output'])) . '</pre>';
                }
            }
        } else {
            echo '<p><a href="?testabsender=1" style="display: inline-block; background-color: #1da6ef; color: white; text-decoration: none; padding: 10px 15px;">Test-E-Mail an Absender senden</a></p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>Direct Sendmail Test</h2>
        <?php
        if (isset($_GET['directmail'])) {
            $to = "contact@3steps2.com";
            $subject = "Direct Sendmail Test";
            $message = "Dies ist ein Test mit direktem Aufruf des Sendmail-Programms.\n\nZeitstempel: " . date('Y-m-d H:i:s');
            
            // E-Mail-Inhalt vorbereiten
            $email = "To: $to\r\n";
            $email .= "From: contact@3steps2.com\r\n";
            $email .= "Subject: $subject\r\n";
            $email .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $email .= "MIME-Version: 1.0\r\n";
            $email .= "Content-Type: text/plain; charset=utf-8\r\n";
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
            
            if ($returnVar === 0) {
                echo '<p class="success">Direkter Sendmail-Aufruf erfolgreich. Überprüfe deinen Posteingang.</p>';
                echo '<pre>Befehl: ' . htmlspecialchars($cmd) . '</pre>';
                if (!empty($output)) {
                    echo '<pre>Ausgabe: ' . htmlspecialchars(implode("\n", $output)) . '</pre>';
                }
            } else {
                echo '<p class="error">Fehler beim direkten Sendmail-Aufruf. Rückgabewert: ' . $returnVar . '</p>';
                echo '<pre>Befehl: ' . htmlspecialchars($cmd) . '</pre>';
                if (!empty($output)) {
                    echo '<pre>Ausgabe: ' . htmlspecialchars(implode("\n", $output)) . '</pre>';
                }
            }
        } else {
            echo '<p><a href="?directmail=1" style="display: inline-block; background-color: #1da6ef; color: white; text-decoration: none; padding: 10px 15px;">Direkten Sendmail-Aufruf testen</a></p>';
        }
        ?>
    </div>
</body>
</html> 