<?php
// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$email = $username = $password = $confirm_password = $surname = $student_no = $contact = $module_code = $email =   "";
$email_err = $username_err = $password_err = $confirm_password_err = $surname_err = $student_no_err = $contact_err = $module_code_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validate email
if (isset($_POST["email"]) && !empty(trim($_POST["email"]))) {
    $email = trim($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    }
} else {
    $email_err = "Please enter your email.";
}
// Validate surname
if (isset($_POST["surname"]) && !empty(trim($_POST["surname"]))) {
    $surname = trim($_POST["surname"]);
    // You can add additional checks here if needed
} else {
    $surname_err = "Please enter your surname.";
}

// Validate student number
if (isset($_POST["student_no"]) && !empty(trim($_POST["student_no"]))) {
    $student_no = trim($_POST["student_no"]);
    // You can add additional checks here if needed
} else {
    $student_no_err = "Student number should have 9 numbers starting with 20.";
}

// Validate contact
if (isset($_POST["contact"]) && !empty(trim($_POST["contact"]))) {
    $contact = trim($_POST["contact"]);
    // You can add additional checks here if needed
} else {
    $contact_err = "Your contact number should have 10 numbers .";
}

// Validate module code
if (isset($_POST["module_code"]) && !empty(trim($_POST["module_code"]))) {
    $module_code = trim($_POST["module_code"]);
    // You can add additional checks here if needed
} else {
    $module_code_err = "Please enter the module code that has only 7 characters.";
}
 
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))){
        $username_err = "Username can only contain letters, numbers, and underscores.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in the database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($surname_err) && empty($student_no_err) && empty($contact_err) && empty($module_code_err) && empty($email_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, surname, student_no, contact, module_code, email) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssss", $param_username, $param_password, $param_surname, $param_student_no, $param_contact, $param_module_code, $param_email);

            // Set parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_surname = $surname;
            $param_student_no = $student_no;
            $param_contact = $contact;
            $param_module_code = $module_code;
            $param_email = $email;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to the registration successful page
             header("location: registration_successful.php");
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" type="text/css" href="style.css">



 
    <style>
         body{ font: 14px sans-serif;}
        .wrapper { width: 360px; padding: 20px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
</div>
                <div class="form-group">
        <label>Surname</label>
        <input type="text" name="surname" class="form-control <?php echo (!empty($surname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $surname; ?>">
        <span class="invalid-feedback"><?php echo $surname_err; ?></span>
    </div>    

    <div class="form-group">
        <label>Student Number</label>
        <input type="text" name="student_no" class="form-control <?php echo (!empty($student_no_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $student_no; ?>">
        <span class="invalid-feedback"><?php echo $student_no_err; ?></span>
    </div>    

    <div class="form-group">
        <label>Contact</label>
        <input type="text" name="contact" class="form-control <?php echo (!empty($contact_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $contact; ?>">
        <span class="invalid-feedback"><?php echo $contact_err; ?></span>
    </div>    

    <div class="form-group">
        <label>Module Code</label>
        <input type="text" name="module_code" class="form-control <?php echo (!empty($module_code_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $module_code; ?>">
        <span class="invalid-feedback"><?php echo $module_code_err; ?></span>
    </div>    

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
        <span class="invalid-feedback"><?php echo $email_err; ?></span>
    </div>       
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>

