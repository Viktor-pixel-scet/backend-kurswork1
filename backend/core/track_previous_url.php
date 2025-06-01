<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getProjectBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . "://" . $host . "/backend-kurswork/";
}

$base_url = getProjectBaseUrl();
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$return_url = $base_url;

if ($referer === 'http://localhost/') {
    $referer = $base_url;
}

if (!empty($referer) && strpos($referer, '/backend-kurswork/') !== false) {
    $return_url = $referer;
}

if ($return_url !== 'http://localhost/') {
    $_SESSION['previous_url'] = $return_url;
}
