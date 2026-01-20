<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'user_management';
    
    $conn = mysqli_connect($host, $user, $pass, $dbname);
    
    if ($conn) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        
        $sql = "SELECT id, nom, prenom, password FROM utilisateurs WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $nom, $prenom, $hashed_password);
        mysqli_stmt_fetch($stmt);
        
        if ($id && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $nom . ' ' . $prenom;
            header('Location: index.html#account-space');
            exit();
        } else {
            echo "<script>alert('Email ou mot de passe incorrect'); window.location.href='index.html#login-form';</script>";
        }
        
        mysqli_close($conn);
    }
}
?>