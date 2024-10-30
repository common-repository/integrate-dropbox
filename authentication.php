<?php

function sanitize_input($input) {
    return preg_replace('/[^a-zA-Z0-9|_-]/', '', $input);
}

$code = isset($_GET['code']) ? sanitize_input($_GET['code']) : null;
$state = isset($_GET['state']) ? sanitize_input($_GET['state']) : null;

if (empty($code) || empty($state)) {
    exit('You are not allowed on this page. Are you a chili human?');
}

$getStateData = explode('|', $state);
$redirectURL = null;

if (isset($getStateData[1])) {
    $decodedState = base64_decode(strtr($getStateData[1], '-_', '+/'));
    if ($decodedState === false) {
        exit('Invalid state encoding');
    }

    $explodedState = explode('&', $decodedState);

    if (count($explodedState) >= 2) {
        $nonce = isset($explodedState[3]) ? $explodedState[3] : '';
        
        $redirectURLParams = [
            $explodedState[0],
            $explodedState[1],
            "code=" . urlencode($code),
            "state=" . urlencode($state),
            $nonce,
        ];

        $redirectURL = implode('&', $redirectURLParams);
    } else {
        exit('Invalid state format');
    }
}

if (filter_var($redirectURL, FILTER_VALIDATE_URL)) {
    header("Content-Security-Policy: default-src 'self'");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    header("Location: " . $redirectURL);
    exit;
} else {
    echo "Invalid redirect URL: " . htmlspecialchars($redirectURL);
}

?>
