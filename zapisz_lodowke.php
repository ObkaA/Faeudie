<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';
require_once 'sesja.php';

header('Content-Type: application/json'); // Respond with JSON

// Ensure the user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User ID not found in session.']);
    exit();
}

// Get the raw POST data (JSON)
$input = file_get_contents('php://input');
$items_to_process = json_decode($input, true);

if (!is_array($items_to_process)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data format.']);
    exit();
}

try {
    $conn->beginTransaction();

    // 1. Fetch current fridge state from DB for comparison (efficient diffing)
    $stmt = $conn->prepare("
        SELECT
            ingredient_id,
            unit,
            amount
        FROM
            fridgeingredient
        WHERE
            user_id = :user_id
    ");
    $stmt->execute([':user_id' => $user_id]);
    $current_db_items_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create a map for easy lookup: ['ingredient_id_unit' => amount]
    $current_db_map = [];
    foreach ($current_db_items_raw as $item) {
        $current_db_map[$item['ingredient_id'] . '_' . $item['unit']] = $item['amount'];
    }

    // Prepare a map for items from the client to be inserted/updated
    // This will hold the final state of fridge items after client-side modifications
    $final_client_items_map = []; // key: ingredient_id_unit, value: amount

    foreach ($items_to_process as $item) {
        $ingredient_id = null; // This will hold the resolved ingredient_id

        // Resolve ingredient_id for new items or ensure existing ones are valid
        if ($item['type'] === 'new') {
            $ingredient_name = trim($item['ingredient_name'] ?? '');
            if (empty($ingredient_name)) {
                throw new Exception("New ingredient name cannot be empty.");
            }

            // Check if ingredient exists in 'ingredients' table
            $stmt = $conn->prepare("SELECT id FROM ingredients WHERE name ILIKE :ingredient_name");
            $stmt->execute([':ingredient_name' => $ingredient_name]);
            $existing_ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_ingredient) {
                $ingredient_id = $existing_ingredient['id'];
            } else {
                // Insert new ingredient into 'ingredients' table
                $stmt = $conn->prepare("INSERT INTO ingredients (name) VALUES (:ingredient_name) RETURNING id");
                $stmt->execute([':ingredient_name' => $ingredient_name]);
                $new_ingredient = $stmt->fetch(PDO::FETCH_ASSOC);
                $ingredient_id = $new_ingredient['id'];
                if (!$ingredient_id) {
                    throw new Exception("Failed to insert new ingredient and retrieve ID.");
                }
            }
        } else { // type === 'existing'
            $ingredient_id = $item['ingredient_id'];
        }

        if (!$ingredient_id) {
            throw new Exception("Could not resolve ingredient ID for item: " . ($item['ingredient_name'] ?? $item['ingredient_id']));
        }

        // Add to final client items map. Handle potential unit changes or accumulation.
        $item_key = $ingredient_id . '_' . $item['unit'];
        $amount_float = (float)$item['amount'];

        if (array_key_exists($item_key, $final_client_items_map)) {
            // Accumulate amount if same ingredient/unit is added multiple times client-side
            $final_client_items_map[$item_key] += $amount_float;
        } else {
            $final_client_items_map[$item_key] = $amount_float;
        }
    }

    // Now, compare $final_client_items_map with $current_db_map to perform DB operations

    // Items to delete (present in DB but not in final client map)
    $delete_stmt = $conn->prepare("DELETE FROM fridgeingredient WHERE user_id = :user_id AND ingredient_id = :ingredient_id AND unit = :unit");
    foreach ($current_db_map as $key => $amount) {
        if (!array_key_exists($key, $final_client_items_map)) {
            list($ing_id, $unit) = explode('_', $key);
            $delete_stmt->execute([
                ':user_id' => $user_id,
                ':ingredient_id' => $ing_id,
                ':unit' => $unit
            ]);
        }
    }

    // Items to insert/update (present in final client map)
    $insert_stmt = $conn->prepare("INSERT INTO fridgeingredient (user_id, ingredient_id, amount, unit) VALUES (:user_id, :ingredient_id, :amount, :unit)");
    $update_stmt = $conn->prepare("UPDATE fridgeingredient SET amount = :amount WHERE user_id = :user_id AND ingredient_id = :ingredient_id AND unit = :unit");

    foreach ($final_client_items_map as $key => $amount) {
        list($ing_id, $unit) = explode('_', $key);
        if (array_key_exists($key, $current_db_map)) {
            // Update if amount changed
            if ($current_db_map[$key] !== $amount) {
                $update_stmt->execute([
                    ':amount' => $amount,
                    ':user_id' => $user_id,
                    ':ingredient_id' => $ing_id,
                    ':unit' => $unit
                ]);
            }
        } else {
            // Insert new item
            $insert_stmt->execute([
                ':user_id' => $user_id,
                ':ingredient_id' => $ing_id,
                ':amount' => $amount,
                ':unit' => $unit
            ]);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Changes saved successfully.']);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error saving fridge: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

exit();
?>