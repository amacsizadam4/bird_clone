<?php
// Handle ?lang=en or ?lang=tr
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'tr'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + (365 * 24 * 60 * 60), '/'); // Save for 1 year
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')); // Remove ?lang param
    exit;
}
?>

<a href="?lang=en"><img src="assets/icons/en.png" alt="English" width="24"></a>
<a href="?lang=tr"><img src="assets/icons/tr.png" alt="Türkçe" width="24"></a>
