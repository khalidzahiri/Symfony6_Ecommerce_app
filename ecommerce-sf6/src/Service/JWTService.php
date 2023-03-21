<?php

namespace App\Service;

class JWTService {
    // On génére le token

    public function generate(array $header, array $payload, string $secret, int $validity = 10800):string
    {
        if ($validity <=0 ){
            return "";
        }
        $now = new \DateTimeImmutable();
        $exp = $now->getTimestamp() + $validity;

        $payload ['iat'] = $now->getTimestamp();
        $payload ['exp'] = $exp;

        // On encode en base64

        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // // On nettoei les valeurs encodé

        $base64Header = str_replace(['+', '/', '='],['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='],['-', '_', ''], $base64Payload);

        // On génére la signature

        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        $base64Signature = str_replace(['+', '/', '='],['-', '_', ''], $base64Signature);

        // On cree le token

        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;
        return $jwt;

    }

    // je verifie que le token est valid

    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-0-9\-\_\=]+\.[a-zA-0-9\-\_\=]+\.[a-zA-0-9\-\_\=]+$/', $token
        ) === 1;
    }
}