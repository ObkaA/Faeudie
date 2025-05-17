<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once "db.php";
    require_once "sesja.php";
    $komunikat = '';
    $typKomunikatu = '';


    if(isLoggedIn())
    {
        logoutUser();
        header("Location: index.php");
        exit();
    }
    

    if ($_SERVER["REQUEST_METHOD"] === "POST") 
    {
        // Pobranie danych z formularza
        $login = $_POST['login'] ?? '';
        $haslo = $_POST['haslo'] ?? '';

        //echo '<p style="color: blue;">Login: ' . htmlspecialchars($login) . '</p>';
        //echo '<p style="color: blue;">Hasło: ' . htmlspecialchars($haslo) . '</p>';

        $stmt = $conn->prepare("SELECT id, password_hashed FROM users WHERE login = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user) 
        {
            if(password_verify($haslo, $user['password_hashed'])) {
                loginUser($user['id'], $login);
                $komunikat='Zalogowano';
                $typKomunikatu='sukces';
            }
            else
            {
                $komunikat='Hasło nie pasuje do loginu';
                $typKomunikatu='blad';
            }
        }
        else
        {
            $komunikat='Nie ma takiego zarejestrowanego użytkownika';
            $typKomunikatu='blad';
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

    <div id="menu">
        <div class="menu-item"><a href="index.php">Strona Główna</a></div>
        <div class="menu-item"><a href="wszystkie_przepisy.php">Wszystkie Przepisy</a></div>
        <div class="menu-item"><a href="dodaj_przepis.php">Dodaj Przepis</a></div>
        <div class="menu-item"><a href="zaloguj_sie.php"> <?php require_once 'sesja.php'; echo get_loginout_mes() ?> </a></div>
    </div>

    <div class="login-form-wrapper">
        <h2>Zaloguj się</h2>

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

            <input type="submit" value="Zaloguj się">
        </form>

        <p class="register-link">
            Nie masz konta? <a href="rejestracja.php">Zarejestruj się</a>
        </p>
    </div>
</body>
</html>
