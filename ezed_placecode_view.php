<?php
require_once('ezed_auth.php');

try {
    $placecode_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$placecode_id) {
        throw new Exception('Invalid placecode ID');
    }

    // Get placecode content using bind_result instead of get_result
    $stmt = $db->prepare("SELECT placecode_id, placecode_description, placecode, placecode_code, status FROM placecodes WHERE placecode_id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $db->error);
    }
    
    $stmt->bind_param("i", $placecode_id);
    $stmt->execute();
    
    // Bind variables to the prepared statement
    $stmt->bind_result($id, $description, $placecode, $code, $status);
    
    // Fetch values
    if (!$stmt->fetch()) {
        throw new Exception('Placecode not found');
    }
    
    // Store in an array for consistent usage with the rest of the code
    $placecode = [
        'placecode_id' => $id,
        'placecode_description' => $description,
        'placecode' => $placecode,
        'placecode_code' => $code,
        'status' => $status
    ];
    
    $stmt->close();

} catch (Exception $e) {
    handleError('Placecode View Error', $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Placecode</title>
    <meta charset="utf-8">
    <link href="css/edit1.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="443B34">
    <div align="center">
        <table width="850" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000" bgcolor="#FFFFFF">
            <tr>
                <td valign="top" bordercolor="#FFFFFF">
                    <div align="center">
                        <span class="text">
                            <?= htmlspecialchars_decode($placecode['placecode_code']) ?>
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>