<?php
        // Initialize the session
        session_start();
        
        // Check if the user is logged in, otherwise redirect to login page
        if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
            header("location: login.php");
            exit;
        }
        
        // Include config file
        require_once "config.php";
        
        
        // Define variables and initialize with empty values
        $old_password = $new_password = $confirm_password = "";
        $old_password_err = $new_password_err = $confirm_password_err = "";
        $username = $email = "";
        $surname = $student_no = $contact = $module_code = ""; // Add these variables
        
        // Retrieve user's current username, email, surname, student number, contact, and module code from the database
        $id = $_SESSION["id"];
        $sql = "SELECT username, email, surname, student_no, contact, module_code FROM users WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $db_username, $db_email, $db_surname, $db_student_no, $db_contact, $db_module_code);
                    mysqli_stmt_fetch($stmt);
                    $username = $db_username;
                    $email = $db_email;
                    $surname = $db_surname; // Assign the retrieved values to these variables
                    $student_no = $db_student_no;
                    $contact = $db_contact;
                    $module_code = $db_module_code;
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        // Processing form data when form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
            // Validate old password
            if (empty(trim($_POST["old_password"]))) {
                $old_password_err = "Please enter the old password.";
            } else {
                $old_password = trim($_POST["old_password"]);
            }
        
            // Validate new password
            if (empty(trim($_POST["new_password"]))) {
                $new_password_err = "Please enter the new password.";
            } elseif (strlen(trim($_POST["new_password"])) < 6) {
                $new_password_err = "Password must have at least 6 characters.";
            } else {
                $new_password = trim($_POST["new_password"]);
            }
        
            // Validate confirm password
            if (empty(trim($_POST["confirm_password"]))) {
                $confirm_password_err = "Please confirm the password.";
            } else {
                $confirm_password = trim($_POST["confirm_password"]);
                if (empty($new_password_err) && ($new_password != $confirm_password)) {
                    $confirm_password_err = "Password did not match.";
                }
            }
        
            // Verify the old password
            $sql = "SELECT password FROM users WHERE id = ?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 1) {
                        mysqli_stmt_bind_result($stmt, $hashed_password);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (password_verify($old_password, $hashed_password)) {
                                // Old password is correct, proceed to update the password
                                if (empty($new_password_err) && empty($confirm_password_err)) {
                                    // Prepare an update statement
                                    $sql = "UPDATE users SET password = ? WHERE id = ?";
                                    if ($stmt = mysqli_prepare($link, $sql)) {
                                        // Bind variables to the prepared statement as parameters
                                        mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
        
                                        // Set parameters
                                        $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                        $param_id = $_SESSION["id"];
        
                                        // Attempt to execute the prepared statement
                                        if (mysqli_stmt_execute($stmt)) {
                                            // Password updated successfully. Destroy the session and redirect to login page
                                            session_destroy();
                                            header("location: newpassword.php");
                                            exit();
                                        } else {
                                            echo "Oops! Something went wrong. Please try again later.";
                                        }
        
                                        // Close statement
                                        mysqli_stmt_close($stmt);
                                    }
                                }
                            } else {
                                $old_password_err = "The old password is incorrect.";
                            }
                        }
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        // Close connection
        mysqli_close($link);
        ?>
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { font: 14px sans-serif; }
        .wrapper {
            width: 360px;
            padding: 20px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: -140px;
            margin-top: 150px;
        }
        /* Add CSS to make the profile picture round */
        #output-container {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            position: relative;
            overflow: hidden;
            position: absolute;
            top: 50px;
            left: 45%;
        }
        #output {
            width: 100%;
            height: 100%;
        }
        /* Style for the camera icon */
        #cameraIcon {
            position: absolute;
            top: 80;
            left: 90;
            width: 100%;
            height: 100%;
            background: url('camera_icon.png') center center no-repeat;
            background-size: 30px;
            opacity: 0.7;
            cursor: pointer;
        }
        /* Hide the file input */
        #profilePicture {
            display: none;
        }
        /* Center the title */
        h1 {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Edit Profile</h1> <!-- Add the title here -->
    <form action="Update_profile.php" method="POST" enctype="multipart/form-data">
    <label for="profilePicture" id="output-container">
                    <input type="file" accept="image/*" onchange="loadFile(event)" name="profile_picture" id="profilePicture">
                    <img id="output" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSoiNSuN7akpj7D0Z3M8_-_cehNrUgrTU_sjvG5cGU4-A&s" alt="Profile Picture"/>
                    <!-- Replace 'camera_icon.png' with your camera icon image path -->
                    <label id="cameraIcon" for="profilePicture"></label>
                </label>
                <script>
                    var loadFile = function(event) {
                        var image = document.getElementById('output');
                        image.src = URL.createObjectURL(event.target.files[0]);
                    };
                </script>
        
        <div class="wrapper">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            
         
            <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>">
                </div>
                <div class="form-group">
            <label for="surname">Surname:</label>
            <input type="text" id="surname" name="surname" class="form-control" value="<?php echo htmlspecialchars($surname); ?>">
            <span class="invalid-feedback"><?php echo $surname_err; ?></span>
        </div>
        
        <div class="form-group">
            <label for="student_no">Student Number:</label>
            <input type="text" id="student_no" name="student_no" class="form-control" value="<?php echo htmlspecialchars($student_no); ?>">
            <span class="invalid-feedback"><?php echo $student_no_err; ?></span>
        </div>
        
        <div class="form-group">
            <label for="contact">Contact:</label>
            <input type="text" id="contact" name="contact" class="form-control" value="<?php echo htmlspecialchars($contact); ?>">
            <span class="invalid-feedback"><?php echo $contact_err; ?></span>
        </div>
        
        <div class="form-group">
            <label for="module_code">Module Code:</label>
            <input type="text" id="module_code" name="module_code" class="form-control" value="<?php echo htmlspecialchars($module_code); ?>">
            <span class="invalid-feedback"><?php echo $module_code_err; ?></span>
        </div>
                <div class="form-group">
               
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                </div>
               
                <div class="form-group">
                    <label for="old_password">Old Password:</label>
                    <input type="password" name="old_password" id="old_password" class="form-control <?php echo (!empty($old_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $old_password; ?>">
                    <span class="invalid-feedback"><?php echo $old_password_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                    <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                </div>
        
                <div class="form-group">

                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                
                    <input type="submit" class="btn btn-primary" value="Save Changes">
                    <a class="btn btn-link ml-2" href="profile.php">Cancel</a>
                </div>
            </form>
        </div>
        </body>
        </html>