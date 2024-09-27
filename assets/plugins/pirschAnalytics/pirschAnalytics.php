<?php

<?php

function sendToPirschAnalytics($url, $client_ip, $access_key) {
    $api_url = 'https://api.pirsch.io/api/v1/hit';

    // Prepara i dati
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
        'tags' => array(
            'example_tag' => 'example_value'  // Puoi modificare o aggiungere tag personalizzati
        )
    );

    // Rimuovi i valori nulli dal payload
    $data = array_filter($data);

    // Crea la richiesta
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_key  // Usa l'access key configurata
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Esegui la richiesta
    $response = curl_exec($ch);
    curl_close($ch);

    // Controlla la risposta
    if ($response === false) {
        global $modx;
        $modx->logEvent(1, 3, 'Errore nell\'invio dei dati a Pirsch Analytics', 'Pirsch Analytics Plugin');
    }
}

// Ottieni l'URL corrente
$url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$client_ip = $_SERVER['REMOTE_ADDR'];

// Leggi la Pirsch Access Key dalle configurazioni del plugin
$access_key = $modx->getPluginConfig('pirsch_access_key');

// Verifica che la chiave non sia vuota
if (!empty($access_key)) {
    sendToPirschAnalytics($url, $client_ip, $access_key);
} else {
    global $modx;
    $modx->logEvent(1, 3, 'Pirsch Access Key non configurata', 'Pirsch Analytics Plugin');
}
