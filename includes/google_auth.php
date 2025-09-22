<?php
function get_google_auth_url() {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'offline'
    ];
    return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
}
?>