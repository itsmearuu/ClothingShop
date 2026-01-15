<?php
/**
 * USER PROFILE PAGE - profile.php
 * Allows users to view and edit their profile information
 * Features:
 * - View current profile data
 * - Edit personal information (first name, last name, middle name, email, birthday, gender)
 * - Change password
 * - Upload/change profile picture
 */

require_once 'session.php';
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

/**
 * PROFILE UPDATE HANDLER
 * Processes profile form submission
 * Updates user credentials in database
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $middleName = trim($_POST['middleName'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    
    // Validate required fields
    if (empty($firstName) || empty($lastName)) {
        $error_message = "First name and last name are required.";
    } else {
        // Update user profile
        $updateQuery = "UPDATE users SET firstName = ?, lastName = ?, middleName = ?, birthday = ?, gender = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("sssssi", $firstName, $lastName, $middleName, $birthday, $gender, $user_id);
        
        if ($updateStmt->execute()) {
            $success_message = "Profile updated successfully!";
            // Update session user name
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            // Refresh user data
            $user['firstName'] = $firstName;
            $user['lastName'] = $lastName;
            $user['middleName'] = $middleName;
            $user['birthday'] = $birthday;
            $user['gender'] = $gender;
        } else {
            $error_message = "Error updating profile. Please try again.";
        }
    }
}

/**
 * PASSWORD CHANGE HANDLER
 * Processes password change request
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        $error_message = "Current password is incorrect.";
    } elseif (empty($newPassword)) {
        $error_message = "New password cannot be empty.";
    } elseif ($newPassword !== $confirmPassword) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($newPassword) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } else {
        // Hash and update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $passwordQuery = "UPDATE users SET password = ? WHERE id = ?";
        $passwordStmt = $conn->prepare($passwordQuery);
        $passwordStmt->bind_param("si", $hashedPassword, $user_id);
        
        if ($passwordStmt->execute()) {
            $success_message = "Password changed successfully!";
        } else {
            $error_message = "Error changing password. Please try again.";
        }
    }
}

/**
 * PROFILE PICTURE UPLOAD HANDLER
 * Processes profile picture upload
 * Validates file type and size
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $uploadDir = 'uploads/profile_pictures/';
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = "File upload error.";
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $error_message = "Only image files (JPEG, PNG, GIF, WebP) are allowed.";
    } elseif ($file['size'] > $maxFileSize) {
        $error_message = "File size must not exceed 5MB.";
    } else {
        // Generate unique filename
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $user_id . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $filename;
        
        // Delete old profile picture if exists
        if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
            unlink($user['profile_picture']);
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Update database with new profile picture path
            $pictureQuery = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $pictureStmt = $conn->prepare($pictureQuery);
            $pictureStmt->bind_param("si", $filePath, $user_id);
            
            if ($pictureStmt->execute()) {
                $success_message = "Profile picture updated successfully!";
                $user['profile_picture'] = $filePath;
            } else {
                $error_message = "Error saving profile picture. Please try again.";
                unlink($filePath);
            }
        } else {
            $error_message = "Error uploading file. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - ClothingShop</title>
    
    <!-- Fonts & icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Oswald:wght@200..700&family=Poppins:wght@400;500;800&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    
    <style>
        body {
            background-color: #222831;
            color: #DFD0B8;
        }
        
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .profile-container h1 {
            color: #DFD0B8;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
            padding: 30px;
            background: #393E46;
            border-radius: 10px;
            border: 1px solid #4a5159;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #948979;
        }
        
        .profile-info h2 {
            margin-bottom: 10px;
            color: #DFD0B8;
        }
        
        .profile-info p {
            margin: 5px 0;
            color: #b5a89a;
        }
        
        .form-section {
            background: #393E46;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #4a5159;
        }
        
        .form-section h3 {
            margin-bottom: 20px;
            color: #DFD0B8;
            border-bottom: 2px solid #948979;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #DFD0B8;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #4a5159;
            border-radius: 5px;
            font-size: 14px;
            background-color: #2c3137;
            color: #DFD0B8;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #948979;
            box-shadow: 0 0 5px rgba(148, 137, 121, 0.3);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-row.full {
            grid-template-columns: 1fr;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #948979 0%, #7a6e60 100%);
            color: #222831;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: linear-gradient(135deg, #a39a8a 0%, #8a7d70 100%);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .alert-error {
            background-color: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .picture-upload-area {
            border: 2px dashed #4a5159;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s;
            background-color: rgba(148, 137, 121, 0.05);
            color: #DFD0B8;
        }
        
        .picture-upload-area:hover {
            border-color: #948979;
            background-color: rgba(148, 137, 121, 0.1);
        }
        
        .picture-upload-area input {
            display: none;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <div class="mylogo">CLOTHINGSHOP</div>
        <nav>
            <a href="index.php">HOME</a>
            <a href="products.php">PRODUCTS</a>
            <a href="about.php">ABOUT</a>
            <a href="contact.php">CONTACT</a>
        </nav>
        
        <div class="logsign">
            <?php if(isLoggedIn()): ?>
                <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fa-regular fa-user"></i></a>
            <?php endif; ?>
            <a href="cart.php" id="cart-link"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
        </div>
    </header>

    <div class="profile-container">
        <h1>My Profile</h1>
        
        <!-- Success/Error Messages -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <!-- Profile Header with Picture -->
        <div class="profile-header">
            <div>
                <?php
                $profilePic = !empty($user['profile_picture']) && file_exists($user['profile_picture']) 
                    ? $user['profile_picture'] 
                    : 'https://via.placeholder.com/150';
                ?>
                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-picture">
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h2>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($user['createdAt'])); ?></p>
                <p><strong>Birthday:</strong> <?php echo !empty($user['birthday']) ? date('F d', strtotime($user['birthday'])) : 'Not set'; ?></p>
            </div>
        </div>

        <!-- Profile Picture Upload Section -->
        <div class="form-section">
            <h3>Profile Picture</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="picture-upload-area" onclick="document.getElementById('pictureInput').click();">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 30px; color: #948979; margin-bottom: 10px; display: block;"></i>
                    <p><strong>Click to upload or drag and drop</strong></p>
                    <p style="color: #666; font-size: 12px;">PNG, JPG, GIF or WebP (Max 5MB)</p>
                    <input type="file" id="pictureInput" name="profile_picture" accept="image/*" onchange="this.form.submit();">
                </div>
            </form>
        </div>

        <!-- Edit Profile Section -->
        <div class="form-section">
            <h3>Personal Information</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="middleName">Middle Name (Optional)</label>
                    <input type="text" id="middleName" name="middleName" value="<?php echo htmlspecialchars($user['middleName'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="birthday">Birthday</label>
                        <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">--Select Gender--</option>
                            <option value="Male" <?php echo ($user['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($user['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($user['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" name="update_profile" class="btn-submit">Update Profile</button>
            </form>
        </div>

        <!-- Change Password Section -->
        <div class="form-section">
            <h3>Change Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <button type="submit" name="change_password" class="btn-submit">Change Password</button>
            </form>
        </div>

        <!-- Back to Home -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #948979; text-decoration: none; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col">
                    <p class="Clo">CLOTHINGSHOP</p>
                    <p>Empowering customers with choice, confidence, and convenience—ClothingShop is your trusted destination for modern online shopping.</p>
                </div>
                <div class="col">
                    <p class="Com">COMPANY</p>
                    <div class="footer-links">
                        <a href="index.php">HOME</a>
                        <a href="products.php">PRODUCTS</a>
                        <a href="about.php">ABOUT</a>
                        <a href="contact.php">CONTACT</a>
                    </div>
                </div>
                <div class="col">
                    <p class="Git">GET IN TOUCH</p>
                    <p>+63 902 6488 930</p>
                    <p>contact@ClothingShop.com</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
