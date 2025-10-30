<?php
/**
 * Flash Message System
 * Restaurant ERP System v2.0
 * Session-based flash messaging with auto-cleanup
 */

class FlashMessage
{
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';

    /**
     * Set a flash message
     */
    public static function set($message, $type = self::TYPE_INFO, $dismissible = true)
    {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }

        $_SESSION['flash_messages'][] = [
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible,
            'timestamp' => time(),
            'id' => uniqid('flash_', true)
        ];
    }

    /**
     * Get all flash messages and clear them
     */
    public static function getAll($clearAfter = true)
    {
        $messages = $_SESSION['flash_messages'] ?? [];

        if ($clearAfter) {
            unset($_SESSION['flash_messages']);
        }

        return $messages;
    }

    /**
     * Get flash messages by type
     */
    public static function getByType($type, $clearAfter = true)
    {
        $all_messages = $_SESSION['flash_messages'] ?? [];
        $filtered_messages = array_filter($all_messages, function($msg) use ($type) {
            return $msg['type'] === $type;
        });

        if ($clearAfter) {
            // Remove only messages of this type
            $_SESSION['flash_messages'] = array_filter($all_messages, function($msg) use ($type) {
                return $msg['type'] !== $type;
            });

            // Reindex array
            $_SESSION['flash_messages'] = array_values($_SESSION['flash_messages']);

            // If no messages left, remove the session key
            if (empty($_SESSION['flash_messages'])) {
                unset($_SESSION['flash_messages']);
            }
        }

        return array_values($filtered_messages);
    }

    /**
     * Check if there are any flash messages
     */
    public static function has($type = null)
    {
        $messages = $_SESSION['flash_messages'] ?? [];

        if ($type === null) {
            return !empty($messages);
        }

        return !empty(array_filter($messages, function($msg) use ($type) {
            return $msg['type'] === $type;
        }));
    }

    /**
     * Count flash messages
     */
    public static function count($type = null)
    {
        $messages = $_SESSION['flash_messages'] ?? [];

        if ($type === null) {
            return count($messages);
        }

        return count(array_filter($messages, function($msg) use ($type) {
            return $msg['type'] === $type;
        }));
    }

    /**
     * Clear all flash messages
     */
    public static function clear($type = null)
    {
        if ($type === null) {
            unset($_SESSION['flash_messages']);
            return;
        }

        if (!isset($_SESSION['flash_messages'])) {
            return;
        }

        $_SESSION['flash_messages'] = array_filter($_SESSION['flash_messages'], function($msg) use ($type) {
            return $msg['type'] !== $type;
        });

        $_SESSION['flash_messages'] = array_values($_SESSION['flash_messages']);

        if (empty($_SESSION['flash_messages'])) {
            unset($_SESSION['flash_messages']);
        }
    }

    /**
     * Clean old messages (older than 1 hour)
     */
    public static function cleanup($maxAge = 3600)
    {
        if (!isset($_SESSION['flash_messages'])) {
            return;
        }

        $currentTime = time();
        $_SESSION['flash_messages'] = array_filter($_SESSION['flash_messages'], function($msg) use ($currentTime, $maxAge) {
            return ($currentTime - $msg['timestamp']) <= $maxAge;
        });

        $_SESSION['flash_messages'] = array_values($_SESSION['flash_messages']);

        if (empty($_SESSION['flash_messages'])) {
            unset($_SESSION['flash_messages']);
        }
    }

    /**
     * Render flash messages as HTML
     */
    public static function render($includeCSS = true, $includeJS = true)
    {
        self::cleanup(); // Clean old messages first
        $messages = self::getAll();

        if (empty($messages)) {
            return '';
        }

        $html = '';

        // Include CSS if requested
        if ($includeCSS) {
            $html .= self::getCSS();
        }

        // Flash messages container
        $html .= '<div id="flash-messages-container" class="flash-messages-container">';

        foreach ($messages as $message) {
            $typeClass = 'flash-' . $message['type'];
            $dismissibleClass = $message['dismissible'] ? ' flash-dismissible' : '';
            $alertIcon = self::getIconForType($message['type']);

            $html .= "<div class='flash-message {$typeClass}{$dismissibleClass}' data-id='{$message['id']}'>";
            $html .= "<div class='flash-content'>";
            $html .= "<span class='flash-icon'>{$alertIcon}</span>";
            $html .= "<span class='flash-text'>" . htmlspecialchars($message['message']) . "</span>";

            if ($message['dismissible']) {
                $html .= "<button class='flash-close' type='button' aria-label='Close'>&times;</button>";
            }

            $html .= "</div></div>";
        }

        $html .= '</div>';

        // Include JavaScript if requested
        if ($includeJS) {
            $html .= self::getJS();
        }

        return $html;
    }

    /**
     * Get CSS for flash messages
     */
    private static function getCSS()
    {
        return '
        <style>
        .flash-messages-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            width: 100%;
        }

        .flash-message {
            margin-bottom: 10px;
            padding: 12px 16px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            animation: slideInRight 0.3s ease-out;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
        }

        .flash-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .flash-icon {
            flex-shrink: 0;
            font-size: 16px;
        }

        .flash-text {
            flex: 1;
        }

        .flash-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            margin-left: 8px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .flash-close:hover {
            opacity: 1;
        }

        .flash-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .flash-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .flash-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }

        .flash-info {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .flash-message.flash-removing {
            animation: slideOutRight 0.3s ease-in forwards;
        }

        /* Mobile responsive */
        @media (max-width: 480px) {
            .flash-messages-container {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
        </style>
        ';
    }

    /**
     * Get JavaScript for flash messages
     */
    private static function getJS()
    {
        return '
        <script>
        (function() {
            // Auto-dismiss flash messages after 5 seconds
            function autoDismissFlashMessages() {
                const messages = document.querySelectorAll(".flash-message.flash-dismissible");
                messages.forEach(function(message) {
                    setTimeout(function() {
                        if (message.parentNode) {
                            dismissFlashMessage(message);
                        }
                    }, 5000);
                });
            }

            // Dismiss flash message
            function dismissFlashMessage(messageElement) {
                messageElement.classList.add("flash-removing");
                setTimeout(function() {
                    if (messageElement.parentNode) {
                        messageElement.parentNode.removeChild(messageElement);
                    }
                }, 300);
            }

            // Handle close button clicks
            function setupCloseButtons() {
                const closeButtons = document.querySelectorAll(".flash-close");
                closeButtons.forEach(function(button) {
                    button.addEventListener("click", function(e) {
                        e.preventDefault();
                        const messageElement = button.closest(".flash-message");
                        if (messageElement) {
                            dismissFlashMessage(messageElement);
                        }
                    });
                });
            }

            // Initialize when DOM is ready
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", function() {
                    setupCloseButtons();
                    autoDismissFlashMessages();
                });
            } else {
                setupCloseButtons();
                autoDismissFlashMessages();
            }

            // Global function to add flash message via JavaScript
            window.addFlashMessage = function(message, type, dismissible) {
                type = type || "info";
                dismissible = dismissible !== false;

                const container = document.getElementById("flash-messages-container");
                if (!container) return;

                const messageId = "flash_" + Date.now() + Math.random().toString(36).substr(2, 9);
                const typeClass = "flash-" + type;
                const dismissibleClass = dismissible ? " flash-dismissible" : "";
                const alertIcon = getIconForType(type);

                const messageHTML = `
                    <div class="flash-message ${typeClass}${dismissibleClass}" data-id="${messageId}">
                        <div class="flash-content">
                            <span class="flash-icon">${alertIcon}</span>
                            <span class="flash-text">${escapeHtml(message)}</span>
                            ${dismissible ? `<button class="flash-close" type="button" aria-label="Close">&times;</button>` : ""}
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML("beforeend", messageHTML);

                // Setup close button for new message
                const newMessage = container.lastElementChild;
                const closeButton = newMessage.querySelector(".flash-close");
                if (closeButton) {
                    closeButton.addEventListener("click", function(e) {
                        e.preventDefault();
                        dismissFlashMessage(newMessage);
                    });
                }

                // Auto dismiss if dismissible
                if (dismissible) {
                    setTimeout(function() {
                        if (newMessage.parentNode) {
                            dismissFlashMessage(newMessage);
                        }
                    }, 5000);
                }
            };

            function getIconForType(type) {
                switch (type) {
                    case "success": return "✓";
                    case "error": return "✕";
                    case "warning": return "⚠";
                    case "info": return "ℹ";
                    default: return "ℹ";
                }
            }

            function escapeHtml(text) {
                const div = document.createElement("div");
                div.textContent = text;
                return div.innerHTML;
            }
        })();
        </script>
        ';
    }

    /**
     * Get icon for message type
     */
    private static function getIconForType($type)
    {
        switch ($type) {
            case self::TYPE_SUCCESS:
                return '✓';
            case self::TYPE_ERROR:
                return '✕';
            case self::TYPE_WARNING:
                return '⚠';
            case self::TYPE_INFO:
            default:
                return 'ℹ';
        }
    }

    /**
     * Quick methods for different message types
     */
    public static function success($message, $dismissible = true)
    {
        self::set($message, self::TYPE_SUCCESS, $dismissible);
    }

    public static function error($message, $dismissible = true)
    {
        self::set($message, self::TYPE_ERROR, $dismissible);
    }

    public static function warning($message, $dismissible = true)
    {
        self::set($message, self::TYPE_WARNING, $dismissible);
    }

    public static function info($message, $dismissible = true)
    {
        self::set($message, self::TYPE_INFO, $dismissible);
    }

    /**
     * Set flash from URL parameters (for redirects)
     */
    public static function setFromURL()
    {
        if (isset($_GET['msg']) && isset($_GET['type'])) {
            $message = Security::sanitizeInput($_GET['msg']);
            $type = Security::sanitizeInput($_GET['type']);

            // Validate type
            $validTypes = [self::TYPE_SUCCESS, self::TYPE_ERROR, self::TYPE_WARNING, self::TYPE_INFO];
            if (in_array($type, $validTypes)) {
                self::set(urldecode($message), $type);

                // Clean URL by redirecting without parameters
                $clean_url = strtok($_SERVER['REQUEST_URI'], '?');
                if ($clean_url !== $_SERVER['REQUEST_URI']) {
                    header("Location: $clean_url");
                    exit;
                }
            }
        }
    }

    /**
     * Convert to URL parameters for redirects
     */
    public static function toURL($message, $type = self::TYPE_INFO)
    {
        return 'msg=' . urlencode($message) . '&type=' . urlencode($type);
    }
}
