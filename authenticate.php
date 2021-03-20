<?php

declare(strict_types=1);

use \Firebase\JWT\JWT;

require('./vendor/autoload.php');
require('./config.php');
require('./help.php');

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
if (isset($_POST['user'])) {
    $user = $_POST['user'];
    $username = $user['username'];
    $foundUser = db_fetch_row("SELECT * FROM $omega_app_manager_user WHERE user_name = '$username'");
    $hasValidCredentials = false;

    if (count($foundUser) > 0) {
        if (password_verify($user['password'], $foundUser['password'])) $hasValidCredentials = true;
        else $hasValidCredentials = false;
    }

    if ($hasValidCredentials) {
        $secretKey  = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=';
        $tokenId    = base64_encode(random_bytes(16));
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+6 minutes')->getTimestamp();      // Add 60 seconds
        $serverName = "your.domain.name";
        $username   = "username";                                           // Retrieved from filtered POST data

        // Create the token as an array
        $data = [
            'iat'  => $issuedAt,         // Issued at: time when the token was generated
            'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss'  => $serverName,       // Issuer
            'nbf'  => $issuedAt,         // Not before
            'exp'  => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'userName' => $username, // User name
            ]
        ];

        // Encode the array to a JWT string.
        echo JWT::encode(
            $data,      //Data to be encoded in the JWT
            $secretKey, // The signing key
            'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );
    }
}
