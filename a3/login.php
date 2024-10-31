<?php
include('includes/header.php');
include('includes/nav.php');
include('includes/db_connect.php');

$username = $password = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $encryptedPassword = sha1($password);

    if (isset($_POST['login'])) {
        $sql = "SELECT userID, username FROM users WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $encryptedPassword);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
        
        $stmt->close();
    } elseif (isset($_POST['register'])) {
        $checkSql = "SELECT * FROM users WHERE username = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Username already taken.";
        } else {
            $regSql = "INSERT INTO users (username, password, reg_date) VALUES (?, ?, NOW())";
            $regStmt = $conn->prepare($regSql);
            $regStmt->bind_param("ss", $username, $encryptedPassword);
            
            if ($regStmt->execute()) {
                $success = "User registered successfully! Please log in.";
            } else {
                $error = "Error registering user.";
            }

            $regStmt->close();
        }

        $checkStmt->close();
    }

    $conn->close();
}
?>

<style>
    main {
        padding: 20px;
    }
</style>

<main>
    <h1>Login</h1>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        
        <button type="submit" name="login" class="btn btn-primary">Login</button>
    </form>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
</main>

<?php include('includes/footer.php'); ?>
