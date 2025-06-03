<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<?php
    require_once 'db.php';

    require_once 'sesja.php';
    if (!isLoggedIn()) {
        echo '<p>Musisz być zalogowany, żeby dodać przepis.</p>';
        exit;
    }

    $categories = $conn->query("SELECT id, name_category FROM meal_categories")->fetchAll(PDO::FETCH_ASSOC);
    $ingredients = $conn->query("SELECT id, name FROM ingredients")->fetchAll(PDO::FETCH_ASSOC);
    $units = $conn->query("SELECT id, name FROM units")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all available ingredients for the datalist (autocomplete suggestions)
    $all_ingredients = $conn->query("SELECT id, name FROM ingredients ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dodaj Przepis</title>
        <link rel="stylesheet" href="style.css">
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Faeudie</h1>
    <?php include 'menu.php'; ?>


<div class="form-wrapper">
    <h2>Dodaj Przepis</h2>
<form action="zapisz_przepis.php" method = "POST">
    <label for="nazwa">Nazwa przepisu:</label>
    <input type="text" id="nazwa" name="nazwa" required><br>

    <label for ="instrukcje">Instrukcje:</label>
    <textarea id="instrukcje" name="instrukcje" required></textarea><br>

    <label for="kategoria">Typ posiłku:</label>
    <select id="kategoria" name="kategoria">
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat ['id'] ?>"><?= $cat['name_category'] ?></option>
        <?php endforeach; ?>
    </select><br>

    <label for="porcje">Liczba porcji:</label>
    <input type="number" id="porcje" name="porcje" required> <br>

    <h3>Składniki:</h3>

    <div id="ingredients-container">
        <div class="ingredient-row">
            <label>Składnik:</label>
            <input type="text" name="skladnik[]" list="ingredient_suggestions" placeholder="Wpisz nazwę składnika..." required>
            
            <datalist id="ingredient_suggestions">
                <?php foreach($all_ingredients as $ing): ?>
                    <option value="<?= htmlspecialchars($ing['name']) ?>"></option>
                <?php endforeach; ?>
            </datalist>

            <label>Ilość:</label>
            <input type="number" name="ilosc[]" step="0.1" required>

            <label>Jednostka:</label>
            <select name="jednostka[]">
                <?php foreach ($units as $unit): ?>
                    <option value="<?= $unit['id'] ?>"><?= $unit['name'] ?></option>
              <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button type="button" id="add-ingredient-btn">Dodaj składnik</button><br>

    <input type="submit" value="Dodaj przepis">
</form>
</div>

<script>
    const unitsOptions = `<?php
        $options = '';
        foreach ($units as $unit) {
            $options .= '<option value="' . $unit['id'] . '">' . htmlspecialchars($unit['name']) . '</option>';
        }
        echo $options;
    ?>`;

    document.getElementById("add-ingredient-btn").addEventListener("click", function(){
        var newRow = document.createElement("div");
        newRow.classList.add("ingredient-row");

        newRow.innerHTML = `
            <label>Składnik:</label>
            <input type="text" name="skladnik[]" list="ingredient_suggestions" placeholder="Wpisz nazwę składnika..." required>            <datalist id="ingredient_suggestions">
                <?php foreach($ingredients as $ing): ?>
                    <option value="<?= $ing['id'] ?>"><?= $ing['name'] ?></option>
                <?php endforeach; ?>
                </datalist>
            </input>

            <label>Ilość:</label>
            <input type="number" name="ilosc[]" step="0.1" required>

            <label>Jednostka:</label>
            <select name="jednostka[]">
                ${unitsOptions}
            </select>
        `;
        
        document.getElementById("ingredients-container").appendChild(newRow);
    });
    </script>
</body>
</html>