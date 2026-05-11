<?php
session_start();
require_once '../../model/adminModel/announcementsModel.php';
require_once '../../model/connection.php';

$conn = conn_open();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_announcement'])) {
    postAnnouncement($_POST['title'], $_POST['content'],$conn);
}

$announcements = getAnnouncements($conn);
conn_close($conn);

$_SESSION['announcements'] = $announcements;

header("Location: ../../views/admin/announcements.php");
exit();
?>