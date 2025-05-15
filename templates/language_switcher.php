<?php
// Language switching logic
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'tr'])) {
    $_SESSION['lang'] = $_GET['lang'];
    setcookie('lang', $_GET['lang'], time() + (365 * 24 * 60 * 60), '/');
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Detect current language
$currentLang = $_SESSION['lang'] ?? ($_COOKIE['lang'] ?? 'en');
?>

<style>
.language-switcher {
  display: flex;
  gap: 10px;
  align-items: center;
  padding: 5px 10px;
}
.language-switcher a {
  border: 2px solid transparent;
  border-radius: 6px;
  padding: 4px;
  display: inline-block;
  transition: 0.2s;
}
.language-switcher a.active {
  border-color: var(--accent);
  background: var(--card);
}
.language-switcher img {
  width: 24px;
  height: 24px;
}
</style>

<div class="language-switcher">
  <a href="?lang=en" class="<?= $currentLang === 'en' ? 'active' : '' ?>">
    <img src="assets/icons/en.png" alt="English">
  </a>
  <a href="?lang=tr" class="<?= $currentLang === 'tr' ? 'active' : '' ?>">
    <img src="assets/icons/tr.png" alt="Türkçe">
  </a>
</div>
