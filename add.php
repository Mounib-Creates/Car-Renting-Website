<?php
$filename = 'cars.json';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_car'])) {
        try {
            if (empty($_POST['brand'])) {
                $errors['brand'] = 'Brand is required.';
            }
            if (empty($_POST['model'])) {
                $errors['model'] = 'Model is required.';
            }
            if (!in_array($_POST['transmission'], ['Manual', 'Automatic'])) {
                $errors['transmission'] = 'Invalid transmission value.';
            }
            if (!in_array($_POST['fuel_type'], ['Petrol', 'Diesel', 'Electric'])) {
                $errors['fuel_type'] = 'Invalid fuel type value.';
            }
            if (!is_numeric($_POST['passengers']) || intval($_POST['passengers']) < 2 || intval($_POST['passengers']) > 10) {
                $errors['passengers'] = 'Passengers must be a number between 2 and 10.';
            }
            if (!is_numeric($_POST['daily_price_huf']) || intval($_POST['daily_price_huf']) <= 0) {
                $errors['daily_price_huf'] = 'Daily price must be a positive number.';
            }
            if (empty($_POST['image'])) {
                $errors['image'] = 'Image URL is required.';
            }
            if (!is_numeric($_POST['year']) || intval($_POST['year']) < 1900 || intval($_POST['year']) > date('Y')) {
                $errors['year'] = 'Year must be a valid number between 1900 and the current year.';
            }

            if (empty($errors)) {
                $currentCars = json_decode(file_get_contents($filename), true);

                $nextId = 1;
                if (!empty($currentCars)) {
                    $ids = array_map(function($car) {
                        return (int) $car['id'];
                    }, $currentCars);
                    $nextId = max($ids) + 1;
                }

                $newCar = [
                    'id' => $nextId,
                    'brand' => $_POST['brand'],
                    'model' => $_POST['model'],
                    'year' => intval($_POST['year']),
                    'transmission' => $_POST['transmission'],
                    'fuel_type' => $_POST['fuel_type'],
                    'passengers' => intval($_POST['passengers']),
                    'daily_price_huf' => intval($_POST['daily_price_huf']),
                    'image' => $_POST['image']
                ];

                $currentCars[] = $newCar;
                file_put_contents($filename, json_encode($currentCars, JSON_PRETTY_PRINT));

                $successMessage = "Car added successfully!";
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Car</title>
    <link rel="stylesheet" href="add.css">
</head>
<body>
    <div class="container">
        <h1>Add Car</h1>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" required value="<?= $_POST['brand'] ?? '' ?>">
                <?php if (isset($errors['brand'])): ?>
                    <div class="alert alert-danger"><?= $errors['brand'] ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" required value="<?= $_POST['model'] ?? '' ?>">
                <?php if (isset($errors['model'])): ?>
                    <div class="alert alert-danger"><?= $errors['model'] ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="year">Year</label>
                <input type="number" id="year" name="year" required min="1900" max="<?= date('Y') ?>" value="<?= $_POST['year'] ?? '' ?>">
                <?php if (isset($errors['year'])): ?>
                    <div class="alert alert-danger"><?= $errors['year'] ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="transmission">Transmission</label>
                <select id="transmission" name="transmission" required>
                    <option value="Manual" <?= ($_POST['transmission'] ?? '') === 'Manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="Automatic" <?= ($_POST['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                </select>
                <?php if (isset($errors['transmission'])): ?>
                    <div class="alert alert-danger"><?= $errors['transmission'] ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="fuel_type">Fuel Type</label>
                <select id="fuel_type" name="fuel_type" required>
                    <option value="Petrol" <?= ($_POST['fuel_type'] ?? '') === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                    <option value="Diesel" <?= ($_POST['fuel_type'] ?? '') === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                    <option value="Electric" <?= ($_POST['fuel_type'] ?? '') === 'Electric' ? 'selected' : '' ?>>Electric</option>
                </select>
                <?php if (isset($errors['fuel_type'])): ?>
                    <div class="alert alert-danger"><?= $errors['fuel_type'] ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="passengers">Passengers</label>
                <input type="number" id="passengers" name="passengers" required value="<?= $_POST['passengers'] ?? '' ?>">
                <?php if (isset($errors['passengers'])): ?>
                    <div class="alert alert-danger"><?= $errors['passengers'] ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="daily_price_huf">Daily Price (HUF)</label>
                <input type="number" id="daily_price_huf" name="daily_price_huf" required value="<?= $_POST['daily_price_huf'] ?? '' ?>">
                <?php if (isset($errors['daily_price_huf'])): ?>
                    <div class="alert alert-danger"><?= $errors['daily_price_huf'] ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="image">Image URL</label>
                <input type="text" id="image" name="image" required value="<?= $_POST['image'] ?? '' ?>">
                <?php if (isset($errors['image'])): ?>
                    <div class="alert alert-danger"><?= $errors['image'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" name="add_car" class="btn btn-primary">Add Car</button>
        </form>

        <a href="admin_profile.php" class="btn btn-secondary">Back to Admin Page</a>
    </div>
</body>
</html>
