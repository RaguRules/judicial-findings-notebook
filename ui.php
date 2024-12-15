<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" defer></script>
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center mb-4">Notes App</h1>

        <!-- Login Form -->
        <div id="login-section" class="mb-4">
            <h3>Login</h3>
            <form id="login-form">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="login-username" placeholder="Enter username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="login-password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>

        <!-- Notes Section -->
        <div id="notes-section" class="d-none">
            <div class="mb-4">
                <h3>Your Notes</h3>
                <ul class="list-group" id="notes-list">
                    <!-- Notes will be dynamically added here -->
                </ul>
            </div>
            <div>
                <h4>Create a New Note</h4>
                <form id="note-form">
                    <div class="mb-3">
                        <textarea class="form-control" id="note-content" rows="3" placeholder="Write your note here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Add Note</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Store the user's token after login
            let authToken = null;

            // Handle login form submission
            $("#login-form").on("submit", function (event) {
                event.preventDefault();
                const username = $("#login-username").val();
                const password = $("#login-password").val();

                // AJAX POST request for login
                $.ajax({
                    url: "api/login.php",
                    method: "POST",
                    data: { username, password },
                    success: function (response) {
                        if (response.token) {
                            authToken = response.token; // Store the token
                            $("#login-section").addClass("d-none");
                            $("#notes-section").removeClass("d-none");
                            loadNotes(); // Load notes after login
                        } else {
                            alert("Login failed: " + response.error);
                        }
                    },
                    error: function () {
                        alert("An error occurred while logging in.");
                    }
                });
            });

            // Function to load notes
            function loadNotes() {
                // AJAX GET request to fetch notes
                $.ajax({
                    url: "api/get-notes.php",
                    method: "GET",
                    headers: { Authorization: authToken },
                    success: function (response) {
                        const notesList = $("#notes-list");
                        notesList.empty(); // Clear previous notes

                        if (response.length > 0) {
                            response.forEach(note => {
                                notesList.append(`
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${note.content}
                                        <button class="btn btn-danger btn-sm delete-note" data-id="${note.id}">Delete</button>
                                    </li>
                                `);
                            });
                        } else {
                            notesList.append('<li class="list-group-item">No notes found.</li>');
                        }
                    },
                    error: function () {
                        alert("An error occurred while fetching notes.");
                    }
                });
            }

            // Handle note creation
            $("#note-form").on("submit", function (event) {
                event.preventDefault();
                const content = $("#note-content").val();

                // AJAX POST request to create a note
                $.ajax({
                    url: "api/create-note.php",
                    method: "POST",
                    headers: { Authorization: authToken },
                    data: { content },
                    success: function (response) {
                        if (response.message) {
                            $("#note-content").val(""); // Clear the input field
                            loadNotes(); // Refresh the notes list
                        } else {
                            alert("Failed to create note: " + response.error);
                        }
                    },
                    error: function () {
                        alert("An error occurred while creating a note.");
                    }
                });
            });

            // Handle note deletion
            $("#notes-list").on("click", ".delete-note", function () {
                const noteId = $(this).data("id");

                // AJAX DELETE request to delete a note
                $.ajax({
                    url: "api/delete-note.php",
                    method: "DELETE",
                    headers: { Authorization: authToken },
                    data: { id: noteId },
                    success: function (response) {
                        if (response.message) {
                            loadNotes(); // Refresh the notes list
                        } else {
                            alert("Failed to delete note: " + response.error);
                        }
                    },
                    error: function () {
                        alert("An error occurred while deleting the note.");
                    }
                });
            });
        });
    </script>
</body>

</html>
