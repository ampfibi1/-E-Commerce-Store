<?php

function login($email, $password,$conn){
    $sql = "SELECT id, name, role, password_hash FROM users WHERE email = '$email' AND role = 'admin'";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)){
        if ($password === $row['password_hash']){
            return $row;
        }
    }
    return false;
}

?>
