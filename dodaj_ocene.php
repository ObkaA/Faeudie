<?php
session_start();
require_once 'db.php';      // połączenie z bazą
require_once 'sesja.php';   // funkcje do obsługi sesji (isLoggedIn())

if (!isLoggedIn()) {
    header("Location: zaloguj_sie.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: zaloguj_sie.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe_id = $_POST['recipe_id'] ?? null;
    $rating = $_POST['rating'] ?? null;

    if (!$recipe_id || !is_numeric($recipe_id) || !$rating || !in_array($rating, ['1','2','3','4','5'])) {
        // niepoprawne dane, można dodać komunikat
        header("Location: przepis.php?id=" . urlencode($recipe_id));
        exit();
    }

    try {
        // Sprawdzamy, czy już jest ocena tego usera dla tego przepisu
        $stmt = $conn->prepare("SELECT id FROM ratings WHERE user_id = :user_id AND recipe_id = :recipe_id");
        $stmt->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Aktualizujemy ocenę
            $stmt = $conn->prepare("UPDATE ratings SET rating = :rating WHERE id = :id");
            $stmt->execute([':rating' => $rating, ':id' => $existing['id']]);
        } else {
            // Dodajemy nową ocenę
            $stmt = $conn->prepare("INSERT INTO ratings (user_id, recipe_id, rating) VALUES (:user_id, :recipe_id, :rating)");
            $stmt->execute([':user_id' => $user_id, ':recipe_id' => $recipe_id, ':rating' => $rating]);
        }
    } catch (PDOException $e) {
        // Opcjonalnie: loguj błąd lub wyświetl komunikat
        // echo "Błąd: " . $e->getMessage();
    }

    // Po dodaniu/aktualizacji wracamy do przepisu
    header("Location: przepis.php?id=" . urlencode($recipe_id));
    exit();
}

// Jeśli metoda nie POST, przekieruj na główną
header("Location: index.php");
exit();