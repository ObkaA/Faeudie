<?php
    require_once 'sesja.php';
?>

<div id="menu">
        <div class="menu-item"><a href="index.php">Strona Główna</a></div>
        <div class="menu-item"><a href="wszystkie_przepisy.php">Wszystkie Przepisy</a></div>
        <div class="menu-item"><a href="dodaj_przepis.php">Dodaj Przepis</a></div>
        <?php if (isLoggedIn()): ?>
            <div class="menu-item"><a href="lodowka.php">Lodówka</a></div>
        <?php endif; ?>

        <div class="menu-item"><a href="zaloguj_sie.php"> <?php require_once 'sesja.php'; echo get_loginout_mes() ?> </a></div> 
</div>