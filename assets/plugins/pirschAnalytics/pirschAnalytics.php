<?php

// Funzione per inviare eventi personalizzati a Pirsch
function sendPirschEvent($event_name, $event_data, $client_ip, $access_key, $tags = array()) {
    $api_url = 'https://api.pirsch.io/api/v1/event';

    // Prepara i dati dell'evento
    $data = array(
        'name' => $event_name,
        'params' => $event_data,
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
        'url' => 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'tags' => $tags  // Aggiungi i tags personalizzati
    );

    // Filtra valori nulli dall'array dei dati
    $data = array_filter($data);

    // Crea la richiesta
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

    // Logga l'errore in caso di fallimento
    if ($response === false) {
        global $modx;
        $modx->logEvent(1, 3, 'Error sending event to Pirsch Analytics', 'Pirsch Analytics Plugin');
    }
}

// Funzione per tracciare le pagine 404
function track404Error($client_ip, $access_key, $tags = array()) {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // Include query string
    $event_name = '404_error';
    $event_data = array('url' => $url);

    // Aggiungi un tag personalizzato per il tipo di errore
    $tags['error_type'] = '404';

    // Invia l'evento a Pirsch
    sendPirschEvent($event_name, $event_data, $client_ip, $access_key, $tags);
}

// Ottieni la configurazione del plugin usando $modx->event->params
$params = $modx->event->params;
$access_key = isset($params['pirsch_access_key']) ? $params['pirsch_access_key'] : null;
$event_string = isset($params['pirsch_events']) ? $params['pirsch_events'] : ''; // Configurazione degli eventi
$tags_string = isset($params['pirsch_tags']) ? $params['pirsch_tags'] : ''; // Configurazione dei tags
$client_ip = $_SERVER['REMOTE_ADDR'];

// Ottieni la query string dall'URL
$query = isset($_GET['query']) ? $_GET['query'] : null;

// Parsing degli eventi (esempio: "button_click,subscribe_button|form_submit,contact_form")
$event_array = array();
if (!empty($event_string)) {
    $event_groups = explode('|', $event_string); // Divide per gruppi usando '|'
    foreach ($event_groups as $group) {
        list($event_name, $event_param) = explode(',', $group); // Divide in nome evento e parametro
        $event_array[trim($event_name)] = trim($event_param);
    }
}

// Parsing dei tags (esempio: "user_status,logged_in|device_type,desktop")
$tags_array = array('query' => $query); // Aggiungi la query string come tag
if (!empty($tags_string)) {
    $tag_groups = explode('|', $tags_string); // Divide per gruppi usando '|'
    foreach ($tag_groups as $group) {
        list($tag_name, $tag_value) = explode(',', $group); // Divide in nome tag e valore
        $tags_array[trim($tag_name)] = trim($tag_value);
    }
}

// Verifica che la access key sia configurata
if (!empty($access_key)) {
    // Invia ogni evento definito nella configurazione
    foreach ($event_array as $event_name => $event_param) {
        $event_data = array('param' => $event_param);

        // Invia l'evento a Pirsch con i tags personalizzati, inclusa la query
        sendPirschEvent($event_name, $event_data, $client_ip, $access_key, $tags_array);
    }

    // Traccia gli errori 404 utilizzando l'evento `OnPageNotFound`
    if ($modx->event->name == 'OnPageNotFound') {
        track404Error($client_ip, $access_key, $tags_array);
    }
} else {
    $modx->logEvent(1, 3, 'Pirsch Access Key not configured or not found.', 'Pirsch Analytics Plugin');
}
