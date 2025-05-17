<?php

    require_once "db.php";

    if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
        echo "nieprawidlowy id przepisu";
        exit;
    }

    $recipe_id = $_GET['id'];

    $stmt = $conn->prepare (" SELECT r.title, r.instructions, r.calories, r.portions, mc.name_category 
        FROM recipes r
        JOIN meal_categories mc ON r.category = mc.id
        WHERE r.id = :id");

    $stmt->execute([':id'=>$recipe_id]);
    $recipe =$stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        echo "Nie znaleziono przepisu.";
        exit;
    }

    $stmt = $conn->prepare("
    SELECT i.name AS ingredient_name, ri.amount, u.name AS unit_name
    FROM recipeingredients ri
    JOIN ingredients i ON ri.ingredient_id = i.id
    JOIN units u ON ri.unit = u.id
    WHERE ri.recipe_id = :id
    ");
    $stmt->execute([':id' => $recipe_id]);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pobranie komentarzy do przepisu
    $stmt = $conn->prepare("SELECT c.comment, c.time_created, u.login 
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.recipe_id = :id
    ORDER BY c.time_created DESC");
    $stmt->execute([':id' => $recipe_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as rating_count FROM ratings WHERE recipe_id = :id");
    $stmt->execute([':id' => $recipe_id]);
    $ratingData = $stmt->fetch(PDO::FETCH_ASSOC);

    $avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 2) : 0;
    $ratingCount = $ratingData['rating_count'];
    
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta charset="UTF-8">
    <title>Wszystkie Przepisy - Faeudie</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
<h1>Faeudie</h1>

<?php include 'menu.php'; ?>

<div class="form-wrapper">
    <h2><?= htmlspecialchars($recipe['title']) ?></h2>
    <p><strong>Kategoria:</strong> <?=htmlspecialchars($recipe['name_category']) ?> </p>
    <p><strong>Kalorie:</strong> <?=$recipe['calories'] ?> </p>
    <p><strong>Liczba porcji:</strong> <?=$recipe['portions'] ?> </p>
    
    <!-- Wyświetlenie średniej oceny -->
    <div class="average-rating">
        <p>Średnia ocena: <strong><?= $avgRating ?></strong> (na podstawie <?= $ratingCount ?> ocen)</p>
    </div>

    <div class="average-stars">
        <?php
        $fullStars = floor($avgRating);
        $halfStar = ($avgRating - $fullStars) >= 0.5 ? true : false;
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $fullStars) {
                echo '<span style="color: gold;">★</span>';
            } elseif ($i == $fullStars + 1 && $halfStar) {
                echo '<span style="color: gold;">☆</span>'; // prosty symbol pół gwiazdki
            } else {
                echo '<span style="color: #ccc;">★</span>';
            }
        }
        ?>
    </div>

    <h2>Składniki</h2>
    <ul>
        <?php foreach ($ingredients as $item): ?>
            <li><?= htmlspecialchars($item['ingredient_name']) ?> - <?=$item['amount'] ?> <?=htmlspecialchars($item['unit_name']) ?> </li>
        <?php endforeach; ?>
    </ul>

    <h2>Instrukcje</h2>
    <p><?= nl2br(htmlspecialchars($recipe['instructions'])) ?></p>

    <h2>Komentarze</h2>
    <?php if (!$comments): ?>
       <? echo "<p>Brak komentarzy. Bądź pierwszy!</p>"; ?>

    <?php else: ?>
        <ul>
        <?php foreach ($comments as $c): ?>
            <li>
                <strong><?=htmlspecialchars($c['login'])?></strong> (<?=htmlspecialchars($c['time_created'])?>):<br>
                <?=nl2br(htmlspecialchars($c['comment']))?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
        <h3>Dodaj komentarz</h3>
        <form method="post" action="dodaj_komentarz.php">
            <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
            <textarea name="comment" rows="4" cols="50" required></textarea><br>
            <button type="submit">Dodaj komentarz</button>
        </form>
    <?php else: ?>
        <p><a href="zaloguj_sie.php">Zaloguj się</a>, aby dodać komentarz.</p>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
        <div class="rating-form">
            <h3>Oceń ten przepis</h3>
            <form action="dodaj_ocene.php" method="POST">
                <input type="hidden" name="recipe_id" value="<?= htmlspecialchars($recipe_id) ?>">
                
                <div class="star-rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                        <label for="star<?= $i ?>">★</label>
                    <?php endfor; ?>
                </div>

                <button type="submit">Oceń</button>
            </form>
        </div>
    <?php else: ?>
        <p><em>Zaloguj się, aby ocenić przepis.</em></p>
    <?php endif; ?>

</div>
</body>
</html>