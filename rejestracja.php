<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once "db.php";
    //require_once 'sesja.php'; 

    $komunikat = '';
    $typKomunikatu = '';

    if ($_SERVER["REQUEST_METHOD"] === "POST") 
    {
        $login = $_POST['login'] ?? '';
        $haslo = $_POST['haslo'] ?? '';

        //sprawdzenie czy już nie ma takiego loginu
        $stmt = $conn->prepare("SELECT 1 FROM users WHERE login = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        if($stmt->fetch()) 
        {
            $komunikat='Podany login już istnieje';
            $typKomunikatu='blad';
        }
        else
        {
            if (strlen($haslo) < 4) 
            {
                $komunikat = 'Hasło musi mieć co najmniej 4 znaków.';
                $typKomunikatu = 'blad';
            } 
            else 
            {
                $password_hashed = password_hash($haslo, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO users (login, password_hashed) VALUES (:login, :password)");
                $stmt->execute([':login' => $login,':password' => $password_hashed]);


                $komunikat = 'Zarejestrowano pomyślnie!';
                $typKomunikatu = 'sukces';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faeudie</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body id="login-page">
    <h1>Faeudie</h1>

    <?php include 'menu.php'; ?>

    <div class="login-form-wrapper">
        <h2>Zarejestruj się</h2>

        <form class="login-form" method="POST">
            <label for="login">Login:</label>
            <input type="text" name="login" id="login" required>

            <label for="haslo">Hasło:</label>
            <input type="password" name="haslo" id="haslo" required>

            <?php if ($komunikat): ?>
                <div class="komunikat_rejestracji <?= $typKomunikatu ?>">
                    <?= htmlspecialchars($komunikat) ?>
                </div>
            <?php endif; ?>

            <input type="submit" value="Zarejestruj">
        </form>
    </div>
</body>
</html>