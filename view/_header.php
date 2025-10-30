<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Security Headers -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:;">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    <!-- App Meta -->
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?><?= APP_NAME ?> v<?= APP_VERSION ?></title>
    <meta name="description" content="Restaurant ERP System - Restoran Yönetim Sistemi">
    <meta name="keywords" content="restaurant, management, pos, erp, restoran, yönetim">
    <meta name="author" content="<?= APP_NAME ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $sitePath ?>assets/img/favicon.ico">
    <link rel="apple-touch-icon" href="<?= $sitePath ?>assets/img/apple-touch-icon.png">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?= $sitePath ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= $sitePath ?>assets/css/normalize.css">
    <link rel="stylesheet" href="<?= $sitePath ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= $sitePath ?>assets/css/responsive.css">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">

    <!-- Enhanced Styles -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --text-color: #333;
            --text-muted: #6c757d;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }

        /* Loading Spinner */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            transition: opacity 0.3s ease;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced Navigation */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
        }

        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .navbar-nav > li > a {
            color: rgba(255,255,255,0.9) !important;
            transition: color 0.3s ease;
        }

        .navbar-nav > li > a:hover,
        .navbar-nav > li > a:focus {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
        }

        /* User Info Dropdown */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        /* Enhanced Buttons */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: var(--secondary-color);
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.3);
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.4);
        }

        .btn-success {
            background: var(--success-color);
            box-shadow: 0 2px 4px rgba(39, 174, 96, 0.3);
        }

        .btn-warning {
            background: var(--warning-color);
            box-shadow: 0 2px 4px rgba(243, 156, 18, 0.3);
        }

        .btn-danger {
            background: var(--error-color);
            box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
        }

        /* Enhanced Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }

        /* Responsive Helpers */
        @media (max-width: 768px) {
            .navbar-nav {
                margin-top: 10px;
            }

            .user-info {
                padding: 10px 0;
                border-top: 1px solid rgba(255,255,255,0.1);
                margin-top: 10px;
            }
        }

        /* Accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            white-space: nowrap;
            border: 0;
        }

        /* Focus indicators */
        .btn:focus,
        .form-control:focus,
        a:focus {
            outline: 2px solid var(--secondary-color);
            outline-offset: 2px;
        }
    </style>

    <!-- JavaScript Libraries -->
    <script src="<?= $sitePath ?>assets/js/jquery-1.7.2.min.js"></script>
    <script src="<?= $sitePath ?>assets/js/bootstrap.min.js"></script>

    <!-- Global JavaScript Variables -->
    <script>
        window.APP_CONFIG = {
            sitePath: '<?= $sitePath ?>',
            appName: '<?= APP_NAME ?>',
            appVersion: '<?= APP_VERSION ?>',
            csrfToken: '<?= Security::generateCSRFToken() ?>',
            userId: <?= isset($_SESSION['user_session']) ? (int)$_SESSION['user_session'] : 'null' ?>,
            userRole: <?= isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : 'null' ?>,
            debugMode: <?= DEBUG_MODE ? 'true' : 'false' ?>
        };

        // Global AJAX setup
        $(document).ready(function() {
            // Set default AJAX headers
            $.ajaxSetup({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.APP_CONFIG.csrfToken
                },
                beforeSend: function(xhr, settings) {
                    // Add CSRF token to POST requests
                    if (settings.type === 'POST' && settings.data) {
                        if (typeof settings.data === 'string') {
                            settings.data += '&csrf_token=' + encodeURIComponent(window.APP_CONFIG.csrfToken);
                        } else if (typeof settings.data === 'object') {
                            settings.data.csrf_token = window.APP_CONFIG.csrfToken;
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    if (xhr.status === 401) {
                        window.location.href = 'login.php?timeout=1';
                    } else if (xhr.status === 403) {
                        if (window.addFlashMessage) {
                            addFlashMessage('Bu işlem için yetkiniz bulunmuyor.', 'error');
                        } else {
                            alert('Bu işlem için yetkiniz bulunmuyor.');
                        }
                    }
                }
            });

            // Auto-hide loading overlay
            setTimeout(function() {
                $('.loading-overlay').fadeOut();
            }, 500);
        });

        // Utility functions
        function formatCurrency(amount) {
            return new Intl.NumberFormat('tr-TR', {
                style: 'currency',
                currency: '<?= CURRENCY_CODE ?>'
            }).format(amount);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('tr-TR');
        }

        function showConfirm(message, callback) {
            if (confirm(message)) {
                if (typeof callback === 'function') {
                    callback();
                }
            }
        }
    </script>

    <?php
    // Process flash messages from session
    FlashMessage::setFromURL();
    ?>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Flash Messages Container -->
    <?= FlashMessage::render() ?>

    <!-- Skip to main content (accessibility) -->
    <a href="#main-content" class="sr-only sr-only-focusable">Ana içeriğe atla</a>

    <!-- CSRF Token for forms -->
    <input type="hidden" id="csrf-token" value="<?= Security::generateCSRFToken() ?>">
