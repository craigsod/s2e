<?php
declare(strict_types=1);

require_once('../../Connections/studioAdmin_i.php');

// Initialize session with security settings
if (!isset($_SESSION)) {
    session_start([
        'cookie_httponly' => 1,
        'cookie_secure' => 1,
        'use_only_cookies' => 1
    ]);
}

// Authorization function
function isAuthorized(string $strUsers, string $strGroups, ?string $UserName, ?string $UserGroup): bool {
    if (empty($UserName)) {
        return false;
    }
    $arrUsers = explode(",", $strUsers);
    $arrGroups = explode(",", $strGroups);
    return in_array($UserName, $arrUsers) || in_array($UserGroup, $arrGroups);
}

// Handle logout
$logoutAction = $_SERVER['PHP_SELF'] . "?doLogout=true";
if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
    $logoutAction .= "&" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_GET['doLogout']) && $_GET['doLogout'] === "true") {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

// Access control
$MM_authorizedUsers = "3,1";
if (!isset($_SESSION['MM_Username']) || !isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])) {
    $MM_referrer = $_SERVER['PHP_SELF'];
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) {
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
    }
    header("Location: index.php?accesscheck=" . urlencode($MM_referrer));
    exit;
}

// Get database connection
try {
    $db = DatabaseConnection::getInstance();
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: db_error.htm");
    exit;
}

// Common error handling function
function handleError($error_sub, $error_msg) {
    error_log($error_msg);
    mail('craig@studioofdance.com', $error_sub, $error_msg);
    header("Location: db_error.htm");
    exit;
}