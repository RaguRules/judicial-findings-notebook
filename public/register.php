<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome</title>

    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet" href="css/bootstrap.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/bootstrap.js"></script>

    <script src="js/main.js"></script>
    <script src="js/crypto-js.min.js" ></script>

</head>
<body>
<div class="main" style="padding-top: 50px; padding-bottom: 1px;">
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
                <p style="text-align: center; font-style: italic; font-weight: bold;">This system was developed in 2025 by Courts' Interpreter Srirajeswaran Raguraj. For any bugs/ errors, please contact: +(94)777958841.</p>
            </div>
        </section>
    </div>

    <script>
        // Function to get a cookie by name
        function getCookie(name) {
            var cookieValue = null;
            if (document.cookie && document.cookie !== '') {
                var cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = cookies[i].trim();
                    if (cookie.substring(0, name.length + 1) === (name + '=')) {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }
            return cookieValue;
        }

        // Function to validate the access token
        function validateAccessToken(accessToken) {
            $.ajax({
                url: 'http://localhost/api/V1/auth/validatetoken.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ "token": accessToken }),
                // jQuery and the server responds with a status code like 401 Unauthorized, it’s treated as an error response. 
                // For Expired token, API server returns 401 which will be considered as error, so we read, and do the needful from xhr in error block.
                success: function(response) {
                    if (response.status === 'success') {
                        // Valid token, redirect to homepage
                        window.location.href = "http://localhost/public/home.php";
                    } else {
                            console.log("No useful token available, proceed with login");
                    }
                },
                error: function(xhr, status, error) {
                    // Check for 401 status code (Unauthorized)
                    if (xhr.status === 401) {
                        console.log("Token is invalid or expired.");
                        var refreshToken = getCookie('refresh_token');
                        if (refreshToken) {
                            refreshAccessToken(refreshToken);
                        } else {
                            console.log("No refresh token available, proceed with login");
                        }
                    } else {
                        // Handle other errors (network issues, server errors)
                        console.error("Error validating token:", error);
                    }
                }
            });
        }

        // Function to refresh the access token
        function refreshAccessToken(refreshToken) {
            $.ajax({
                url: 'http://localhost/api/V1/auth/refreshaccesstoken.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ refresh_token: refreshToken }),
                success: function(response) {
                    if (response.status === 'success') {
                        // Update the access token and redirect
                        localStorage.setItem('access_token', response.access_token);
                        window.location.href = "http://localhost/public/home.php";
                    } else {
                        // Invalid refresh token, proceed with signup form display
                        console.log("Invalid refresh token, proceed with signup");
                    }
                },
                error: function(xhr, status, error) {
                    // Error refreshing token, proceed with signup form display
                    console.error("Error refreshing token:", error);
                }
            });
        }

        $(document).ready(function() {
            // Check for valid access token or refresh token on page load
            var accessToken = localStorage.getItem('access_token');
            var refreshToken = getCookie('refresh_token');

            if (accessToken) {
                validateAccessToken(accessToken);
            } else if (refreshToken) {
                refreshAccessToken(refreshToken);
            } else {
                // No valid token, proceed with signup form display
                console.log("No valid token, proceed with signup");
            }

            $("#register-form").submit(function(event) {
                event.preventDefault(); // Prevent default form submission

                var username = $("#name").val();
                var password = $("#pass").val();
                var confirmPassword = $("#re_pass").val();
                var errorMessageElement = $("#error-message");

                // Basic client-side validation
                if (password !== confirmPassword) {
                    errorMessageElement.html('<div class="alert alert-info" role="alert">Passwords do not match! Re-Enter.</div>');
                    return;
                }

                // Validate username format
                var usernameRegex = /^[a-zA-Z0-9_]+$/;
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

                // AJAX request to your signup API endpoint
                $.ajax({
                    url: 'http://localhost/api/V1/auth/signup.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        username: username,
                        password: password // Note: Password will be hashed on the server-side
                    }),
                    success: function(response) {
                        if (response.status === 'success') {
                            // Redirect to login page with success message
                            window.location.href = "login.php?signup=success";
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