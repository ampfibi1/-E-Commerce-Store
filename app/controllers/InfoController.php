<?php

class InfoController {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function help() {
        $page_title = 'Help Center';
        include APP . '/views/info/help.php';
    }

    public function contact() {
        $page_title = 'Contact Us';
        include APP . '/views/info/contact.php';
    }

    public function terms() {
        $page_title = 'Terms & Privacy';
        include APP . '/views/info/terms.php';
    }

    public function disputes() {
        // Smart redirect: send customers to their dispute page, sellers to theirs,
        // and unauthenticated visitors to login first.
        if (!isset($_SESSION['uid'])) {
            set_flash('info', 'Please log in to open or view disputes.');
            redirect(BASE_URL . '?c=auth&a=login');
        }
        if ($_SESSION['role'] === 'seller') {
            redirect(BASE_URL . '?c=seller&a=disputes');
        }
        redirect(BASE_URL . '?c=customer&a=disputes');
    }
}
