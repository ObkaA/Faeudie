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
    
    <h2>Składniki</h2>
    <ul>
        <?php foreach ($ingredients as $item): ?>
            <li><?= htmlspecialchars($item['ingredient_name']) ?> - <?=$item['amount'] ?> <?=htmlspecialchars($item['unit_name']) ?> </li>
        <?php endforeach; ?>
    </ul>

    <h2>Instrukcje</h2>
    <p><?= nl2br(htmlspecialchars($recipe['instructions'])) ?></p>

</div>
</body>
</html>