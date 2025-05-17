<?php
if(session_status() === PHP_SESSION_NONE) 
{
    session_start();
}


// Funkcja do logowania użytkownika
function loginUser($user_id, $login) {
    $_SESSION['user_id'] = $user_id;//nie potrzebne nam
    $_SESSION['login'] = $login;
    //echo '<p style="color: blue;">Hasło: ' . htmlspecialchars('logged') . '</p>';
}

// Funkcja do sprawdzania, czy użytkownik jest zalogowany
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function get_loginout_mes(){
    if(isLoggedIn()){
        return 'Wyloguj się';
    }else{
        return 'Zaloguj się';
    }
}

// Funkcja do wylogowania użytkownika
function logoutUser() {
    // Usuwamy dane sesji
    session_unset();
    session_destroy();
}
?>