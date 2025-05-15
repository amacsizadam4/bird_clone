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
        <div id="searchResults" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; z-index: 1000; display: none;"></div>
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

<!-- Search script -->
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

<!-- DM Sidebar -->
<div id="dm-bar" style="position: fixed; bottom: 0; right: 0; width: 250px; background: white; border: 1px solid #ccc; border-top-left-radius: 8px; padding: 8px; z-index: 9999;">
    <strong>💬 <?= $t['direct_messages'] ?? 'Messages' ?></strong>
    <div id="dm-friends" style="max-height: 200px; overflow-y: auto;"></div>
</div>

<script>
fetch('/bird_clone/ajax/mutual_friends.php')
    .then(res => res.text())
    .then(html => {
        document.getElementById('dm-friends').innerHTML = html;
    });
</script>

<!-- Chat Window Container -->
<div id="chat-windows" style="position: fixed; bottom: 0; right: 0; display: flex; flex-direction: row-reverse; gap: 10px; z-index: 9999;"></div>

<!-- Chat Window JS -->
<script>
function openChatWindow(username) {
    if (document.getElementById('chat-' + username)) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'chat-window';
    wrapper.id = 'chat-' + username;
    wrapper.style = `
        width: 300px;
        height: 400px;
        background: white;
        border: 1px solid #ccc;
        display: flex;
        flex-direction: column;
        position: relative;
    `;

    wrapper.innerHTML = `
        <div style="background: #eee; padding: 5px; font-weight: bold;">
            @${username}
            <span style="float:right; cursor:pointer;" onclick="closeChatWindow('${username}')">✖</span>
        </div>
        <div class="chat-messages" style="flex:1; padding:5px; overflow-y:auto;"></div>
        <form onsubmit="return sendMessage('${username}', this)">
            <input type="text" name="content" style="width:100%; padding:5px;" placeholder="<?= $t['type_message'] ?? 'Type a message...' ?>" required>
        </form>
    `;

    document.getElementById('chat-windows').appendChild(wrapper);

    fetch(`/bird_clone/ajax/load_chat.php?u=${username}`)
        .then(res => res.text())
        .then(html => {
            const messagesEl = wrapper.querySelector('.chat-messages');
            messagesEl.innerHTML = html;
            messagesEl.scrollTop = messagesEl.scrollHeight;
        });
}

function closeChatWindow(username) {
    const win = document.getElementById('chat-' + username);
    if (win) win.remove();
}

function sendMessage(username, form) {
    const chatBox = form.closest('.chat-window').querySelector('.chat-messages');
    const input = form.querySelector('input[name=content]');
    const content = input.value.trim();
    if (!content) return false;

    fetch('/bird_clone/actions/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `to=${encodeURIComponent(username)}&content=${encodeURIComponent(content)}`
    })
    .then(res => res.text())
    .then(html => {
        chatBox.innerHTML = html;
        chatBox.scrollTop = chatBox.scrollHeight;
        input.value = '';
    });

    return false;
}
</script>

<script>
function refreshDmFriends() {
    fetch('/bird_clone/ajax/mutual_friends.php')
        .then(res => res.text())
        .then(html => {
            const container = document.getElementById('dm-friends');
            if (container) {
                container.innerHTML = html;
            }
        });
}

// Initial load
refreshDmFriends();

// Refresh every 5 seconds
setInterval(refreshDmFriends, 5000);
</script>
