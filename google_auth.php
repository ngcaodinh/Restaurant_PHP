<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';

$client_id = '490954709937-j4gohbodbtb7kg2215oe63fbe21cn8oi.apps.googleusercontent.com';
$redirect_uri = 'http://localhost/Restaurant_PHP/callback.php';
$auth_uri = 'https://accounts.google.com/o/oauth2/auth';
$scope = 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';

$auth_url = $auth_uri . '?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'online',
    'prompt' => 'consent'
]);

header('Location: ' . $auth_url);
exit;
?>