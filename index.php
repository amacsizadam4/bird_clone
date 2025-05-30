<?php
require 'includes/auth.php';
require 'config.php';
require 'includes/lang.php';
require 'includes/functions.php';
include 'templates/header.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$filter = $_GET['filter'] ?? 'recent';
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
$limit = 20;

switch ($filter) {
    case 'week':
        $orderSql = "(SELECT COUNT(*) FROM likes WHERE thought_id = t.id) DESC";
        $whereSql = "WHERE t.created_at >= NOW() - INTERVAL 7 DAY";
        $heading = $t['popular_week'];
        break;
    case 'all':
        $orderSql = "(SELECT COUNT(*) FROM likes WHERE thought_id = t.id) DESC";
        $whereSql = "";
        $heading = $t['popular_all'];
        break;
    default:
        $orderSql = "t.created_at DESC";
        $whereSql = "";
        $heading = $t['recent'];
        break;
}
?>
<link rel="stylesheet" href="assets/style.css">
<style>

</style>

<div class="container">
    <div class="tabs">
        <a href="?filter=recent" class="<?= $filter === 'recent' ? 'active' : '' ?>"><?= $t['recent'] ?></a>
        <a href="?filter=week" class="<?= $filter === 'week' ? 'active' : '' ?>"><?= $t['popular_week'] ?></a>
        <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>"><?= $t['popular_all'] ?></a>
    </div>

    <form method="POST" action="thought/create.php" enctype="multipart/form-data" class="card post-form">
        <textarea name="content" placeholder="<?= $t['whats_on_your_mind'] ?>" maxlength="280" required></textarea>
        
        <div class="file-inputs">
            <div class="file-input-wrapper">
                <input type="file" name="image1" accept="image/*" id="image1">
                <label for="image1" class="file-input-label">
                    <span class="icon">📷</span> Image 1
                </label>
            </div>
            <div class="file-input-wrapper">
                <input type="file" name="image2" accept="image/*" id="image2">
                <label for="image2" class="file-input-label">
                    <span class="icon">📷</span> Image 2
                </label>
            </div>
            <div class="file-input-wrapper">
                <input type="file" name="image3" accept="image/*" id="image3">
                <label for="image3" class="file-input-label">
                    <span class="icon">📷</span> Image 3
                </label>
            </div>
            <div class="file-input-wrapper">
                <input type="file" name="image4" accept="image/*" id="image4">
                <label for="image4" class="file-input-label">
                    <span class="icon">📷</span> Image 4
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn"><?= $t['post_button'] ?></button>
    </form>

    <h3><?= $heading ?></h3>

    <?php
    $stmt = $pdo->prepare("
        SELECT t.*, u.username,
               ot.content AS original_content,
               ou.username AS original_username,
               qt.content AS quote_content,
               qu.username AS quote_user
        FROM thoughts t
        JOIN users u ON t.user_id = u.id
        LEFT JOIN thoughts ot ON t.original_thought_id = ot.id
        LEFT JOIN users ou ON ot.user_id = ou.id
        LEFT JOIN thoughts qt ON t.quote_id = qt.id
        LEFT JOIN users qu ON qt.user_id = qu.id
        $whereSql
        ORDER BY $orderSql
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute();
    $thoughts = $stmt->fetchAll();

    foreach ($thoughts as $thought):
        $target_id = $thought['original_thought_id'] ?: $thought['id'];
        $likeCount = $pdo->query("SELECT COUNT(*) FROM likes WHERE thought_id = $target_id")->fetchColumn();
        $repostCount = $pdo->query("SELECT COUNT(*) FROM thoughts WHERE original_thought_id = $target_id")->fetchColumn();
        $quoteCount = $pdo->query("SELECT COUNT(*) FROM thoughts WHERE quote_id = $target_id")->fetchColumn();
    ?>

    <div onclick="openModal(<?= $target_id ?>)" class="card">
        <?php if ($thought['original_thought_id']): ?>
            <em><?= $t['reposted_by'] ?> <?= render_user_icon($thought['username'], 20) ?></em><br>
            <?php if ($thought['original_content'] && $thought['original_username']): ?>
                <strong><?= render_user_icon($thought['original_username'], 20) ?></strong><br>
                <p><?= nl2br(htmlspecialchars($thought['original_content'])) ?></p>
            <?php else: ?>
                <p><i><?= $t['post_deleted_or_unavailable'] ?></i></p>
            <?php endif; ?>
        <?php else: ?>
            <strong><?= render_user_icon($thought['username'], 30) ?></strong><br>
            <p><?= nl2br(htmlspecialchars($thought['content'])) ?></p>

            <?php
            $stmt2 = $pdo->prepare("SELECT image_path FROM thought_images WHERE thought_id = ?");
            $stmt2->execute([$thought['id']]);
            foreach ($stmt2->fetchAll() as $img) {
                echo "<img src='uploads/" . htmlspecialchars($img['image_path']) . "' width='150' style='margin: 5px; border-radius: 8px;'>";
            }
            ?>

            <?php if ($thought['quote_id'] && $thought['quote_content']): ?>
                <div style="border-left: 3px solid var(--accent); margin-top: 10px; padding-left: 15px; background: rgba(255, 255, 255, 0.02); border-radius: 8px; padding: 12px;">
                    <em><?= $t['quoted_from'] ?> <?= link_username($thought['quote_user']) ?></em><br>
                    <p><?= nl2br(htmlspecialchars($thought['quote_content'])) ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <br><small style="color: rgba(255, 255, 255, 0.6);"><?= $thought['created_at'] ?></small><br>

        <div onclick="event.stopPropagation();" class="action-buttons">
            <form method="POST" action="actions/like.php">
                <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                <button type="submit" class="action-btn like-btn">
                    <span class="icon">❤️</span>
                    <span class="count"><?= $likeCount ?></span>
                </button>
            </form>
            
            <button class="action-btn comment-btn" onclick="openModal(<?= $target_id ?>)">
                <span class="icon">💬</span>
                <span>Comment</span>
            </button>
            
            <form method="POST" action="actions/repost.php">
                <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                <button type="submit" class="action-btn repost-btn">
                    <span class="icon">🔁</span>
                    <span class="count"><?= $repostCount ?></span>
                </button>
            </form>
            
            <?php if (!$thought['quote_id']): ?>
            <button class="action-btn quote-btn" onclick="openQuoteModal(<?= $target_id ?>)">
                <span class="icon">💭</span>
                <span class="count"><?= $quoteCount ?></span>
            </button>
            <?php endif; ?>
            
            <?php if ($_SESSION['user_id'] == $thought['user_id']): ?>
            <form method="POST" action="actions/delete_thought.php">
                <input type="hidden" name="thought_id" value="<?= $thought['id'] ?>">
                <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this post?')">
                    <span class="icon">🗑️</span>
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php
        $comment_stmt = $pdo->prepare("SELECT comments.content, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.thought_id = ? ORDER BY comments.created_at DESC LIMIT 3");
        $comment_stmt->execute([$target_id]);
        foreach ($comment_stmt->fetchAll() as $comment) {
            echo "<div style='margin-top:8px; margin-left:20px; font-size:0.9em; border-left:2px solid var(--accent); padding-left:12px; background: rgba(255, 255, 255, 0.02); border-radius: 4px; padding: 8px 12px;'>";
            echo "<strong>" . render_user_icon($comment['username'], 20) . ":</strong> " . htmlspecialchars($comment['content']);
            echo "</div>";
        }
        ?>
    </div>
    <?php endforeach; ?>

    <form method="GET" style="text-align: center; margin-top: 30px;">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <input type="hidden" name="offset" value="<?= $offset + $limit ?>">
        <button type="submit" class="btn" style="padding: 12px 24px; border-radius: 25px;"><?= $t['load_more'] ?></button>
    </form>
</div>

<!-- COMMENT MODAL -->
<div id="commentModal" style="
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    backdrop-filter: blur(5px);
    justify-content: center;
    align-items: center;
    z-index: 10000;
">
    <div id="modalContent" style="
        background: var(--card);
        color: var(--text);
        padding: 30px;
        width: 90%;
        max-width: 900px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
    "></div>
</div>

<!-- QUOTE MODAL -->
<div id="quoteModal" class="quote-modal">
    <div class="quote-modal-content">
        <h3>Quote this post</h3>
        <form id="quoteForm" method="POST" action="actions/quote.php">
            <input type="hidden" name="thought_id" id="quoteThoughtId">
            <textarea name="content" placeholder="<?= $t['quote_placeholder'] ?>" required></textarea>
            <div class="quote-modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeQuoteModal()">Cancel</button>
                <button type="submit" class="submit-btn">Quote</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(thoughtId) {
    fetch('thought/full_view.php?thought_id=' + thoughtId)
        .then(res => res.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
            document.getElementById('commentModal').style.display = 'flex';
        });
}

