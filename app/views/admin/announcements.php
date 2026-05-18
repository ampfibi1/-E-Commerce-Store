<?php
session_start();

$announcements = $_SESSION['announcements'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">

<body>
    <?php include 'sidebar.php'; ?>
    <div class="main_content" >
        <h1>Platform Announcements</h1>
        <form action="<?php echo BASE_URL; ?>?c=admin&amp;a=announcements" method="post">
            <h3>Post New Announcement</h3>
            <input type="text" name="title" placeholder="Title">
            <textarea name="content" placeholder="Content"></textarea>
            <button type="submit" name="post_announcement">Post</button>
        </form>
        <h2>Recent Announcements</h2>
        <?php foreach ($announcements as $ann): ?>
        <div class="announcement">
            <h3><?= htmlspecialchars($ann['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($ann['content'])) ?></p>
            <small>Posted on <?= $ann['created_at'] ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>