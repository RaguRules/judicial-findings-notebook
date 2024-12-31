<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome</title>

    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">

    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet" href="css/bootstrap.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/bootstrap.js"></script>

    <script src="assets/js/main.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js" integrity="sha512-a+SUDuw+8bNaWtk5e2r3sgo7D+r+v67b9n+j+Pz+b9J59655/z5d+454JomQ+pGvWl4GZ5mG4k/fEM4d5H/w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->

</head>
<body>
    <div class="main">

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
                        <a href="login.php" class="signup-image-link">I am already member</a>
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
            errorMessageElement.html('<div class="alert alert-info" role="alert">Passwords do not match! Re-Enter.</div>');
            return;
        }

        // Client-side validation (JavaScript implementation of PHP logic)
        // Validate username format (Allow alphanumeric characters, underscores, and numbers)
        var usernameRegex = /^[a-zA-Z0-9_]+$/; // Corrected regex
        if (!usernameRegex.test(username)) {
            errorMessageElement.html('<div class="alert alert-warning" role="alert">Invalid username format. Please use only alphanumeric characters and underscores.</div>');
            return;
        }

        // Validate password complexity
        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
        if (password.length < 8 || !passwordRegex.test(password)) {
            errorMessageElement.html('<div class="alert alert-primary" role="alert">Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.</div>');
            return;
        }

            // Hash the password using SHA-256 (Example - Not sufficient for production on its own)
            // var hashedPassword = CryptoJS.SHA256(password).toString();

            // AJAX request to your signup API endpoint
            $.ajax({
                url: 'http://localhost/api/V1/auth/signup.php', // Replace with your actual API endpoint URL
                type: 'POST',
                contentType: 'application/json', // Set the Content-Type header for JSON
                data: JSON.stringify({ // Send data as JSON
                    username: username,
                    password: password // Send the hashed password
                }),
                success: function(response) {
                    // Handle the API response
                    if (response.status === 'success') {
                        // alert("Signup successful!");
                        // Redirect to login page or perform other actions
                        window.location.href = "login.php?signup=success"; // Example: Redirect to login page
                    } else {
                        errorMessageElement.html('<div class="alert alert-danger" role="alert">' + response.message + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    var response = xhr.responseJSON;
                    if (response && response.message) {
                        errorMessageElement.html('<div class="alert alert-danger" role="alert">' + response.message + '</div>');
                    } else {
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