<?php

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'tr'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + (365 * 24 * 60 * 60), '/');

    // Redirect to same page without ?lang=...
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    header("Location: $url");
    exit;
}

// Load language preference from session or cookie
$lang = $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? 'en');

// Load language file
$lang_file = __DIR__ . '/../lang/' . $lang . '.php';
if (file_exists($lang_file)) {
    $t = require $lang_file;
} else {
    $t = require __DIR__ . '/../lang/en.php';
}

// Make available globally
$GLOBALS['lang'] = $lang;
