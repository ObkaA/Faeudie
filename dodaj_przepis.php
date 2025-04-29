<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

<?php
    
    $host = "localhost"; // adres serwera bazy danych
    $dbname = "postgres"; // nazwa bazy danych

    $conn=new PDO("pgsql:host=$host;dbname=$dbname", 'postgres', 'maslo555');
    
    $categories = $conn->query("SELECT id, name_category FROM meal_categories")->fetchAll(PDO::FETCH_ASSOC);
    $ingredients = $conn->query("SELECT id, name FROM ingredients")->fetchAll(PDO::FETCH_ASSOC);
    $units = $conn->query("SELECT id, name FROM units")->fetchAll(PDO::FETCH_ASSOC);
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
    <div id="menu">
        <div class="menu-item"><a href="index.php">Strona Główna</a></div>
        <div class="menu-item"><a href="wszystkie_przepisy.php">Wszystkie Przepisy</a></div>
        <div class="menu-item"><a href="dodaj_przepis.php">Dodaj Przepis</a></div>
        <div class="menu-item"><a href="zaloguj_sie.php">Zaloguj się</a></div>
    </div>


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

    <label for="kalorie">Kalorie:</label>
    <input type="number" id="kalorie" name="kalorie" required><br>

    <label for="porcje">Liczba porcji:</label>
    <input type="number" id="porcje" name="porcje" required> <br>

    <h3>Składniki:</h3>

    <div id="ingredients-container">
        <div class="ingredient-row">
            <label>Składnik:</label>
            <select name="skladnik[]">
                <?php foreach($ingredients as $ing): ?>
                    <option value="<?=$ing['id'] ?>"><?= $ing['name'] ?></option>
                <?php endforeach; ?>
            </select>

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
    document.getElementById("add-ingredient-btn").addEventListener("click", function(){
        var newRow = document.createElement("div");
        newRow.classList.add("ingredient-row");

        newRow.innerHTML = `
            <label>Składnik:</label>
            <select name="skladnik[]">
                <?php foreach($ingredients as $ing): ?>
                    <option value="<?= $ing['id'] ?>"><?= $ing['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label>Ilość:</label>
            <input type="number" name="ilosc[]" step="0.1" required>

            <label>Jednostka:</label>
            <select name="jednostka[]">
                <?php foreach ($units as $unit): ?>
                    <option value="<?= $unit['id'] ?>"><?= $unit['name'] ?></option>
                <?php endforeach; ?>
            </select>
        `;
        
        document.getElementById("ingredients-container").appendChild(newRow);
    });
    </script>
</body>
</html>