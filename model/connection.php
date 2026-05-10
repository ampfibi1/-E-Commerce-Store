<?php
    function connectDB() { 
       $host = "localhost";
       $username = "your_username";
       $password = "your_password";
       $database = "your_database";

       $conn = new mysqli($host, $username, $password, $database);

       if ($conn->connect_error) {
           die("Connection failed: " . mysqli_connect_error());
       }
       return $conn;
    }

    function closeDB($conn) {
        mysqli_close($conn);
    }
?>