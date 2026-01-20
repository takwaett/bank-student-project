<?php
// Définir le type de contenu et l'encodage
header('Content-Type: text/html; charset=utf-8');

// Démarrer la session pour les messages
session_start();

// Message de retour
$message = '';

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    
    // Configuration de la base de données
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $dbname = 'user_management';
    
    // Connexion à la base de données
    $conn = mysqli_connect($host, $user, $pass);
    
    if (!$conn) {
        $message = '<div class="error-message">❌ Erreur de connexion au serveur MySQL</div>';
    } else {
        // Créer la base de données si elle n'existe pas
        $create_db = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if (!mysqli_query($conn, $create_db)) {
            $message = '<div class="error-message">❌ Erreur lors de la création de la base de données</div>';
        } else {
            // Sélectionner la base de données
            mysqli_select_db($conn, $dbname);
            
            // Créer la table si elle n'existe pas
            $create_table = "CREATE TABLE IF NOT EXISTS utilisateurs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nom VARCHAR(50) NOT NULL,
                prenom VARCHAR(50) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            
            if (!mysqli_query($conn, $create_table)) {
                $message = '<div class="error-message">❌ Erreur lors de la création de la table</div>';
            } else {
                // Définir l'encodage UTF-8
                mysqli_set_charset($conn, "utf8mb4");
                
                // Récupérer et nettoyer les données du formulaire
                $nom = mysqli_real_escape_string($conn, trim($_POST['nom'] ?? ''));
                $prenom = mysqli_real_escape_string($conn, trim($_POST['prenom'] ?? ''));
                $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
                $password = $_POST['password'] ?? '';
                
                // Validation des données
                $errors = [];
                
                if (empty($nom)) {
                    $errors[] = "Le nom est requis";
                }
                
                if (empty($prenom)) {
                    $errors[] = "Le prénom est requis";
                }
                
                if (empty($email)) {
                    $errors[] = "L'email est requis";
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Format d'email invalide";
                }
                
                if (empty($password)) {
                    $errors[] = "Le mot de passe est requis";
                } elseif (strlen($password) < 8) {
                    $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
                }
                
                // Si pas d'erreurs
                if (empty($errors)) {
                    // Vérifier si l'email existe déjà
                    $check_sql = "SELECT id FROM utilisateurs WHERE email = ?";
                    $check_stmt = mysqli_prepare($conn, $check_sql);
                    
                    if ($check_stmt) {
                        mysqli_stmt_bind_param($check_stmt, "s", $email);
                        mysqli_stmt_execute($check_stmt);
                        mysqli_stmt_store_result($check_stmt);
                        
                        if (mysqli_stmt_num_rows($check_stmt) > 0) {
                            $message = '<div class="error-message">❌ Cet email est déjà utilisé</div>';
                        } else {
                            mysqli_stmt_close($check_stmt);
                            
                            // Hacher le mot de passe
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);
                            
                            // Insérer dans la base de données
                            $sql = "INSERT INTO utilisateurs (nom, prenom, email, password) 
                                    VALUES (?, ?, ?, ?)";
                            
                            $stmt = mysqli_prepare($conn, $sql);
                            
                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, "ssss", $nom, $prenom, $email, $password_hash);
                                
                                if (mysqli_stmt_execute($stmt)) {
                                    $message = '<div class="success-message">✅ Compte créé avec succès !</div>';
                                    
                                    // Redirection vers la page de connexion après 3 secondes
                                    echo '<script>
                                        setTimeout(function() {
                                            window.location.href = "index.html#login-form";
                                        }, 3000);
                                    </script>';
                                } else {
                                    $message = '<div class="error-message">❌ Erreur lors de la création du compte: ' . mysqli_error($conn) . '</div>';
                                }
                                
                                mysqli_stmt_close($stmt);
                            } else {
                                $message = '<div class="error-message">❌ Erreur de préparation de la requête: ' . mysqli_error($conn) . '</div>';
                            }
                        }
                        if (isset($stmt)) {
                            mysqli_stmt_close($stmt);
                        }
                    } else {
                        $message = '<div class="error-message">❌ Erreur de préparation de la requête de vérification</div>';
                    }
                } else {
                    $message = '<div class="error-message">❌ ' . implode('<br>', $errors) . '</div>';
                }
            }
        }
        
        mysqli_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Bank for Students</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        
        .message-container {
            margin-top: 20px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Création de compte</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message-container">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="nom" placeholder="Nom" required 
                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="text" name="prenom" placeholder="Prénom" required
                       value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="email" name="email" placeholder="Email étudiant" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Mot de passe (min. 8 caractères)" required>
            </div>
            
            <button type="submit" name="register" class="btn-primary">
                S'inscrire
            </button>
        </form>
        
        <a href="index.html" class="back-link">← Retour à l'accueil</a>
    </div>
</body>
</html>