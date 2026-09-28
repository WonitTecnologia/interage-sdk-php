<?php

declare(strict_types=1);

namespace WonitTecnologia\Interage\Http;

use WonitTecnologia\Interage\Exception\TransportException;

/**
 * Transporte padrão, usando a extensão curl nativa do PHP.
 */
final class CurlTransport implements Transport
{
    public function send(Request $request): Response
    {
        $handle = curl_init($request->url);
        if ($handle === false) {
            throw new TransportException('interage: não foi possível iniciar o curl');
        }

        $headerLines = [];
        foreach ($request->headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        $responseHeaders = [];
        $options = [
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT_MS => (int) round($request->timeout * 1000),
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return strlen($line);
            },
        ];
        if ($request->body !== null) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }
        curl_setopt_array($handle, $options);

        $body = curl_exec($handle);
        if ($body === false) {
            $message = curl_error($handle);
            $code = curl_errno($handle);
            curl_close($handle);
            throw new TransportException('interage: erro na requisição HTTP: ' . $message, $code);
        }

        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        return new Response($status, $responseHeaders, (string) $body);
    }
}
