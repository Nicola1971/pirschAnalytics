<?php

// Funzione per inviare hit a Pirsch Analytics
function sendToPirschAnalytics($url, $client_ip, $access_key, $tags = array()) {
    $api_url = 'https://api.pirsch.io/api/v1/hit';

    // Prepara i dati per l'invio
    $data = array(
        'url' => $url,
        'ip' => $client_ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'accept_language' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null,
        'sec_ch_ua' => isset($_SERVER['HTTP_SEC_CH_UA']) ? $_SERVER['HTTP_SEC_CH_UA'] : null,
        'sec_ch_ua_mobile' => isset($_SERVER['HTTP_SEC_CH_UA_MOBILE']) ? $_SERVER['HTTP_SEC_CH_UA_MOBILE'] : null,
        'sec_ch_ua_platform' => isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) ? $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] : null,
        'sec_ch_ua_platform_version' => isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION']) ? $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] : null,
        'sec_ch_width' => isset($_SERVER['HTTP_SEC_CH_WIDTH']) ? $_SERVER['HTTP_SEC_CH_WIDTH'] : null,
        'sec_ch_viewport_width' => isset($_SERVER['HTTP_SEC_CH_VIEWPORT_WIDTH']) ? $_SERVER['HTTP_SEC_CH_VIEWPORT_WIDTH'] : null,
        'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
        'tags' => $tags  // Aggiungi i tags personalizzati
    );

    // Rimuovi i valori nulli dal payload
    $data = array_filter($data);

    // Crea la richiesta CURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_key
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Esegui la richiesta
    $response = curl_exec($ch);
    curl_close($ch);

    // Log in caso di errore
    if ($response === false) {
        global $modx;
        $modx->logEvent(1, 3, 'Errore nell\'invio dei dati a Pirsch Analytics', 'Pirsch Analytics Plugin');
    }
}

// Ottieni l'URL corrente e l'IP dell'utente
$url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$client_ip = $_SERVER['REMOTE_ADDR'];

// Ottieni la Pirsch Access Key e i tags dalla configurazione del plugin
$params = $modx->event->params;
$access_key = isset($params['pirsch_access_key']) ? $params['pirsch_access_key'] : null;
$tags_string = isset($params['pirsch_tags']) ? $params['pirsch_tags'] : ''; // Configurazione dei tags

// Parsing dei tags (esempio: "user_status,logged_in|device_type,desktop")
$tags_array = array();
if (!empty($tags_string)) {
    $tag_groups = explode('|', $tags_string); // Divide per gruppi usando '|'
    foreach ($tag_groups as $group) {
        list($tag_name, $tag_value) = explode(',', $group); // Divide in nome tag e valore
        $tags_array[trim($tag_name)] = trim($tag_value);
    }
}

// Verifica se la Access Key è configurata
if (!empty($access_key)) {
    // Invia i dati a Pirsch Analytics con i tags
    sendToPirschAnalytics($url, $client_ip, $access_key, $tags_array);
} else {
    global $modx;
    $modx->logEvent(1, 3, 'Pirsch Access Key non configurata o non trovata.', 'Pirsch Analytics Plugin');
}