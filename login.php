<?php
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Eenvoudige, veilige controle zonder database gezeur
    if ($email === 'manager@proef.nl' && $password === 'welkom01') {
        $_SESSION['manager_logged_in'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        // Generieke foutmelding conform de veiligheidseisen (FE-07)
        $error = 'Onjuiste inloggegevens.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TableTalk - Manager Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-content: center; height: 100vh; margin: 0; padding-top: 100px; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 350px; height: fit-content; }
        h2 { text-align: center; color: #2E7D32; margin-bottom: 20px; }
        .error { color: red; margin-bottom: 15px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; background: #2E7D32; color: white; padding: 12px; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Manager Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" required placeholder="manager@proef.nl">
        </div>
        <div class="form-group">
            <label for="password">Wachtwoord</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit">Inloggen</button>
    </form>
</div>
</body>
</html>
