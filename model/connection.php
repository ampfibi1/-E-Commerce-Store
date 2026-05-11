<?php
    function conn_open() { 
       $host = "localhost";
       $username = "root";
       $password = "";
       $database = "ecommerce";

       $conn = new mysqli($host, $username, $password, $database);

       if (!$conn) die("Connection failed: " . mysqli_connect_error());
       return $conn;
    }

    function conn_close($conn) {
        mysqli_close($conn);
    }
?>