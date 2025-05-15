<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/lang.php';
?>

<?php include __DIR__ . '/language_switcher.php'; ?>

<!-- GLOBAL HEADER -->
<div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0; gap: 20px; flex-wrap: wrap;">

    <!-- LEFT: Welcome -->
    <div>
        <strong><?= $t['username'] ?>:</strong> <?= htmlspecialchars($_SESSION['username']) ?>
    </div>

    <!-- CENTER: Search -->
<div style="position: relative; width: 250px;">
    <form method="GET" action="/bird_clone/search.php" id="searchForm">
        <input type="text" name="q" id="searchInput" placeholder="<?= $t['search_username'] ?>" autocomplete="off" required>
    </form>
    <div id="searchResults" style="
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    "></div>
</div>



    <!-- RIGHT: Navigation -->
    <div style="display: flex; gap: 10px;">
        <a href="/bird_clone/index.php"><?= $t['main_feed'] ?? 'Main Feed' ?></a>
        <a href="/bird_clone/user.php?u=<?= urlencode($_SESSION['username']) ?>"><?= $t['profile'] ?? 'My Profile' ?></a>
        <a href="/bird_clone/settings.php"><?= $t['settings'] ?? 'Settings' ?></a>
        <a href="/bird_clone/logout.php"><?= $t['logout'] ?></a>
    </div>

</div>

<hr>

<script>
const input = document.getElementById('searchInput');
const resultsBox = document.getElementById('searchResults');

input.addEventListener('input', function () {
    const query = input.value.trim();
    if (query.length === 0) {
        resultsBox.innerHTML = '';
        resultsBox.style.display = 'none';
        return;
    }

    fetch(`/bird_clone/ajax/search_users.php?q=${encodeURIComponent(query)}`)
        .then(res => res.text())
        .then(html => {
            resultsBox.innerHTML = html;
            resultsBox.style.display = 'block';
        });
});

document.addEventListener('click', function (e) {
    if (!resultsBox.contains(e.target) && e.target !== input) {
        resultsBox.style.display = 'none';
    }
});
</script>
