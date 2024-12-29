<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome</title>

    <!-- Font Icon -->
    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">

    <!-- Main css -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Bootstrap css, & JS -->
    <link rel="stylesheet" href="css/bootstrap.css">
	<script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/bootstrap.js"></script>

    <!-- Other JS -->
    <script src="assets/js/main.js"></script>

</head>
<body>
    <div class="main">

        <!-- Sign up form -->
        <section class="signup">
            <div class="container">
                <div class="signup-content">
    
                    <div class="signup-form">
                        <h2 class="form-title">Sign up</h2>
                        <form method="POST" class="register-form" id="register-form">
                            <div class="form-group">
                                <label for="name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                <input type="text" name="name" id="name" placeholder="Enter your username"/>
                            </div>
                            <div class="form-group">
                                <label for="pass"><i class="zmdi zmdi-lock"></i></label>
                                <input type="password" name="pass" id="pass" placeholder="Enter your Password"/>
                            </div>
                            <div class="form-group">
                                <label for="re-pass"><i class="zmdi zmdi-lock-outline"></i></label>
                                <input type="password" name="re_pass" id="re_pass" placeholder="Repeat your password"/>
                            </div>
                            <div class="form-group form-button">
                                <input type="submit" name="signup" id="signup" class="form-submit" value="Register"/>
                            </div>
                            <div id="error-message"></div>
                        </form>
                    </div>
                    <div class="signup-image">
                        <figure><img src="images/signup-image.jpg" alt="sing up image"></figure>
                        <a href="signin.php" class="signup-image-link">I am already member</a>
                    </div>
                </div>
            </div>
        </section>
    </div>
    

    <script>
    $(document).ready(function() {
        $("#register-form").submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            var username = $("#name").val();
            var password = $("#pass").val();
            var confirmPassword = $("#re_pass").val();
            var errorMessageElement = $("#error-message");

            // Basic client-side validation (you might want to add more)
            if (password !== confirmPassword) {
                // alert("Passwords do not match!");
                errorMessageElement.html('<div class="alert alert-info" role="alert">Passwords do not match! Re-Enter.</div>'); 
                    
                return;
            }

            // Client-side validation (JavaScript implementation of PHP logic)
            // Validate username format
            var usernameRegex = /^[a-zA-Z0-9_]+$/;
            if (!usernameRegex.test(username)) {
                // alert("Invalid username format. Please use only alphanumeric characters and underscores.");
                errorMessageElement.html('<div class="alert alert-warning" role="alert">Invalid username format. Please use only alphanumeric characters and underscores.</div>');   
                return;
            }

            // Validate password complexity
            var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
            if (password.length < 8 || !passwordRegex.test(password)) {
                // alert("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.");
                errorMessageElement.html('<div class="alert alert-primary" role="alert">Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.</div>'); 
                return;
            }

            // AJAX request to your signup API endpoint
            $.ajax({
                url: 'http://localhost/api/V1/auth/signup.php', // Replace with your actual API endpoint URL
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded', // Set the Content-Type header for form data
                data: {
                    username: username,
                    password: password
                },
                success: function(response) {
                    // Handle the API response
                    if (response.status === 'success') {
                        alert("Signup successful!");
                        // Redirect to login page or perform other actions
                        window.location.href = "login.html"; // Example: Redirect to login page
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 400) { // Check for Bad Request status code
                        var response = JSON.parse(xhr.responseText); // Parse the JSON response
                        // POP UP ERROR
                        // if (response.message === 'This username has already been taken. Select another username') {
                        //     alert(response.message); // Show the specific error message
                        // } else {
                        //     // Handle other 400 errors 
                        //     console.error("Signup error:", error);
                        //     alert("An error occurred during signup."); 
                        // }
                        // var errorMessageElement = $("#error-message"); // Assuming you have a <div id="error-message"></div> in your HTML

                        if (response.message === 'This username has already been taken. Select another username') {
                            // 2. Display the specific error message using Bootstrap's alert class
                            console.error("Signup error:", error);
                            errorMessageElement.html('<div class="alert alert-danger" role="alert">' + response.message + '</div>'); 
                        } else {
                            // Handle other 400 errors
                            console.error("Signup error:", error);
                            errorMessageElement.html('<div class="alert alert-danger" role="alert">An error occurred during signup.</div>'); 
                        }
                    } else {
                        console.error("Signup error:", error);
                        // alert("An error occurred during signup.");
                        console.error("Signup error:", error);
                        errorMessageElement.html('<div class="alert alert-danger" role="alert">An error occurred during signup.</div>'); 
                    }
                }
            });
        });
    });
    </script>
</body>
</html>