<?php

namespace App\Service;

class JWTService {
    // On génére le token

    public function generate(array $header, array $payload, string $secret, int $validity = 10800):string
    {
        if ($validity > 0){
            $now = new \DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload ['iat'] = $now->getTimestamp();
            $payload ['exp'] = $exp;
        }

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

    // Je recupere le Payload
    public function getPayload(string $token): array
    {
        // je demonte le token en plusieurs array a chaque fois quil y'a un .
        $array = explode('.', $token);

        // Je decode le Payload
        $payload = json_decode(base64_decode($array[1]),true);

        return $payload;
    }

    // Je recupere le Header
    public function getHeader(string $token): array
    {
        // je demonte le token en plusieurs array a chaque fois quil y'a un .
        $array = explode('.', $token);

        // Je decode le Header
        $header = json_decode(base64_decode($array[0]),true);

        return $header;
    }

    // Je verifie si le token a expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new \DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    // Je verifie la signature du token
    public function check(string $token, string $secret)
    {
        // je recupere le header et le payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // Je regenere le token
        $verifToken =  $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }

}



















