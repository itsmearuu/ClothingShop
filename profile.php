<?php
// profile.php
session_start();
require_once 'config.php';
require_once 'session.php';

requireLogin();

// Get profile user ID (either from URL for admin, or current user)
$profileUserId = isset($_GET['user_id']) && isAdmin() ? intval($_GET['user_id']) : $_SESSION['user_id'];

// Check if user can edit this profile
$canEdit = canEditProfile($profileUserId);

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $profileUserId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: index.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canEdit) {
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $middleName = $conn->real_escape_string($_POST['middleName']);
    $birthday = $conn->real_escape_string($_POST['birthday']);
    $gender = $conn->real_escape_string($_POST['gender']);
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $uploadDir = 'uploads/profile_pictures/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExt, $allowedExts)) {
            $fileName = $profileUserId . '_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
                // Delete old profile picture
                if ($user['profile_picture'] && file_exists($user['profile_picture'])) {
                    unlink($user['profile_picture']);
                }
                
                $profilePicture = $targetPath;
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $profilePicture, $profileUserId);
                $stmt->execute();
            }
        }
    }
    
    // Update user info
    $stmt = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, middleName = ?, birthday = ?, gender = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $firstName, $lastName, $middleName, $birthday, $gender, $profileUserId);
    
    if ($stmt->execute()) {
        // Update session if editing own profile
        if ($profileUserId == $_SESSION['user_id']) {
            $_SESSION['user_name'] = $firstName;
        }
        $success = "Profile updated successfully!";
        
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $profileUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Error updating profile.";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password']) && $canEdit) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (password_verify($currentPassword, $user['password'])) {
        if ($newPassword === $confirmPassword) {
            if (strlen($newPassword) >= 6) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $profileUserId);
                
                if ($stmt->execute()) {
                    $passwordSuccess = "Password changed successfully!";
                } else {
                    $passwordError = "Error changing password.";
                }
            } else {
                $passwordError = "Password must be at least 6 characters.";
            }
        } else {
            $passwordError = "Passwords do not match.";
        }
    } else {
        $passwordError = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ClothingShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #393E46 0%, #222831 100%);
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #948979;
        }
        
        .no-profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #2c3137;
            border: 4px solid #948979;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #948979;
        }
        
        .profile-info {
            background: #393E46;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #4a5159;
            margin-bottom: 20px;
        }
        
        .profile-info h3 {
            color: #948979;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #4a5159;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #948979;
            width: 150px;
        }
        
        .info-value {
            color: #DFD0B8;
            flex: 1;
        }
        
        .admin-badge {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-top: 10px;
        }
        
        .edit-permission-notice {
            background: rgba(148, 137, 121, 0.1);
            border-left: 4px solid #948979;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #948979;
        }
        
        .edit-permission-notice i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <header>
        <div class="mylogo" onclick="window.location.href='index.php'">CLOTHINGSHOP</div>
        <nav>
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="products.php">PRODUCTS</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <div class="logsign">
            <?php if(isLoggedIn()): ?>
                <a href="profile.php"><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                <?php if(isAdmin()): ?>
                    <a href="admin/index.php"><i class="fas fa-tachometer-alt"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fa-regular fa-user"></i></a>
            <?php endif; ?>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i><span id="cart-count" class="cart-badge">0</span></a>
        </div>
    </header>

    <div class="profile-container">
        <?php if (isAdmin() && $profileUserId != $_SESSION['user_id']): ?>
            <div class="edit-permission-notice">
                <i class="fas fa-user-shield"></i>
                <strong>Admin Mode:</strong> You are editing another user's profile.
                <a href="admin/users.php" class="btn btn-sm btn-secondary ms-3">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        <?php endif; ?>

        <?php if (!$canEdit): ?>
            <div class="alert alert-warning">
                <i class="fas fa-lock"></i> You don't have permission to edit this profile.
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="profile-picture-container">
                <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
                <?php else: ?>
                    <div class="no-profile-picture">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h2 style="color: #DFD0B8; margin-bottom: 10px;">
                <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?>
            </h2>
            <p style="color: #948979; margin-bottom: 10px;"><?php echo htmlspecialchars($user['email']); ?></p>
            <?php if ($user['role'] === 'admin'): ?>
                <span class="admin-badge">
                    <i class="fas fa-crown"></i> ADMINISTRATOR
                </span>
            <?php endif; ?>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="profile-info">
            <h3><i class="fas fa-user-circle"></i> Profile Information</h3>
            
            <?php if ($canEdit): ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">First Name</label>
                            <input type="text" class="form-control" name="firstName" value="<?php echo htmlspecialchars($user['firstName']); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">Middle Name</label>
                            <input type="text" class="form-control" name="middleName" value="<?php echo htmlspecialchars($user['middleName']); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">Last Name</label>
                            <input type="text" class="form-control" name="lastName" value="<?php echo htmlspecialchars($user['lastName']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">Birthday</label>
                            <input type="date" class="form-control" name="birthday" value="<?php echo $user['birthday']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" style="color: #948979; font-weight: 600;">Gender</label>
                            <select class="form-control" name="gender" required>
                                <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" style="color: #948979; font-weight: 600;">Profile Picture</label>
                        <input type="file" class="form-control" name="profile_picture" accept="image/*">
                        <small class="text-muted">Leave empty to keep current picture</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            <?php else: ?>
                <div class="info-row">
                    <div class="info-label">First Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['firstName']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Middle Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['middleName']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Last Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['lastName']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Birthday:</div>
                    <div class="info-value"><?php echo date('F d, Y', strtotime($user['birthday'])); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gender:</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['gender']); ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Change Password -->
        <?php if ($canEdit): ?>
            <div class="profile-info">
                <h3><i class="fas fa-lock"></i> Change Password</h3>
                
                <?php if (isset($passwordSuccess)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $passwordSuccess; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($passwordError)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $passwordError; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" style="color: #948979; font-weight: 600;">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #948979; font-weight: 600;">New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color: #948979; font-weight: 600;">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Account Details -->
        <div class="profile-info">
            <h3><i class="fas fa-info-circle"></i> Account Details</h3>
            <div class="info-row">
                <div class="info-label">Account Type:</div>
                <div class="info-value">
                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Member Since:</div>
                <div class="info-value"><?php echo date('F d, Y', strtotime($user['createdAt'])); ?></div>
            </div>
        </div>
        <div class="profile-info">
    <h3><i class="fas fa-shopping-bag"></i> My Orders</h3>
    <p style="color: #948979; margin-bottom: 20px;">
        View and track all your orders
    </p>
    <a href="my_orders.php" class="btn btn-primary">
        <i class="fas fa-list"></i> View My Orders
    </a>
</div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col">
                    <p class="Clo">CLOTHINGSHOP</p>
                    <p>Empowering customers with choice, confidence, and convenience—ClothingShop is your trusted destination for modern online shopping.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>