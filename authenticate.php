<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

use \Firebase\JWT\JWT;

require('./vendor/autoload.php');
require('./config.php');
require('./help.php');

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);
if (isset($_POST['user'])) {
    $user = $_POST['user'];
    $email = $user['email'];
    $foundUser = db_fetch_row("SELECT * FROM $omega_app_manager_user WHERE email = '$email'");
    $hasValidCredentials = false;

    if (count($foundUser) > 0) {
        if (password_verify($user['password'], $foundUser['password'])) $hasValidCredentials = true;
        else $hasValidCredentials = false;
    }

    if ($hasValidCredentials) {
        $secretKey  = $jwt_secret;
        $tokenId    = base64_encode(random_bytes(16));
        $issuedAt   = new DateTimeImmutable();
        $expiresIn  = $issuedAt->modify('+30 days')->getTimestamp();
        $userName   = $foundUser["user_name"];
        $userEmail = $foundUser["email"];

        // Create the token as an array
        $data = [
            'iat'  => $issuedAt,
            'jti'  => $tokenId,
            'nbf'  => $issuedAt,
            'exp'  => $expiresIn,
            'data' => [
                'userName' => $userName,
                'userEmail' => $userEmail,
            ]
        ];

        // Encode the array to a JWT string.
        $token =  JWT::encode(
            $data,      //Data to be encoded in the JWT
            $secretKey, // The signing key
            'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );

        $result = [
            'token' => $token,
            'tokenId' => $tokenId,
            'expiresIn' => $expiresIn
        ];

        echo json_encode($result);
    } else {
        http_response_code(404);
    }
}
