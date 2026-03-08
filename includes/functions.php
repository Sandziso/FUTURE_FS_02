<?php
/**
 * LeadFlow CRM - Helper Functions
 * Contains sanitization, redirection, flash messages, authentication helpers,
 * date formatting, status badges, CSRF protection, and logging utilities.
 */

// ==================== SANITIZATION & VALIDATION ====================

/**
 * Sanitize input data
 * @param string $data Raw input
 * @return string Cleaned output
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// ==================== REDIRECTION ====================

/**
 * Redirect to a given URL
 * @param string $url Destination
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

// ==================== FLASH MESSAGES ====================

/**
 * Display or set a flash message (one-time notification)
 * @param string $name Session key
 * @param string $message Message content (if setting)
 * @param string $class Bootstrap alert class
 * @return void
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $msg = $_SESSION[$name];
            $cls = $_SESSION[$name . '_class'] ?? $class;
            echo '<div class="' . $cls . ' alert-dismissible fade show" role="alert">'
                . $msg .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            unset($_SESSION[$name], $_SESSION[$name . '_class']);
        }
    }
}

// ==================== AUTHENTICATION ====================

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user's data from database (requires global $pdo)
 * @return array|false User associative array or false if not logged in
 */
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return false;
    $stmt = $pdo->prepare("SELECT id, username, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ==================== DATE FORMATTING ====================

/**
 * Format a MySQL timestamp into a readable date
 * @param string $datetime MySQL datetime or timestamp
 * @param string $format PHP date format (default: 'd M Y, H:i')
 * @return string Formatted date
 */
function formatDate($datetime, $format = 'd M Y, H:i') {
    if (empty($datetime)) return '—';
    $timestamp = strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Return a human-readable time difference (e.g., "2 minutes ago")
 * @param string $datetime MySQL datetime
 * @return string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('d M Y', $time);
    }
}

// ==================== UI HELPERS ====================

/**
 * Generate a Bootstrap badge for lead status
 * @param string $status new|contacted|converted
 * @return string HTML of the badge
 */
function statusBadge($status) {
    $classes = [
        'new'       => 'bg-primary',
        'contacted' => 'bg-warning text-dark',
        'converted' => 'bg-success'
    ];
    $class = $classes[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
}

/**
 * Get the base URL of the application (useful for assets)
 * @param string $path Optional path to append
 * @return string
 */
function baseUrl($path = '') {
    $base = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    return $base . $dir . '/' . ltrim($path, '/');
}

// ==================== CSRF PROTECTION ====================

/**
 * Generate a CSRF token and store it in session
 * @return string Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Submitted token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ==================== CURRENCY FORMATTING ====================

/**
 * Format currency in South African Rand or Swazi Lilangeni
 * @param float $amount
 * @param string $currency (ZAR or SZL) – defaults to 'ZAR' unless CURRENCY constant defined
 * @return string
 */
function formatCurrency($amount, $currency = null) {
    if ($currency === null) {
        $currency = defined('CURRENCY') ? CURRENCY : 'ZAR';
    }
    $symbol = ($currency === 'ZAR') ? 'R' : 'E';
    return $symbol . ' ' . number_format($amount, 2);
}

// ==================== LOGGING ====================

/**
 * Log user activity to database
 * @param int $user_id
 * @param string $action
 * @param string $description
 */
function logActivity($user_id, $action, $description = '') {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $description, $ip]);
}

/**
 * Simple logging to a file (useful for debugging)
 * @param string $message Log message
 * @param string $file Log file path (relative to project root)
 */
function logMessage($message, $file = 'logs/app.log') {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($file, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// ==================== DATABASE UTILITIES ====================

/**
 * Check if a database table exists
 * @param string $tableName
 * @return bool
 */
function tableExists($tableName) {
    global $pdo;
    try {
        $result = $pdo->query("SELECT 1 FROM $tableName LIMIT 1");
        return $result !== false;
    } catch (PDOException $e) {
        return false;
    }
}

// ==================== SETTINGS HELPERS ====================

/**
 * Get a system setting value from database.
 * @param string $key
 * @param mixed $default Default value if setting not found
 * @return mixed
 */
function getSetting($key, $default = null) {
    global $pdo;
    static $settings = null;
    if ($settings === null) {
        // Cache all settings in one query
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    return $settings[$key] ?? $default;
}

// ==================== LOCALISATION ====================

/**
 * Format phone number for display (simple).
 * @param string $phone
 * @return string
 */
function formatPhone($phone) {
    if (empty($phone)) return '—';
    // Add basic formatting if needed
    return $phone;
}

/**
 * Simple translation stub – extend with array or database later.
 * @param string $key
 * @param string $lang (optional)
 * @return string
 */
function __($key, $lang = null) {
    static $translations = [
        'en' => [
            'new' => 'New',
            'contacted' => 'Contacted',
            'converted' => 'Converted',
            'total_leads' => 'Total Leads',
            'pipeline_value' => 'Pipeline Value',
            'conversion_rate' => 'Conversion Rate',
        ],
        'ss' => [ // siSwati (simple demo)
            'new' => 'Intsha',
            'contacted' => 'Kuthintwile',
            'converted' => 'Kuguquliwe',
            'total_leads' => 'Lwati Lonkhe',
            'pipeline_value' => 'Linani le Pipeline',
            'conversion_rate' => 'Izinga Lekuguqula',
        ],
    ];
    $lang = $lang ?? getSetting('default_language', 'en');
    return $translations[$lang][$key] ?? $key;
}

// ==================== ANALYTICS HELPERS ====================

/**
 * Calculate average days from 'new' to 'converted' for leads.
 * @return float|int
 */
function averageConversionDays() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT AVG(DATEDIFF(updated_at, created_at)) as avg_days
        FROM leads
        WHERE status = 'converted' AND converted_to_client = 1
    ");
    $result = $stmt->fetchColumn();
    return $result ? round($result, 1) : 0;
}

/**
 * Get lead source performance (conversion count per source).
 * @return array
 */
function sourcePerformance() {
    global $pdo;
    return $pdo->query("
        SELECT source, 
               COUNT(*) as total,
               SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
        FROM leads
        WHERE source IS NOT NULL
        GROUP BY source
        ORDER BY converted DESC
    ")->fetchAll();
}

// ==================== TASK OVERDUE CHECK ====================

/**
 * Count overdue tasks for a user (or all if user_id null)
 * @param int|null $user_id
 * @return int
 */
function countOverdueTasks($user_id = null) {
    global $pdo;
    $sql = "SELECT COUNT(*) FROM tasks WHERE status = 'pending' AND due_date < CURDATE()";
    if ($user_id) {
        $stmt = $pdo->prepare($sql . " AND user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->query($sql);
    }
    return (int) $stmt->fetchColumn();
}