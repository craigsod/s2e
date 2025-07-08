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

// Get site URL from session with fallback
$siteurl = $_SESSION['URL'] ?? '';
if (empty($siteurl)) {
    // Fallback to server name if available
    $siteurl = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 's2e-demo.com';
}

$userFound = false;
$emailSent = false;
$errorMessage = "";
$db_username = "";
$db_pwd = "";
$db_first_name = "";
$db_email = "";

try {
    // Get database connection
    $db = DatabaseConnection::getInstance();
    
    // Process form submission
    if (isset($_POST['sendpwd'])) {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if (empty($username) && empty($email)) {
            throw new Exception("No username or email provided");
        }
        
        // Prepare query to find user by username or email
        $query = "SELECT username, pwd, first_name, email FROM users WHERE ";
        if (!empty($username)) {
            $query .= "username = ?";
            $params = [$username];
            $types = "s";
        } else {
            $query .= "email = ?";
            $params = [$email];
            $types = "s";
        }
        
        $stmt = $db->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $db->error);
        }
        
        $stmt->bind_param($types, $params[0]);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }
        
        // Using bind_result instead of get_result for cross-server compatibility
        $stmt->bind_result($db_username, $db_pwd, $db_first_name, $db_email);
        
        // Fetch the result
        $userFound = $stmt->fetch();
        $stmt->close();
        
        // Process the result if user found
        if ($userFound) {
            // Check if we have an email address for this user
            if (!empty($db_email)) {
                // Send email with login information
                $subject = "Website maintenance login";
                $message = "\r\n" . "Hello " . $db_first_name . "\r\n";
                $message .= "\r\n" . "Here are your username and password to the maintenance system of your website" . "\r\n";
                $message .= "\r\n" . "Username: " . $db_username;
                $message .= "\r\n" . "Password: " . $db_pwd;
                
                // Add headers for better deliverability
                $headers = "From: noreply@" . $siteurl . "\r\n" .
                           "Reply-To: noreply@" . $siteurl . "\r\n" .
                           "X-Mailer: PHP/" . phpversion();
                
                $emailSent = mail($db_email, $subject, $message, $headers);
                
                if (!$emailSent) {
                    $errorMessage = "Failed to send email. Please contact the administrator.";
                }
            }
        }
    }
    
} catch (Exception $e) {
    $errorMessage = "Error: " . $e->getMessage();
    error_log($errorMessage);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($_SESSION['site_name'] ?? 'Recover login information') ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/edit1.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="443B34">
    <div align="center">
        <table width="600px" border="0" cellpadding="12" cellspacing="0" bordercolor="999999" bgcolor="#FFFFFF">
            <tr>
                <td bgcolor="#CFCFCF">
                    <div align="left" class="subhead">Recover password</div>
                </td>
            </tr>
            <tr>
                <td>
                    <?php if(isset($_POST['sendpwd'])): ?>
                        <?php if($userFound): ?>
                            <?php if(empty($db_email)): ?>
                                <p>&nbsp;</p><p>&nbsp;</p>
                                <blockquote>
                                    <p class='warning'>There is no email on record for your username.</p>
                                    <p class='text'><strong>Please contact the site administrator to have your email information updated.</strong></p>
                                    <p><br><a href='/edit' class='text'>Return to Login page</a></p>
                                </blockquote>
                                <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
                            <?php elseif($emailSent): ?>
                                <blockquote>
                                    <p class='text'><br>An email containing your login information has been sent to <?= htmlspecialchars($db_email) ?>.</p>
                                    <p><br><a href='/edit' class='text'>Return to Login page</a></p><br><br><br>
                                </blockquote>
                            <?php else: ?>
                                <blockquote>
                                    <p class='warning'>We found your account but were unable to send the email. <?= htmlspecialchars($errorMessage) ?></p>
                                    <p><br><a href='/edit' class='text'>Return to Login page</a></p><br><br><br>
                                </blockquote>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if(isset($_POST['email']) && !empty($_POST['email'])): ?>
                                <?php
                                $pattern = '/^[^@]+@[^\s\r\n\'";,@%]+$/';
                                if (!preg_match($pattern, trim($_POST['email'] ?? ''))) :
                                ?>
                                    <blockquote><p class="warning">Please enter a valid email address</p></blockquote>
                                <?php else: ?>
                                    <blockquote><p class='warning'>We could not find your account. Please verify your information</p></blockquote>
                                <?php endif; ?>
                            <?php else: ?>
                                <blockquote><p class='warning'>We could not find your account. Please verify your information</p></blockquote>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <form method="post" name="login" id="login" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                        <p class="text">
                            Please verify yourself by providing either your username or email address below.<br>
                            An email will be sent to the email address we have on file with your login information.
                        </p>
                        <p>
                            <span class="text">Username:</span>
                            <input name="username" type="text" id="username">
                            <br><br>
                            <span class="text">OR</span>
                        </p>
                        <p>
                            <span class="text">Email:</span>
                            <input type="text" name="email">
                            <span class="text">(must be the same as the one we have on file)</span>
                        </p>
                        <p>
                            <input name="sendpwd" type="submit" id="sendpwd" value="Submit">
                        </p>
                    </form>
                    <script>
                        document.login.username.focus();
                    </script>
                </td>
            </tr>
            <tr>
                <td height="35"><p><a href='/edit' class="link">Return to login page</a></p></td>
            </tr>
            <tr>
                <td height="10" bgcolor="#CFCFCF">&nbsp;</td>
            </tr>
        </table>
    </div>
</body>
</html>