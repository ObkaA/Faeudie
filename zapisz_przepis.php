<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
require_once 'db.php';

$nazwa = $_POST['nazwa'];
$instrukcje = $_POST['instrukcje'];
$kategoria = $_POST['kategoria'];
$kalorie = $_POST['kalorie'];
$porcje = $_POST['porcje'];
$user_id = 1;

if (empty($nazwa) || empty($instrukcje) || empty($kategoria) || empty($kalorie) || empty($porcje)) {
    echo "Wszystkie pola są wymagane!";
    exit;
}

if (!is_numeric($kalorie) || !is_numeric($porcje)) {
    echo "Kalorie i porcje muszą być liczbami!";
    exit;
}

$query = "INSERT INTO recipes (user_id, title, category, instructions, calories, portions) 
          VALUES (:user_id, :title, :category, :instructions, :calories, :portions)";
$stmt = $conn->prepare($query);
$stmt->execute([
    ':user_id' => $user_id,
    ':title' => $nazwa,
    ':category' => $kategoria,
    ':instructions' => $instrukcje,
    ':calories' => $kalorie,
    ':portions' => $porcje
]);

$recipe_id = $conn->lastInsertId();

$skladniki = $_POST['skladnik'];
$ilosci = $_POST['ilosc'];
$jednostki = $_POST['jednostka'];

if (empty($skladniki) || empty($ilosci) || empty($jednostki)) {
    echo "Wszystkie składniki muszą być wypełnione!";
    exit;
}

for ($i = 0; $i < count($skladniki); $i++) {
    if (!is_numeric($ilosci[$i])) {
        echo "Ilość składnika musi być liczbą!";
        exit;
    }

    $query = "INSERT INTO recipeingredients (recipe_id, ingredient_id, amount, unit) 
              VALUES (:recipe_id, :ingredient_id, :amount, :unit)";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':recipe_id' => $recipe_id,
        ':ingredient_id' => $skladniki[$i],
        ':amount' => $ilosci[$i],
        ':unit' => $jednostki[$i]
    ]);
}

header('Location: index.php'); 
exit();
?>