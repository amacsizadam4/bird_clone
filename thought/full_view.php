<?php
require '../includes/auth.php';
require '../config.php';
require '../includes/lang.php';
require '../includes/functions.php';

$thought_id = $_GET['thought_id'] ?? 0;

// Fetch main thought
$stmt = $pdo->prepare("
    SELECT t.*, u.username, u.profile_pic, u.bio,
           qt.content AS quote_content,
           qu.username AS quote_user
    FROM thoughts t
    JOIN users u ON t.user_id = u.id
    LEFT JOIN thoughts qt ON t.quote_id = qt.id
    LEFT JOIN users qu ON qt.user_id = qu.id
    WHERE t.id = ?
");
$stmt->execute([$thought_id]);
$thought = $stmt->fetch();

if (!$thought) {
    echo "<p>{$t['post_deleted_or_unavailable']}</p>";
    exit;
}

// Main thought display
echo "<div style='margin-bottom: 10px;'>";
echo render_user_icon($thought['username'], 40);
if (!empty($thought['bio'])) {
    echo "<p style='margin-top: 5px; font-size: 0.9em;'>" . nl2br(htmlspecialchars($thought['bio'])) . "</p>";
}
echo "</div>";

echo "<p>" . nl2br(htmlspecialchars($thought['content'])) . "</p>";

if ($thought['quote_id'] && $thought['quote_content']) {
    echo "<div style='border-left: 3px solid #ccc; margin: 10px 0; padding-left: 10px;'>";
    echo "<em>{$t['quoted_from']} " . link_username($thought['quote_user']) . "</em><br>";
    echo "<p>" . nl2br(htmlspecialchars($thought['quote_content'])) . "</p>";
    echo "</div>";
}

// Images
$img_stmt = $pdo->prepare("SELECT image_path FROM thought_images WHERE thought_id = ?");
$img_stmt->execute([$thought_id]);
foreach ($img_stmt->fetchAll() as $img) {
    echo "<img src='uploads/" . htmlspecialchars($img['image_path']) . "' width='100' style='margin: 5px 0;'><br>";
}

// COMMENT TREE
function render_comments($pdo, $thought_id, $parent_id = null, $depth = 0) {
    global $t;
    $stmt = $pdo->prepare("
        SELECT c.*, u.username
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.thought_id = ? AND c.parent_comment_id " . ($parent_id ? "= ?" : "IS NULL") . "
        ORDER BY c.created_at ASC
    ");
    $stmt->execute($parent_id ? [$thought_id, $parent_id] : [$thought_id]);

    while ($c = $stmt->fetch()) {
        echo "<div style='margin-left:" . (20 * $depth) . "px; border-left:1px solid #ccc; padding-left:10px; margin-top:5px;'>";

        if ($c['deleted']) {
            echo "<em>{$t['comment_deleted']}</em>";
        } else {
            echo "<strong>" . link_username($c['username']) . "</strong>: " . nl2br(htmlspecialchars($c['content']));
        }

        if (!$c['deleted']) {
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

echo "<hr><h4>{$t['comments']}</h4>";
render_comments($pdo, $thought_id);
?>

<!-- New root-level comment -->
<hr>
<form method="POST" action="thought/comment.php">
    <input type="hidden" name="thought_id" value="<?= $thought_id ?>">
    <textarea name="content" required placeholder="<?= $t['comment_placeholder'] ?>" maxlength="280"></textarea><br>
    <button type="submit"><?= $t['comment_button'] ?></button>
</form>
