<?php
require_once 'db.php';
$recipes =$conn->query("
    SELECT r.id, r.title, r.instructions, r.calories, r.portions, mc.name_category
    FROM recipes r
    JOIN meal_categories mc ON r.category = mc.id ")->fetchALL(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wszystkie Przepisy - Faeudie</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<h1>Faeudie</h1>

<div id="menu">
    <div class="menu-item"><a href="index.php">Strona Główna</a></div>
    <div class="menu-item"><a href="wszystkie_przepisy.php">Wszystkie Przepisy</a></div>
    <div class="menu-item"><a href="dodaj_przepis.php">Dodaj Przepis</a></div>
    <div class="menu-item"><a href="zaloguj_sie.php">Zaloguj się</a></div>
</div>

<h2 style="text-align:center; margin-top: 140px; ">Lista przepisów</h2>

<?php foreach ($recipes as $recipe): ?>
    <div class="recipe">
        <div class="recipe-title" onclick="toggleDetails(<?= $recipe['id'] ?>)">
            <?= htmlspecialchars($recipe['title']) ?>
        </div>
        <div class="recipe-details" id="details-<?= $recipe['id']?>">
            <p><strong>Kategoria:</strong> <?=htmlspecialchars($recipe['name_category']) ?></p>
            <p><strong>Instrukcje:</strong> <?= nl2br(htmlspecialchars($recipe['instructions']))?></p>
            <p><strong>Kalorie:</strong> <?= $recipe['calories'] ?></p>
            <p><strong>Porcje:</strong> <?= $recipe['portions'] ?></p>
        </div>
    </div>
<?php endforeach; ?>

<script>
    function toggleDetails(id){
        const elem=document.getElementById('details-' +id);
        elem.style.display = elem.style.display === 'block' ? 'none' : 'block';
    }
</script>

</body>
</html>
