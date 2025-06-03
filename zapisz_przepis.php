<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
require_once 'sesja.php';

if (!isLoggedIn()) {
    die('Musisz być zalogowany, żeby dodać przepis!');
}

$nazwa = $_POST['nazwa'];
$instrukcje = $_POST['instrukcje'];
$kategoria = $_POST['kategoria'];
$porcje = $_POST['porcje'];
$user_id = $_SESSION['user_id'];

$skladniki = $_POST['skladnik'];
$ilosci = $_POST['ilosc'];
$jednostki = $_POST['jednostka'];

if (empty($nazwa) || empty($instrukcje) || empty($kategoria) || empty($porcje) || empty($skladniki)) {
    echo "Wszystkie pola są wymagane!";
    exit;
}

if (!is_numeric($porcje)) {
    echo "Porcje muszą być liczbą!";
    exit;
}

$totalCalories = 0;

// Przetwarzanie składników i obliczanie kalorii
$ingredient_ids = [];

for ($i = 0; $i < count($skladniki); $i++) {
    $ingredient_name = trim($skladniki[$i]);
    $quantity = floatval($ilosci[$i]);
    $unit_id = intval($jednostki[$i]);

    if ($ingredient_name === '' || $quantity <= 0) {
        echo "Niepoprawne dane składnika.";
        exit;
    }


    // Sprawdź czy składnik istnieje
    $stmt = $conn->prepare("SELECT id, protein, fat, carbs FROM ingredients WHERE name ILIKE :name LIMIT 1");
    $stmt->execute([':name' => $ingredient_name]);
    $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ingredient) {
        $ingredient_id = $ingredient['id'];
        $protein = $ingredient['protein'];
        $fat = $ingredient['fat'];
        $carbs = $ingredient['carbs'];
    } else {
        // Nowy składnik z zerowymi makrami
        $stmt = $conn->prepare("INSERT INTO ingredients (name, protein, fat, carbs) VALUES (:name, 0, 0, 0) RETURNING id");
        $stmt->execute([':name' => $ingredient_name]);
        $new_ing = $stmt->fetch(PDO::FETCH_ASSOC);
        $ingredient_id = $new_ing['id'];
        $protein = $fat = $carbs = 0;
    }

    // Licz kalorie jeśli są makra (na 100g)
    $totalCalories += ($quantity * (4 * $protein + 9 * $fat + 4 * $carbs)) / 100;

    echo "Total calories: " . $total_calories . "<br>";
    // Zapamiętaj do późniejszego dodania do recipeingredients
    $ingredient_ids[] = [
        'id' => $ingredient_id,
        'amount' => $quantity,
        'unit' => $unit_id
    ];
}
var_dump($_POST['skladnik']);
var_dump($_POST['ilosc']);
var_dump($_POST['jednostka']);
// exit;

// Wstaw przepis do bazy z obliczonymi kaloriami
$stmt = $conn->prepare("INSERT INTO recipes (user_id, title, category, instructions, calories, portions) 
                        VALUES (:user_id, :title, :category, :instructions, :calories, :portions)");
$stmt->execute([
    ':user_id' => $user_id,
    ':title' => $nazwa,
    ':category' => $kategoria,
    ':instructions' => $instrukcje,
    ':calories' => (int)$totalCalories,
    ':portions' => $porcje
]);

$recipe_id = $conn->lastInsertId();

// Dodaj składniki do tabeli recipeingredients
foreach ($ingredient_ids as $ing) {
    $stmt = $conn->prepare("INSERT INTO recipeingredients (recipe_id, ingredient_id, amount, unit) 
                            VALUES (:recipe_id, :ingredient_id, :amount, :unit)");
    $stmt->execute([
        ':recipe_id' => $recipe_id,
        ':ingredient_id' => $ing['id'],
        ':amount' => $ing['amount'],
        ':unit' => $ing['unit']
    ]);
}

header('Location: index.php');
exit;
?>