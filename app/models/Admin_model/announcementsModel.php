<?php
function getAnnouncements($conn) {
    $sql = "SELECT id, title, content, created_at FROM announcements ORDER BY created_at DESC";
    $result = mysqli_query($conn, $sql);
    $announcements = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $announcements[] = $row;
    }
    return $announcements;
}

function postAnnouncement($title,$content,$conn) {
    $sql = "INSERT INTO announcements (title, content, created_at) VALUES ('$title', '$content', NOW())";
    mysqli_query($conn, $sql);
}
?>