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
<div class="container">
    <div class="tabs">
        <a href="?filter=recent" class="<?= $filter === 'recent' ? 'active' : '' ?>"><?= $t['recent'] ?></a>
        <a href="?filter=week" class="<?= $filter === 'week' ? 'active' : '' ?>"><?= $t['popular_week'] ?></a>
        <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>"><?= $t['popular_all'] ?></a>
    </div>

    <form method="POST" action="thought/create.php" enctype="multipart/form-data" class="card">
        <textarea name="content" placeholder="<?= $t['whats_on_your_mind'] ?>" maxlength="280" required></textarea>
        <input type="file" name="image1" accept="image/*">
        <input type="file" name="image2" accept="image/*">
        <input type="file" name="image3" accept="image/*">
        <input type="file" name="image4" accept="image/*">
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
        $likeCount = getThoughtLikesCount($target_id);
        $repostCount = getThoughtRepostsCount($target_id);
        $quoteCount = $pdo->prepare("SELECT COUNT(*) FROM thoughts WHERE quote_id = ?");
        $quoteCount->execute([$target_id]);
        $quoteCount = $quoteCount->fetchColumn();
    ?>

    <div onclick="openModal(<?= $target_id ?>)" class="card" style="cursor: pointer;">
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
            $images = getImagesForThought($thought['id']);
            foreach ($images as $img) {
                echo "<img src='uploads/" . htmlspecialchars($img['image_path']) . "' width='150' style='margin: 5px;'>";
            }
            ?>

            <?php if ($thought['quote_id'] && $thought['quote_content']): ?>
                <div style="border-left: 3px solid #ccc; margin-top: 10px; padding-left: 10px;">
                    <em><?= $t['quoted_from'] ?> <?= link_username($thought['quote_user']) ?></em><br>
                    <p><?= nl2br(htmlspecialchars($thought['quote_content'])) ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <br><small><?= $thought['created_at'] ?></small><br><br>

        <div onclick="event.stopPropagation();" style="margin-top: 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <form method="POST" action="actions/like.php">
                <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                <button type="submit" class="btn">❤️ <?= $likeCount ?></button>
            </form>
            <form method="POST" action="actions/repost.php">
                <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                <button type="submit" class="btn">🔁 <?= $repostCount ?></button>
            </form>
            <?php if (!$thought['quote_id']): ?>
            <form method="POST" action="actions/quote.php" style="display: flex; gap: 5px;">
                <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                <input type="text" name="content" placeholder="<?= $t['quote_placeholder'] ?>" required>
                <button type="submit" class="btn">💬 <?= $quoteCount ?></button>
            </form>
            <?php endif; ?>
            <?php if ($_SESSION['user_id'] == $thought['user_id']): ?>
            <form method="POST" action="actions/delete_thought.php">
                <input type="hidden" name="thought_id" value="<?= $thought['id'] ?>">
                <button type="submit" class="btn">🗑️</button>
            </form>
            <?php endif; ?>
        </div>

        <?php
        $comments = getCommentsByThought($target_id);
        foreach (array_slice($comments, 0, 3) as $comment) {
            echo "<div style='margin-top:5px; margin-left:20px; font-size:0.9em; border-left:2px solid var(--border); padding-left:10px;'>";
            echo "<strong>" . render_user_icon($comment['username'], 20) . ":</strong> " . htmlspecialchars($comment['content']);
            echo "</div>";
        }
        ?>
    </div>
    <?php endforeach; ?>

    <form method="GET" style="text-align: center;">
        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <input type="hidden" name="offset" value="<?= $offset + $limit ?>">
        <button type="submit" class="btn"><?= $t['load_more'] ?></button>
    </form>
</div>

<!-- MODAL -->
<div id="commentModal" style="
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
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
        border-radius: 12px;
        box-shadow: 0 0 20px rgba(0,0,0,0.6);
    "></div>
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
window.addEventListener('click', function(e) {
    if (e.target.id === 'commentModal') {
        document.getElementById('commentModal').style.display = 'none';
    }
});
</script>
