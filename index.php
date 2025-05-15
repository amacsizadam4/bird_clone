<?php
require 'includes/auth.php';
require 'config.php';
require 'includes/lang.php';
require 'includes/functions.php';
include 'templates/header.php';

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

<!-- WRAPPER LAYOUT -->
<div style="display: flex; justify-content: center; gap: 20px; padding: 0 20px;">
    <div style="flex: 1;"></div>

    <div style="flex: 2; max-width: 600px;">
        <!-- TAB FILTERS -->
        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
            <a href="?filter=recent" style="text-decoration: <?= $filter === 'recent' ? 'underline' : 'none' ?>;"><?= $t['recent'] ?></a>
            <a href="?filter=week" style="text-decoration: <?= $filter === 'week' ? 'underline' : 'none' ?>;"><?= $t['popular_week'] ?></a>
            <a href="?filter=all" style="text-decoration: <?= $filter === 'all' ? 'underline' : 'none' ?>;"><?= $t['popular_all'] ?></a>
        </div>

        <!-- POST FORM -->
        <form method="POST" action="thought/create.php" enctype="multipart/form-data" style="margin-bottom: 20px;">
            <textarea name="content" placeholder="<?= $t['whats_on_your_mind'] ?>" maxlength="280" required></textarea><br><br>
            <input type="file" name="image1" accept="image/*"><br>
            <input type="file" name="image2" accept="image/*"><br>
            <input type="file" name="image3" accept="image/*"><br>
            <input type="file" name="image4" accept="image/*"><br><br>
            <button type="submit"><?= $t['post_button'] ?></button>
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

        <div onclick="openModal(<?= $target_id ?>)"
             style="border: 1px solid #ccc; margin-bottom: 20px; padding: 10px; cursor: pointer; position: relative;">

            <?php if ($thought['original_thought_id']): ?>
                <em><?= $t['reposted_by'] ?> <?= render_user_icon($thought['username'], 30) ?></em><br>
                <?php if ($thought['original_content'] && $thought['original_username']): ?>
                    <strong><?= render_user_icon($thought['original_username'], 20) ?></strong><br>
                    <p><?= nl2br(htmlspecialchars($thought['original_content'])) ?></p>
                <?php else: ?>
                    <p><i><?= $t['post_deleted_or_unavailable'] ?></i></p>
                <?php endif; ?>
            <?php else: ?>
                <strong><?= render_user_icon($thought['username'], 30) ?></strong><br>
                <p><?= nl2br(htmlspecialchars($thought['content'])) ?></p>

                <?php if ($thought['quote_id'] && $thought['quote_content']): ?>
                    <div style="border-left: 3px solid #ccc; margin-top: 10px; padding-left: 10px;">
                        <em><?= $t['quoted_from'] ?> <?= render_user_icon($thought['quote_user'], 20) ?></em><br>
                        <p><?= nl2br(htmlspecialchars($thought['quote_content'])) ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php
            $stmt2 = $pdo->prepare("SELECT image_path FROM thought_images WHERE thought_id = ?");
            $stmt2->execute([$thought['id']]);
            foreach ($stmt2->fetchAll() as $img) {
                echo "<img src='uploads/" . htmlspecialchars($img['image_path']) . "' width='150' style='margin: 5px;'>";
            }
            ?>

            <br><small><?= $thought['created_at'] ?></small><br><br>

            <div onclick="event.stopPropagation();" style="margin-top: 10px; display: flex; align-items: center; gap: 10px;">
                <form method="POST" action="actions/like.php">
                    <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                    <button type="submit">❤️ <?= $likeCount ?></button>
                </form>
                <form method="POST" action="actions/repost.php">
                    <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                    <button type="submit">🔁 <?= $repostCount ?></button>
                </form>
                <?php if (!$thought['quote_id']): ?>
                <form method="POST" action="actions/quote.php">
                    <input type="hidden" name="thought_id" value="<?= $target_id ?>">
                    <input type="text" name="content" placeholder="<?= $t['quote_placeholder'] ?>" required>
                    <button type="submit">💬 <?= $quoteCount ?></button>
                </form>
                <?php endif; ?>
                <?php if ($_SESSION['user_id'] == $thought['user_id']): ?>
                <form method="POST" action="actions/delete_thought.php">
                    <input type="hidden" name="thought_id" value="<?= $thought['id'] ?>">
                    <button type="submit">🗑️</button>
                </form>
                <?php endif; ?>
            </div>

            <?php
            $comment_stmt = $pdo->prepare("
                SELECT comments.content, comments.deleted, users.username
                FROM comments
                JOIN users ON comments.user_id = users.id
                WHERE comments.thought_id = ?
                ORDER BY comments.created_at DESC
                LIMIT 3
            ");
            $comment_stmt->execute([$target_id]);
            foreach ($comment_stmt->fetchAll() as $comment) {
                echo "<div style='margin-top:5px; margin-left:20px; font-size:0.9em; border-left:2px solid #ccc; padding-left:10px;'>";
                echo "<strong>" . render_user_icon($comment['username'], 20) . ":</strong> ";
                echo $comment['deleted'] ? "<em>{$t['comment_deleted']}</em>" : htmlspecialchars($comment['content']);
                echo "</div>";
            }
            ?>
        </div>
        <?php endforeach; ?>

        <form method="GET" style="text-align: center;">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <input type="hidden" name="offset" value="<?= $offset + $limit ?>">
            <button type="submit"><?= $t['load_more'] ?></button>
        </form>
    </div>

    <div style="flex: 1;"></div>
</div>

<!-- MODAL -->
<div id="commentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
     background: rgba(0,0,0,0.7); justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; max-width:600px; max-height:80%; overflow:auto;" id="modalContent"></div>
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
