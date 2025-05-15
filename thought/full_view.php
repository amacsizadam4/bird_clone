<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';
require '../includes/functions.php';

$thought_id = $_GET['thought_id'] ?? 0;

$stmt = $pdo->prepare("SELECT t.*, u.username, u.profile_pic, u.bio, qt.content AS quote_content, qu.username AS quote_user FROM thoughts t JOIN users u ON t.user_id = u.id LEFT JOIN thoughts qt ON t.quote_id = qt.id LEFT JOIN users qu ON qt.user_id = qu.id WHERE t.id = ?");
$stmt->execute([$thought_id]);
$thought = $stmt->fetch();

if (!$thought) {
    echo "<div class='card'><p>{$t['post_deleted_or_unavailable']}</p></div>";
    exit;
}

?>
<div class="card" style="max-width: 700px; margin: auto;">
    <div class="card" style="margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <?= render_user_icon($thought['username'], 50) ?>
            <div>
                <strong><?= htmlspecialchars($thought['username']) ?></strong><br>
                <?php if (!empty($thought['bio'])): ?>
                    <small><?= nl2br(htmlspecialchars($thought['bio'])) ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <p style="font-size: 1.2em;"><?= nl2br(htmlspecialchars($thought['content'])) ?></p>

    <?php if ($thought['quote_id'] && $thought['quote_content']): ?>
        <div style="border-left: 3px solid #ccc; margin: 15px 0; padding-left: 10px;">
            <em><?= $t['quoted_from'] ?> <?= link_username($thought['quote_user']) ?></em><br>
            <p><?= nl2br(htmlspecialchars($thought['quote_content'])) ?></p>
        </div>
    <?php endif; ?>

    <?php
    $img_stmt = $pdo->prepare("SELECT image_path FROM thought_images WHERE thought_id = ?");
    $img_stmt->execute([$thought_id]);
    foreach ($img_stmt->fetchAll() as $img) {
        echo "<img src='uploads/" . htmlspecialchars($img['image_path']) . "' width='100%' style='max-width:500px; margin:10px 0;'>";
    }
    ?>
</div>

<hr>
<h3><?= $t['comments'] ?></h3>
<div class="card" style="padding: 20px;">
<?php
function render_comments($pdo, $thought_id, $parent_id = null, $depth = 0) {
    global $t;
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.thought_id = ? AND c.parent_comment_id " . ($parent_id ? "= ?" : "IS NULL") . " ORDER BY c.created_at ASC");
    $stmt->execute($parent_id ? [$thought_id, $parent_id] : [$thought_id]);

    while ($c = $stmt->fetch()) {
        echo "<div class='card' style='margin-left: " . (20 * $depth) . "px;'>";
        if ($c['deleted']) {
            echo "<em>{$t['comment_deleted']}</em>";
        } else {
            echo "<strong>" . link_username($c['username']) . "</strong>: " . nl2br(htmlspecialchars($c['content']));

            if ($_SESSION['user_id'] == $c['user_id']) {
                echo "<form method='POST' action='actions/delete_comment.php' style='display:inline; margin-left:10px;'>
                        <input type='hidden' name='comment_id' value='" . $c['id'] . "'>
                        <button type='submit' onclick='return confirm(\"{$t['delete_comment_confirm']}\")'>🗑️</button>
                      </form>";
            }

            echo "<form method='POST' action='thought/comment.php' style='margin-top:5px;'>
                    <input type='hidden' name='thought_id' value='" . $thought_id . "'>
                    <input type='hidden' name='parent_comment_id' value='" . $c['id'] . "'>
                    <input type='text' name='content' placeholder='{$t['reply_placeholder']}' required>
                    <button type='submit'>{$t['reply']}</button>
                  </form>";
        }

        render_comments($pdo, $thought_id, $c['id'], $depth + 1);
        echo "</div>";
    }
}

render_comments($pdo, $thought_id);
?>
</div>

<hr>
<form method="POST" action="thought/comment.php">
    <input type="hidden" name="thought_id" value="<?= $thought_id ?>">
    <textarea name="content" required placeholder="<?= $t['comment_placeholder'] ?>" maxlength="280"></textarea><br>
    <button type="submit" class="btn">💬 <?= $t['comment_button'] ?></button>
</form>
