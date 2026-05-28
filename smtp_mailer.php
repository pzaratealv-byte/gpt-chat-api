<?php

function smtp_send_mail(array $config, string $toEmail, string $subject, string $body, ?string &$error = null): bool
{
    $host = $config['smtp_host'] ?? '';
    $port = (int)($config['smtp_port'] ?? 587);
    $secure = strtolower(trim($config['smtp_secure'] ?? 'tls'));
    $username = $config['smtp_username'] ?? '';
    $password = $config['smtp_password'] ?? '';
    $fromEmail = $config['from_email'] ?? '';
    $fromName = $config['from_name'] ?? 'Chatbot';

    if (!$host || !$port || !$username || !$password || !$fromEmail) {
        $error = 'Configuración SMTP incompleta.';
        return false;
    }

    $transport = ($secure === 'ssl' ? 'ssl://' : '') . $host;
    $socket = @stream_socket_client(
        $transport . ':' . $port,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        $error = 'No se pudo conectar al servidor SMTP: ' . $errstr . ' (' . $errno . ')';
        return false;
    }

    stream_set_timeout($socket, 30);

    $read = function () use ($socket, &$error): string|false {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        if ($data === '') {
            $error = 'Respuesta vacía del servidor SMTP.';
            return false;
        }
        return $data;
    };

    $expect = function (array $codes, string $response) use (&$error): bool {
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            $error = trim($response);
            return false;
        }
        return true;
    };

    $write = function (string $command) use ($socket): bool {
        return fwrite($socket, $command . "\r\n") !== false;
    };

    $response = $read();
    if ($response === false || !$expect([220], $response)) {
        fclose($socket);
        return false;
    }

    if (!$write('EHLO localhost')) {
        $error = 'No se pudo enviar EHLO.';
        fclose($socket);
        return false;
    }
    $response = $read();
    if ($response === false || !$expect([250], $response)) {
        fclose($socket);
        return false;
    }

    if ($secure === 'tls') {
        if (!$write('STARTTLS')) {
            $error = 'No se pudo iniciar STARTTLS.';
            fclose($socket);
            return false;
        }
        $response = $read();
        if ($response === false || !$expect([220], $response)) {
            fclose($socket);
            return false;
        }

        $cryptoOk = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if (!$cryptoOk) {
            $error = 'No se pudo activar TLS.';
            fclose($socket);
            return false;
        }

        if (!$write('EHLO localhost')) {
            $error = 'No se pudo reenviar EHLO tras STARTTLS.';
            fclose($socket);
            return false;
        }
        $response = $read();
        if ($response === false || !$expect([250], $response)) {
            fclose($socket);
            return false;
        }
    }

    if (!$write('AUTH LOGIN')) {
        $error = 'No se pudo iniciar autenticación SMTP.';
        fclose($socket);
        return false;
    }
    $response = $read();
    if ($response === false || !$expect([334], $response)) {
        fclose($socket);
        return false;
    }

    if (!$write(base64_encode($username))) {
        $error = 'No se pudo enviar el usuario SMTP.';
        fclose($socket);
        return false;
    }
    $response = $read();
    if ($response === false || !$expect([334], $response)) {
        fclose($socket);
        return false;
    }

    if (!$write(base64_encode($password))) {
        $error = 'No se pudo enviar la contraseña SMTP.';
        fclose($socket);
        return false;
    }
    $response = $read();
    if ($response === false || !$expect([235], $response)) {
        fclose($socket);
        return false;
    }

    if (!$write('MAIL FROM:<' . $fromEmail . '>')) {
        $error = 'No se pudo enviar MAIL FROM.';
        fclose($socket);
        return false;
    }
    $response = $read();
    if ($response === false || !$expect([250], $response)) {
        fclose($socket);
        return false;
    }

    if (!$write('RCPT TO:<' . $toEmail . '>')) {
        $error = 'No se pudo enviar RCPT TO.';
        fclose($socket);
        return false;
    }
    $response = $read();
    if ($response === false || !$expect([250, 251], $response)) {
        fclose($socket);
        return false;
    }

    if (!$write('DATA')) {
        $error = 'No se pudo iniciar DATA.';
        fclose($socket);
        return false;
    }
    $response = $read();
    if ($response === false || !$expect([354], $response)) {
        fclose($socket);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $safeFromName = str_replace(['"', "\r", "\n"], '', $fromName);

    $headers = [];
    $headers[] = 'Date: ' . date(DATE_RFC2822);
    $headers[] = 'From: "' . $safeFromName . '" <' . $fromEmail . '>';
    $headers[] = 'To: <' . $toEmail . '>';
    $headers[] = 'Subject: ' . $encodedSubject;
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';

    $message = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n.", "\n..", $body) . "\r\n.";

    if (fwrite($socket, $message . "\r\n") === false) {
        $error = 'No se pudo enviar el contenido del correo.';
        fclose($socket);
        return false;
    }

    $response = $read();
    if ($response === false || !$expect([250], $response)) {
        fclose($socket);
        return false;
    }

    $write('QUIT');
    fclose($socket);
    return true;
}
