<?php
require_once 'db.php';
require_once 'sesja.php'; // Include your session management file

// Start the session if it hasn't been started by sesja.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = null;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID from session
}

// Determine if we should show only makeable recipes
$show_only_makeable = isset($_GET['show_makeable']) && $_GET['show_makeable'] == 'true';

if ($show_only_makeable && $user_id) {
    // Query to fetch recipes that the user can make with ingredients in their fridge
    // This query checks if all recipe ingredients are present in the user's fridge
    // with sufficient quantity and matching unit.
    $stmt = $conn->prepare("
        SELECT r.id, r.title, r.instructions, r.calories, r.portions, mc.name_category
        FROM recipes r
        JOIN meal_categories mc ON r.category = mc.id
        WHERE NOT EXISTS (
            SELECT ri.ingredient_id
            FROM recipeingredients ri
            JOIN ingredients i ON ri.ingredient_id = i.id
            JOIN units u ON ri.unit = u.id -- Join units table to get unit name from recipeingredients
            LEFT JOIN fridgeingredient fi ON 
                ri.ingredient_id = fi.ingredient_id 
                AND fi.user_id = :user_id 
            WHERE ri.recipe_id = r.id AND fi.ingredient_id IS NULL
        )
    ");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Original query: fetch all recipes
    $recipes = $conn->query("
        SELECT r.id, r.title, r.instructions, r.calories, r.portions, mc.name_category
        FROM recipes r
        JOIN meal_categories mc ON r.category = mc.id ")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wszystkie Przepisy - Faeudie</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Basic styling for recipe containers */
        .recipe {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .recipe-title {
            cursor: pointer;
            font-weight: 600;
            color: #333;
        }
        .recipe-details {
            display: none; /* Hidden by default */
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #eee;
        }
        .filter-options {
            margin-left:100px;
            margin-bottom: 20px;
        }
        .filter-options a {
            padding: 10px 15px;
            margin: 0 5px;
            border: 1px solidrgb(65, 58, 55);
            border-radius: 5px;
            text-decoration: none;
            color:rgb(59, 51, 51);
            background-color: #fff;
            transition: background-color 0.3s, color 0.3s;
        }
        .filter-options a.active,
        .filter-options a:hover {
            background-color:rgb(70, 63, 57);
            color: #fff;
        }
    </style>
</head>
<body>

<h1>Faeudie</h1>

<?php include 'menu.php'; ?>

<h2 class="recipe-list-heading">Lista przepisów</h2>


<div class="filter-options">
    <a href="wszystkie_przepisy.php" class="<?= !$show_only_makeable ? 'active' : '' ?>">Wszystkie przepisy</a>
    <?php if ($user_id): ?>
        <a href="wszystkie_przepisy.php?show_makeable=true" class="<?= $show_only_makeable ? 'active' : '' ?>">Przepisy do zrobienia (z lodówki)</a>
    <?php endif; ?>
</div>

<?php if (empty($recipes)): ?>
    <div class="recipe">
    <p style="text-align: center;">Brak przepisów do wyświetlenia.</p>
</div>
<?php else: ?>
    <?php foreach ($recipes as $recipe): ?>
        <div class="recipe">
            <div class="recipe-title" onclick="toggleDetails(<?= $recipe['id'] ?>)">
                <?= htmlspecialchars($recipe['title']) ?>
            </div>
            <div class="recipe-details" id="details-<?= $recipe['id']?>">
                <p><strong>Kategoria:</strong> <?= htmlspecialchars($recipe['name_category']) ?></p>
                <p><strong>Instrukcje:</strong> <?= nl2br(htmlspecialchars($recipe['instructions']))?></p>
                <p><strong>Kalorie:</strong> <?= $recipe['calories'] ?></p>
                <p><strong>Porcje:</strong> <?= $recipe['portions'] ?></p>
                <p><a href="przepis.php?id=<?= $recipe['id'] ?>">Zobacz pełny przepis</a></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    function toggleDetails(id){
        const elem = document.getElementById('details-' + id);
        elem.style.display = elem.style.display === 'block' ? 'none' : 'block';
    }
</script>

</body>
</html>