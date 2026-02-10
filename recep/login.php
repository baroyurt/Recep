<?php
// login.php - Kullanıcı Giriş Sayfası
session_start();

// Zaten giriş yapılmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, PDO_OPTIONS);
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Giriş başarılı
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Kullanıcı adı veya şifre hatalı!';
            }
        } catch (Exception $e) {
            $error = 'Veritabanı hatası: ' . $e->getMessage();
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Casino Bakım Takip</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .login-container {
            background: rgba(30, 30, 30, 0.95);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(201, 169, 79, 0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .login-header i {
            font-size: 60px;
            color: #c9a94f;
            margin-bottom: 20px;
        }
        .login-header h1 {
            color: #c9a94f;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #aaa;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #ddd;
            font-size: 14px;
            font-weight: 500;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #c9a94f;
        }
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid rgba(201, 169, 79, 0.3);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            font-size: 16px;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #c9a94f;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 15px rgba(201, 169, 79, 0.2);
        }
        .error-message {
            background: rgba(244, 67, 54, 0.15);
            border: 1px solid #f44336;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            color: #f44336;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error-message i {
            font-size: 20px;
        }
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(145deg, #c9a94f, #a68a3d);
            color: #1a1a1a;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(201, 169, 79, 0.3);
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 25px rgba(201, 169, 79, 0.4);
        }
        .login-btn:active {
            transform: translateY(0);
        }
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .login-footer p {
            color: #888;
            font-size: 13px;
            line-height: 1.6;
        }
        .login-footer code {
            background: rgba(201, 169, 79, 0.15);
            padding: 2px 8px;
            border-radius: 4px;
            color: #c9a94f;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-dice"></i>
            <h1>CASİNO BAKIM TAKİP</h1>
            <p>Sisteme giriş yapmak için bilgilerinizi girin</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>Kullanıcı Adı</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Kullanıcı adınızı girin" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label>Şifre</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Şifrenizi girin" required>
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>
        
        <div class="login-footer">
            <p>
                <strong>Varsayılan Hesaplar:</strong><br>
                👑 Admin: <code>admin</code> / <code>admin123</code><br>
                👤 Kullanıcı: <code>user</code> / <code>user123</code>
            </p>
        </div>
    </div>
</body>
</html>
