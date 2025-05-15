<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/lang.php';
include __DIR__ . '/language_switcher.php';
?>

<style>
    :root {
        --bg: #121212;
        --fg: #ffffff;
        --secondary: #1e1e1e;
        --border: #333;
        --accent: #2a2a2a;
        --highlight: #007bff;
    }

    body {
        background-color: var(--bg);
        color: var(--fg);
        font-family: 'Segoe UI', sans-serif;
    }

    .header {
        background-color: var(--bg);
        color: var(--fg);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 30px;
        flex-wrap: wrap;
        gap: 20px;
        border-bottom: 1px solid var(--border);
    }

    .header-left, .header-center, .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-logo img {
        height: 40px;
    }

    .search-box {
        position: relative;
        width: 250px;
    }

    .search-box input {
        width: 100%;
        padding: 8px 12px;
        border: none;
        border-radius: 6px;
        background-color: var(--secondary);
        color: white;
    }

    .search-box input::placeholder {
        color: #aaa;
    }

    #searchResults {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--secondary);
        border: 1px solid var(--border);
        color: white;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .header-right a {
        color: #ccc;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .header-right a:hover {
        background-color: var(--accent);
    }

    #dm-bar {
        position: fixed;
        bottom: 0;
        right: 0;
        width: 260px;
        background: var(--secondary);
        border: 1px solid var(--border);
        border-top-left-radius: 8px;
        padding: 12px;
        z-index: 9999;
        color: white;
        box-shadow: -2px -2px 10px rgba(0, 0, 0, 0.3);
    }

    #dm-bar strong {
        display: block;
        margin-bottom: 10px;
        font-size: 16px;
    }

    #dm-friends {
        max-height: 200px;
        overflow-y: auto;
    }

    .chat-window {
        width: 300px;
        height: 400px;
        background: var(--secondary);
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        position: relative;
        border-radius: 6px;
        overflow: hidden;
    }

    .chat-window .chat-header {
        background: var(--accent);
        padding: 8px 12px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
    }

    .chat-window .chat-messages {
        flex: 1;
        padding: 8px;
        overflow-y: auto;
        font-size: 14px;
    }

    .chat-window form {
        display: flex;
        border-top: 1px solid var(--border);
    }

    .chat-window form input {
        flex: 1;
        padding: 8px;
        border: none;
        background: var(--bg);
        color: white;
    }

    #chat-windows {
        position: fixed;
        bottom: 0;
        right: 270px;
        display: flex;
        flex-direction: row-reverse;
        gap: 12px;
        z-index: 9999;
    }
</style>

<div class="header">
    <!-- LEFT -->
    <div class="header-left">
        <div class="header-logo">
            <a href="/bird_clone/index.php"><img src="/bird_clone/assets/logo.png" alt="BIRD Logo"></a>
        </div>
        <div><strong><?= $t['username'] ?>:</strong> <?= htmlspecialchars($_SESSION['username']) ?></div>
    </div>

    <!-- CENTER -->
    <div class="header-center search-box">
        <form method="GET" action="/bird_clone/search.php" id="searchForm">
            <input type="text" name="q" id="searchInput" placeholder="<?= $t['search_username'] ?>" autocomplete="off" required>
        </form>
        <div id="searchResults"></div>
    </div>

    <!-- RIGHT -->
    <div class="header-right">
        <a href="/bird_clone/index.php"><?= $t['main_feed'] ?></a>
        <a href="/bird_clone/user.php?u=<?= urlencode($_SESSION['username']) ?>"><?= $t['profile'] ?></a>
        <a href="/bird_clone/settings.php"><?= $t['settings'] ?></a>
        <a href="/bird_clone/logout.php"><?= $t['logout'] ?></a>
    </div>
</div>

<hr>

<!-- DM BAR -->
<div id="dm-bar">
    <strong>💬 <?= $t['direct_messages'] ?? 'Messages' ?></strong>
    <div id="dm-friends"></div>
</div>

<!-- CHAT WINDOWS -->
<div id="chat-windows"></div>

<!-- JS Scripts -->
<script>
    const input = document.getElementById('searchInput');
    const resultsBox = document.getElementById('searchResults');

    input.addEventListener('input', () => {
        const query = input.value.trim();
        if (!query.length) {
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

    document.addEventListener('click', (e) => {
        if (!resultsBox.contains(e.target) && e.target !== input) {
            resultsBox.style.display = 'none';
        }
    });

    function refreshDmFriends() {
        fetch('/bird_clone/ajax/mutual_friends.php')
            .then(res => res.text())
            .then(html => {
                const container = document.getElementById('dm-friends');
                if (container) container.innerHTML = html;
            });
    }

    setInterval(refreshDmFriends, 5000);
    refreshDmFriends();

    function openChatWindow(username) {
        if (document.getElementById('chat-' + username)) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'chat-window';
        wrapper.id = 'chat-' + username;

        wrapper.innerHTML = `
            <div class="chat-header">
                @${username}
                <span style="cursor:pointer;" onclick="closeChatWindow('${username}')">✖</span>
            </div>
            <div class="chat-messages"></div>
            <form onsubmit="return sendMessage('${username}', this)">
                <input type="text" name="content" placeholder="<?= $t['type_message'] ?>" required>
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
  const input = form.querySelector('input[name=content]');
  const content = input.value.trim();
  if (!content) return false;

  fetch('/bird_clone/actions/send_message.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `to=${encodeURIComponent(username)}&content=${encodeURIComponent(content)}`
  }).then(() => {
    // ✅ Reload chat window via JS
    fetch(`/bird_clone/ajax/load_chat.php?u=${username}`)
      .then(res => res.text())
      .then(html => {
        const box = document.getElementById('chat-' + username)?.querySelector('.chat-messages');
        if (box) {
          box.innerHTML = html;
          box.scrollTop = box.scrollHeight;
        }
      });
    input.value = '';
  });

  return false;
}

</script>

<script>
// Auto-refresh all open chat windows every 5 seconds
setInterval(() => {
  document.querySelectorAll('.chat-window').forEach(win => {
    const username = win.id.replace('chat-', '');
    const box = win.querySelector('.chat-messages');
    if (!username || !box) return;

    const atBottom = box.scrollHeight - box.scrollTop <= box.clientHeight + 5;

    fetch(`/bird_clone/ajax/load_chat.php?u=${encodeURIComponent(username)}`)
      .then(res => res.text())
      .then(html => {
        box.innerHTML = html;
        if (atBottom) {
          box.scrollTop = box.scrollHeight;
        }
      });
  });
}, 2000);
</script>

