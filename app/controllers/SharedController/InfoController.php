<?php
// ============================================================
// app/controllers/SharedController/InfoController.php
// Procedural controller — no classes (per CONTRIBUTING.md rule).
// Every public action is a function named "info_<action>" so the
// router can dispatch ?c=info&a=help → info_help($conn).
// $conn is passed in by the router; never accessed via $this.
// ============================================================

function info_help($conn) {
    $page_title = 'Help Center';
    include APP . '/views/info/help.php';
}

function info_contact($conn) {
    $page_title = 'Contact Us';
    include APP . '/views/info/contact.php';
}

function info_terms($conn) {
    $page_title = 'Terms & Privacy';
    include APP . '/views/info/terms.php';
}

// Smart redirect: send customers to their dispute page, sellers to theirs,
// and unauthenticated visitors to login first.
function info_disputes($conn) {
    if (!isset($_SESSION['uid'])) {
        set_flash('info', 'Please log in to open or view disputes.');
        redirect(BASE_URL . '?c=auth&a=login');
    }
    if ($_SESSION['role'] === 'seller') {
        redirect(BASE_URL . '?c=seller&a=disputes');
    }
    redirect(BASE_URL . '?c=customer&a=disputes');
}
