<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Restaurant ERP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #7f8c8d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fa fa-cutlery"></i> Restaurant ERP</h1>
            <p>Yönetim Paneli Girişi</p>
        </div>
        
        <div class="login-body">
            <?php if(isset($_GET['error']) && $_GET['error'] == "login"): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i>
                <span>Kullanıcı adı veya şifre hatalı! Lütfen tekrar deneyin.</span>
            </div>
            <?php endif; ?>
            
            <form action="userTasks.php" method="POST">
                <div class="form-group">
                    <label>Kullanıcı Adı / E-posta</label>
                    <div class="input-wrapper">
                        <i class="fa fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Kullanıcı adınızı girin" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Şifre</label>
                    <div class="input-wrapper">
                        <i class="fa fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Şifrenizi girin" required>
                    </div>
                </div>
                
                <input type="hidden" name="task" value="login">
                
                <button type="submit" name="submit" class="btn-login">
                    <i class="fa fa-sign-in"></i> Giriş Yap
                </button>
            </form>
        </div>
        
        <div class="login-footer">
            © 2025 Restaurant ERP v2.0.0 - Tüm hakları saklıdır
        </div>
    </div>
</body>
</html>
