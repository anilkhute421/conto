<?php

    // cron job
    $url = 'https://contolioapi.azurewebsites.net/api/expirePaymentStatusChange';

    $crl = curl_init();

    curl_setopt($crl, CURLOPT_URL, $url);

    curl_exec($crl);

    curl_close($crl);

?>