function openQuoteModal(thoughtId) {
    document.getElementById('quoteThoughtId').value = thoughtId;
    document.getElementById('quoteModal').style.display = 'flex';
    document.getElementById('quoteModal').classList.add('show');
    // Focus on textarea
    setTimeout(() => {
        document.querySelector('#quoteModal textarea').focus();
    }, 100);
}

function closeQuoteModal() {
    document.getElementById('quoteModal').style.display = 'none';
    document.getElementById('quoteModal').classList.remove('show');
    // Reset form
    document.getElementById('quoteForm').reset();
}

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    if (e.target.id === 'commentModal') {
        document.getElementById('commentModal').style.display = 'none';
    }
    if (e.target.id === 'quoteModal') {
        closeQuoteModal();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('commentModal').style.display = 'none';
        closeQuoteModal();
    }
});

// File input change handlers for better UX
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        const label = this.nextElementSibling;
        if (this.files.length > 0) {
            label.style.background = 'rgba(255, 167, 60, 0.2)';
            label.style.borderColor = 'var(--accent)';
            label.style.color = 'var(--accent)';
        } else {
            label.style.background = 'rgba(255, 255, 255, 0.05)';
            label.style.borderColor = 'rgba(255, 255, 255, 0.1)';
            label.style.color = 'var(--text)';
        }
    });
});
</script>