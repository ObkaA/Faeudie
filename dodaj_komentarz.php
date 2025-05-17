<?php
    require_once 'db.php';
    require_once 'sesja.php';

    if (!isLoggedIn()) {
        header("Location: zaloguj_sie.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_SESSION['user_id'];
        $recipe_id = $_POST['recipe_id'] ?? null;
        $comment = trim($_POST['comment'] ?? '');
    
        if (!$recipe_id || !$comment) {
            // Błąd - brak danych
            header("Location: przepis.php?id=" . intval($recipe_id));
            exit();
        }
    
        $stmt = $conn->prepare("INSERT INTO comments (user_id, recipe_id, comment, time_created) VALUES (:user_id, :recipe_id, :comment, NOW())");
        $stmt->execute([
            ':user_id' => $user_id,
            ':recipe_id' => $recipe_id,
            ':comment' => $comment
        ]);
    
        header("Location: przepis.php?id=" . intval($recipe_id));
        exit();
    }
?>