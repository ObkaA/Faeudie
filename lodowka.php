<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';
require_once 'sesja.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: zaloguj_sie.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: zaloguj_sie.php");
    exit();
}

$all_ingredients = $conn->query("SELECT id, name FROM ingredients ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$common_units = ['g', 'kg', 'ml', 'l', 'szt.', 'szczypta', 'łyżka', 'łyżeczka', 'opakowanie'];

$stmt = $conn->prepare("
    SELECT
        i.name AS ingredient_name,
        i.protein,
        i.carbs,
        i.fat,
        fi.amount,
        fi.unit,
        fi.user_id,
        fi.ingredient_id
    FROM
        fridgeingredient fi
    JOIN
        ingredients i ON fi.ingredient_id = i.id
    WHERE
        fi.user_id = :user_id
    ORDER BY
        i.name
");
$stmt->execute([':user_id' => $user_id]);
$fridge_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moja Lodówka</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .fridge-table input[type="number"], .fridge-table select {
            width: 80px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .error-message, .success-message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <h1>Faeudie</h1>
    <?php include 'menu.php'; ?>

    <div class="form-wrapper">
        <h2>Moja Lodówka</h2>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'success'): ?>
                <p class="success-message">Zmiany w lodówce zostały zapisane!</p>
            <?php elseif ($_GET['status'] == 'error'): ?>
                <p class="error-message">Wystąpił błąd podczas zapisywania zmian. Spróbuj ponownie.</p>
            <?php endif; ?>
        <?php endif; ?>

        <h3>Twoje Składniki:</h3>
        <form id="fridgeUpdateForm">
            <table class="fridge-table">
                <thead>
                    <tr>
                        <th>Składnik</th>
                        <th>Ilość</th>
                        <th>Jednostka</th>
                        <th>Białko (g)</th>
                        <th>Węglowodany (g)</th>
                        <th>Tłuszcz (g)</th>
                        <th>Kcal</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody id="fridge-items-tbody">
                    <?php if (empty($fridge_items)): ?>
                        <tr id="no-items-row">
                            <td colspan="8">Twoja lodówka jest pusta. Dodaj jakieś składniki!</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($fridge_items as $item): 
                            $item_protein_total = round($item['protein'] * ($item['amount'] / 100), 1);
                            $item_carbs_total = round($item['carbs'] * ($item['amount'] / 100), 1);
                            $item_fat_total = round($item['fat'] * ($item['amount'] / 100), 1);
                            $item_kcal_total = round(($item_protein_total * 4) + ($item_carbs_total * 4) + ($item_fat_total * 9), 1);
                        ?>
                            <tr data-type="existing" 
                                data-user-id="<?= htmlspecialchars($item['user_id']) ?>" 
                                data-ingredient-id="<?= htmlspecialchars($item['ingredient_id']) ?>" 
                                data-original-unit="<?= htmlspecialchars($item['unit']) ?>"
                                data-protein-per-100="<?= htmlspecialchars($item['protein']) ?>"
                                data-carbs-per-100="<?= htmlspecialchars($item['carbs']) ?>"
                                data-fat-per-100="<?= htmlspecialchars($item['fat']) ?>">
                                <td><?= htmlspecialchars($item['ingredient_name']) ?></td>
                                <td>
                                    <input type="number" class="amount-input" value="<?= htmlspecialchars($item['amount']) ?>" step="0.1" min="0.1" required>
                                </td>
                                <td>
                                    <select class="unit-select" required>
                                        <?php foreach ($common_units as $unit_name): ?>
                                            <option value="<?= htmlspecialchars($unit_name) ?>" <?= ($item['unit'] == $unit_name) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($unit_name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="protein-display"><?= $item_protein_total ?></td>
                                <td class="carbs-display"><?= $item_carbs_total ?></td>
                                <td class="fat-display"><?= $item_fat_total ?></td>
                                <td class="kcal-display"><?= $item_kcal_total ?></td>
                                <td>
                                    <button type="button" class="remove-item-btn">Usuń</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="submit" id="saveAllChangesBtn">Zapisz wszystkie zmiany</button>
        </form>

        <h3>Dodaj nowy składnik do lodówki:</h3>
        <div class="add-new-item-section">
            <label for="ingredient_name_input">Składnik:</label>
            <input type="text" id="ingredient_name_input" list="ingredient_suggestions" placeholder="Wpisz nazwę składnika..." required>
            
            <datalist id="ingredient_suggestions">
                <?php foreach($all_ingredients as $ing): ?>
                    <option value="<?= htmlspecialchars($ing['name']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
            <button type="button" id="addNewItemBtn">Dodaj do listy</button>
        </div>
    </div>

    <script>
        const fridgeItemsTbody = document.getElementById('fridge-items-tbody');
        const ingredientNameInput = document.getElementById('ingredient_name_input');
        const addNewItemBtn = document.getElementById('addNewItemBtn');
        const fridgeUpdateForm = document.getElementById('fridgeUpdateForm');
        const noItemsRow = document.getElementById('no-items-row');

        const commonUnits = <?= json_encode($common_units); ?>;
        const allIngredientsWithMacros = <?= json_encode($conn->query("SELECT id, name, protein, carbs, fat FROM ingredients")->fetchAll(PDO::FETCH_ASSOC)); ?>;

        // Obliczanie kalorii i makro
        function calculateMacrosAndKcal(amount, proteinPer100, carbsPer100, fatPer100) {
            const ratio = amount / 100;
            const protein = parseFloat((proteinPer100 * ratio).toFixed(1));
            const carbs = parseFloat((carbsPer100 * ratio).toFixed(1));
            const fat = parseFloat((fatPer100 * ratio).toFixed(1));
            const kcal = parseFloat(((protein * 4) + (carbs * 4) + (fat * 9)).toFixed(1));
            return { protein, carbs, fat, kcal };
        }


        // Aktualizowanie makro
        function updateMacroDisplay(row) {
            const amount = parseFloat(row.querySelector('.amount-input').value);
            const proteinPer100 = parseFloat(row.dataset.proteinPer100);
            const carbsPer100 = parseFloat(row.dataset.carbsPer100);
            const fatPer100 = parseFloat(row.dataset.fatPer100);

            if (!isNaN(amount) && !isNaN(proteinPer100) && !isNaN(carbsPer100) && !isNaN(fatPer100)) {
                const { protein, carbs, fat, kcal } = calculateMacrosAndKcal(amount, proteinPer100, carbsPer100, fatPer100);
                row.querySelector('.protein-display').textContent = protein;
                row.querySelector('.carbs-display').textContent = carbs;
                row.querySelector('.fat-display').textContent = fat;
                row.querySelector('.kcal-display').textContent = kcal;
            } else {
                row.querySelector('.protein-display').textContent = 'N/A';
                row.querySelector('.carbs-display').textContent = 'N/A';
                row.querySelector('.fat-display').textContent = 'N/A';
                row.querySelector('.kcal-display').textContent = 'N/A';
            }
        }


        function createNewFridgeItemRow(ingredientName, suggestedIngredientId = null, protein = 0, carbs = 0, fat = 0) {
            //Usuniecie "twoja lodowka jest pusta"
            if (noItemsRow) {
                noItemsRow.remove();
            }

            const newRow = document.createElement('tr');
            newRow.dataset.type = 'new'; 
            newRow.dataset.ingredientName = ingredientName; 
            newRow.dataset.suggestedIngredientId = suggestedIngredientId; 
            newRow.dataset.proteinPer100 = protein;
            newRow.dataset.carbsPer100 = carbs;
            newRow.dataset.fatPer100 = fat;

            const initialAmount = 1.0; 
            const { protein: initialProtein, carbs: initialCarbs, fat: initialFat, kcal: initialKcal } = 
                calculateMacrosAndKcal(initialAmount, protein, carbs, fat);


            newRow.innerHTML = `
                <td>${htmlspecialchars(ingredientName)}</td>
                <td>
                    <input type="number" class="amount-input" value="${initialAmount}" step="0.1" min="0.1" required>
                </td>
                <td>
                    <select class="unit-select" required>
                        ${commonUnits.map(unit => `<option value="${htmlspecialchars(unit)}">${htmlspecialchars(unit)}</option>`).join('')}
                    </select>
                </td>
                <td class="protein-display">${initialProtein}</td>
                <td class="carbs-display">${initialCarbs}</td>
                <td class="fat-display">${initialFat}</td>
                <td class="kcal-display">${initialKcal}</td>
                <td>
                    <button type="button" class="remove-item-btn">Usuń</button>
                </td>
            `;
            fridgeItemsTbody.appendChild(newRow);

            newRow.querySelector('.amount-input').addEventListener('input', () => updateMacroDisplay(newRow));
            newRow.querySelector('.remove-item-btn').addEventListener('click', function() {
                newRow.remove();
                // Pusta lodówka
                if (fridgeItemsTbody.children.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.id = 'no-items-row';
                    emptyRow.innerHTML = '<td colspan="8">Twoja lodówka jest pusta. Dodaj jakieś składniki!</td>';
                    fridgeItemsTbody.appendChild(emptyRow);
                }
            });
        }

        addNewItemBtn.addEventListener('click', function() {
            const ingredientName = ingredientNameInput.value.trim();
            if (ingredientName) {
                // Sprawdzenie czy składnik jest już w liście
                const existingDisplayedItems = Array.from(fridgeItemsTbody.children).filter(row => row.id !== 'no-items-row');
                const isAlreadyDisplayed = existingDisplayedItems.some(row => {
                    const displayedName = row.querySelector('td').textContent.trim();
                    return displayedName.toLowerCase() === ingredientName.toLowerCase();
                });

                if (isAlreadyDisplayed) {
                    alert('Ten składnik już znajduje się na liście.');
                } else {
                    // Zebranie makra dla istniejącego składnika
                    const matchedIngredient = allIngredientsWithMacros.find(ing => ing.name.toLowerCase() === ingredientName.toLowerCase());
                    
                    if (matchedIngredient) {
                        createNewFridgeItemRow(
                            ingredientName, 
                            matchedIngredient.id, 
                            matchedIngredient.protein, 
                            matchedIngredient.carbs, 
                            matchedIngredient.fat
                        );
                    } else {
                        // Ustawienie na kcal (w przyszlosci mozna zrobic to inaczej) 0 dla nowych skladnikow
                        createNewFridgeItemRow(ingredientName, null, 0, 0, 0);
                    }
                    ingredientNameInput.value = ''; // Clear input after adding
                }
            } else {
                alert('Proszę wpisać nazwę składnika.');
            }
        });

        //Listener usuwania składnika
        document.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                row.remove();
                // Wszystko zostało usunięte - Pokaż że lodówka jest pusta
                if (fridgeItemsTbody.children.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.id = 'no-items-row';
                    emptyRow.innerHTML = '<td colspan="8">Twoja lodówka jest pusta. Dodaj jakieś składniki!</td>';
                    fridgeItemsTbody.appendChild(emptyRow);
                }
            });
        });

        // Aktualizacja makra (działa tylko dla nowych składników)
        document.querySelectorAll('.amount-input').forEach(input => {
            input.addEventListener('input', function() {
                updateMacroDisplay(this.closest('tr'));
            });
        });


        fridgeUpdateForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const itemsToSave = [];
            Array.from(fridgeItemsTbody.children).forEach(row => {
                if (row.id === 'no-items-row') return;

                const type = row.dataset.type; // 'existing' lub 'new'
                const ingredientName = type === 'new' ? row.dataset.ingredientName : row.querySelector('td').textContent.trim();
                const amount = row.querySelector('.amount-input').value;
                const unit = row.querySelector('.unit-select').value;

                let itemData = {
                    type: type,
                    ingredient_name: ingredientName,
                    amount: amount,
                    unit: unit
                };

                if (type === 'existing') {
                    itemData.user_id = row.dataset.userId;
                    itemData.ingredient_id = row.dataset.ingredientId;
                    itemData.original_unit = row.dataset.originalUnit;
                } else { // type === 'new'
                    itemData.suggested_ingredient_id = row.dataset.suggestedIngredientId; // Dostępne ID
                }
                itemsToSave.push(itemData);
            });


            fetch('zapisz_lodowke.php', { // Skrypt do zapisania zmian w bazie danych
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(itemsToSave)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = 'lodowka.php?status=success'; // Sukces
                } else {
                    alert('Błąd podczas zapisywania zmian: ' + data.message);
                    window.location.href = 'lodowka.php?status=error'; // Blad
                }
            })
            .catch(error => {
                console.error('Błąd Fetch:', error);
                alert('Wystąpił błąd komunikacji z serwerem.');
                window.location.href = 'lodowka.php?status=error';
            });
        });

        // Simple HTML entity escaping for JS
        function htmlspecialchars(str) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    </script>
</body>
</html>