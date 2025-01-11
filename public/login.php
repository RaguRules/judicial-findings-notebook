<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome Back</title>

    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="main" style="padding-top: 50px;">
        <section class="sign-in">
            <div class="container">
            <?php
                if (isset($_GET['signup']) && $_GET['signup'] === 'success') {
                    echo '<div class="alert alert-success" role="alert">Your registration is completed. Please login to use the system.</div>';
                }
            ?>
                <div class="signin-content">
                    <div class="signin-image">
                        <figure><img src="images/signin-image.jpg" alt="sign in image"></figure>
                        <a href="register.php" class="signup-image-link">Create an account</a>
                    </div>

                    <div class="signin-form">
                        <h2 class="form-title">Sign in</h2>
                        <form method="POST" class="register-form" id="login-form">
                            <div class="form-group">
                                <label for="your_name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                <input type="text" name="your_name" id="your_name" placeholder="Username"/>
                            </div>
                            <div class="form-group">
                                <label for="your_pass"><i class="zmdi zmdi-lock"></i></label>
                                <input type="password" name="your_pass" id="your_pass" placeholder="Password"/>
                            </div>
                            <div class="form-group form-button">
                                <input type="submit" name="signin" id="signin" class="form-submit" value="Log in"/>
                            </div>
                        </form>
                        <div id="error-message"></div>

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

        // Function to set a cookie
        function setCookie(name, value, days) {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        // Function to check for a valid access token and redirect if necessary
        function checkAccessToken() {
            var accessToken = localStorage.getItem('access_token');
            var refreshToken = getCookie('refresh_token');

            if (accessToken) {
                validateAccessToken(accessToken);
            } else if (refreshToken) {
                refreshAccessToken(refreshToken);
            } else {
                // No valid token, proceed with login form display
                console.log("No valid token, proceed with login");
            }
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
                url: 'http://localhost/api/V1/auth/refreshaccesstoken.php', // Replace with your actual API endpoint
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ refresh_token: refreshToken }),
                success: function(response) {
                    if (response.status === 'success') {
                        // Update the access token and redirect
                        localStorage.setItem('access_token', response.access_token);
                        window.location.href = "http://localhost/public/home.php";
                    } else {
                        console.log("Invalid refresh token, proceed with login");
                    }
                },
                error: function(xhr, status, error) {
                    // Error refreshing token, proceed with login form display
                    console.error("Error refreshing token:", error);
                }
            });
        }

        // Function to handle the login form submission
        function handleLoginFormSubmission() {
            $("#login-form").submit(function(event) {
                event.preventDefault();

                // Get the username and password from the form fields
                var username = $("#your_name").val();
                var password = $("#your_pass").val();

                // Basic client-side validation
                if (!username || !password) {
                    $("#error-message").html('<div class="alert alert-warning" role="alert">Please enter both username and password.</div>');
                    return;
                }

                $.ajax({
                    url: 'http://localhost/api/V1/auth/login.php',
                    type: 'POST',
                    contentType: 'application/x-www-form-urlencoded',
                    data: {
                        username: username,
                        password: password
                    },
                    success: function(response, textStatus, xhr) {
                        // Remove any previous messages
                        $("#error-message").empty();
                        $("#success-message").empty();

                        if (response.status === 'success') {
                            // Get tokens from response headers
                            var accessToken = xhr.getResponseHeader('X-Access-Token');
                            var refreshToken = xhr.getResponseHeader('X-Refresh-Token');

                            // Store tokens, & set Cookies
                            localStorage.setItem('access_token', accessToken);
                            setCookie('refresh_token', refreshToken, 30);

                            // Redirect to the home page
                            window.location.href = "http://localhost/public/home.php";
                        } else {
                            // Display error message
                            $("#error-message").html('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                '<strong>Login Failed!</strong> ' + response.message +
                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Login error:", error, xhr, status);
                        $("#error-message").html('<div class="alert alert-danger" role="alert">An error occurred during login. Double check your Username/ Password</div>');
                    }
                });
            });
        }

        $(document).ready(function() {
            // Check for a valid access token on page load
            checkAccessToken();

            // Handle the login form submission
            handleLoginFormSubmission();
        });
    </script>
</body>
</html>