<?php
declare(strict_types=1);

require_once('../../Connections/studioAdmin_i.php');

session_start([
    'cookie_httponly' => 1,
    'cookie_secure' => 1,
    'use_only_cookies' => 1,
    'cookie_samesite' => 'Strict'
]);

class SiteManager {
    private mysqli $db;
    private array $siteInfo;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
        $this->loadSiteInfo();
    }

    private function loadSiteInfo(): void {
        $query = "SELECT studio_name, css_path, head_image, URL FROM site LIMIT 1";
        $result = $this->db->query($query);
        
        if (!$result) {
            throw new Exception('Failed to load site info');
        }
        
        $this->siteInfo = $result->fetch_assoc();
        $result->free();

        $_SESSION['site_name'] = $this->siteInfo['studio_name'];
        $_SESSION['css_path'] = $this->siteInfo['css_path'];
        $_SESSION['head_image'] = $this->siteInfo['head_image'];
        $_SESSION['URL'] = $this->siteInfo['URL'];
    }

    public function handleLogin(string $username, string $password): array {
        $username = filter_var($username, FILTER_SANITIZE_STRING);
        
        $stmt = $this->db->prepare("SELECT username, pwd, access_level, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $_SESSION['MM_Username'] = $username;
            $_SESSION['MM_UserGroup'] = $user['access_level'];
            $_SESSION['IsAuthorized'] = true;

            return [
                'success' => true,
                'status' => $user['status'],
                'redirect' => $user['status'] === 'New' ? 'ezed_newuser.php' : 'ezed_admin.php'
            ];
        }

        return ['success' => false, 'error' => 'Invalid username or password'];
    }

    public function getSiteName(): string {
        return $this->siteInfo['studio_name'];
    }
}

$loginError = '';

try {
    $siteManager = new SiteManager();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
        $result = $siteManager->handleLogin($_POST['username'], $_POST['pwd']);
        
        if ($result['success']) {
            header("Location: " . $result['redirect']);
            exit;
        }
        $loginError = $result['error'];
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("Location: db_error.htm");
    exit;
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title><?= htmlspecialchars($siteManager->getSiteName()) ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/edit1.css" rel="stylesheet">
</head>
<body bgcolor="443B34">
    <div align="center">
        <p>&nbsp;</p>
        <table width="500" border="0" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
            <tr>
                <td width="500" align="center" valign="top">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td bordercolor="#000000" bgcolor="#CFCFCF">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td height="30">
                                            <div align="center">
                                                <span class="subhead"><strong>Simple2Edit</strong> website editing system</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="50" bgcolor="#005DAB">
                                            <div align="center">
                                                <span class="sitename"><?= htmlspecialchars($siteManager->getSiteName()) ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="82%" border="0" align="center" cellpadding="8" cellspacing="0" bordercolor="999999">
                        <tr>
                            <td>
                                <form name="login" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                                    <span class="subhead">Login</span>
                                    <?= $loginError ? "<p class='warning'>" . htmlspecialchars($loginError) . "</p>" : "&nbsp;" ?>
                                    <p align="left">
                                        <span class="text">Username:</span>
                                        <input name="username" type="text" class="text" id="username" size="20" required>
                                    </p>
                                    <p align="left">
                                        <span class="text">Password:</span>
                                        <input name="pwd" type="password" class="text" size="20" required>
                                    </p>
                                    <p align="left">
                                        <input name="Login" type="submit" id="Login" value="Login">
                                    </p>
                                    <input type="hidden" name="javascriptenabled" value="1">
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td height="35">
                                <p>&nbsp;&nbsp;
                                    <a href="ezed_forgotpwd.php">
                                        <span class="link">Click here if you forgot your username or password</span>
                                    </a>
                                </p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <script>
        document.login.username.focus();
    </script>
</body>
</html>