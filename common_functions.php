<?php

function pr($array) {
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

function getAccessToken($code) {

    $Rec_Data = array();

    if (!empty($code)) {

        $postFields = 'client_id=' . BING_CLIENT_ID . '&client_secret=' . BING_CLIENT_SECRET . '&code=' . $code . '&grant_type=authorization_code&redirect_uri=' . BING_REDIRECT_URI;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://login.live.com/oauth20_token.srf');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $Rec_Data = curl_exec($ch);

        if (curl_exec($ch) === false) {
            return $Rec_Data;
        }

        $Rec_Data = json_decode($Rec_Data, true);
    }

    return $Rec_Data;
}

function refreshAccessToken($refresh_token) {
    $Rec_Data = array();
    if (!empty($refresh_token)) {
        $postFields = 'client_id=' . BING_CLIENT_ID . '&client_secret=' . BING_CLIENT_SECRET . '&grant_type=refresh_token&redirect_uri=' . BING_REDIRECT_URI . '&refresh_token=' . $refresh_token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://login.live.com/oauth20_token.srf');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $Rec_Data = curl_exec($ch);

        if (curl_exec($ch) === false) {
            return $Rec_Data;
        }
        
        $Rec_Data = json_decode($Rec_Data, true);
        
        return $Rec_Data;
    }
}

?>