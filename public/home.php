<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Notes App</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="css/font-awesome.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet">
        <script type="text/javascript" src="js/jquery.min.js.download"></script>
    </head>
    <body class="snippet-body">
        <div class="modal fade" id="viewNoteModal" tabindex="-1" role="dialog" aria-labelledby="viewNoteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 80%; height: auto;" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="note-title">View Full Note</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="note-content"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="print-view-note">Print</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-content container note-has-grid">
            <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center">
                <li class="nav-item ml-auto">
                    <a href="javascript:void(0)" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                    <i class="icon-note m-1"></i><span class="d-none d-md-block font-14">New +</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-business">
                    <i class="icon-briefcase mr-1"></i><span class="d-none d-md-block">Import</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="export-button">
                    <i class="icon-share-alt mr-1"></i><span class="d-none d-md-block">Export</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="sign-off-button">
                    <i class="icon-tag mr-1"></i><span class="d-none d-md-block">Sign off</span>
                    </a>
                </li>
                <li class="nav-item ml-auto">
                    <input type="text" id="search-box" class="form-control" placeholder="Search notes..." />
                </li>
                <a href="javascript:void(0)" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                <i class="icon-note m-1"></i><span class="d-none d-md-block font-14">Search</span>
                </a>
            </ul>
            <div class="tab-content bg-transparent">
                <div id="note-full-container" class="note-has-grid row">
                    <div class="col-md-4 single-note-item all-category note-important"></div>
                </div>
            </div>
            <div class="modal fade" id="addEditNotesModal" tabindex="-1" role="dialog" aria-labelledby="addEditNotesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                    <div class="modal-content border-0">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title text-white" id="addEditNotesModalLabel">Edit Note</h5>
                            <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <button id="update-note" class="btn btn-success">Save</button>
                            <button class="btn btn-danger" data-bs-dismiss="modal">Discard</button>
                            <button type="button" class="btn btn-primary" id="print-edit-note">Print</button> 
                        </div>
                        <div class="notes-box">
                            <div class="notes-content">
                                <form action="javascript:void(0);" id="noteForm">
                                    <input type="hidden" id="note-id">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="note-title">
                                                <input type="text" id="note-title-input" class="form-control" minlength="25" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="note-description">
                                                <textarea id="note-description-input" class="form-control" minlength="60" placeholder="Description" rows="25"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="addnotesmodal" tabindex="-1" role="dialog" aria-labelledby="addnotesmodalTitle" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content border-0">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title text-white">Create a Journal</h5>
                            <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="notes-box">
                                <div class="notes-content">
                                    <form action="javascript:void(0);" id="addnotesmodalTitle">
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="note-title">
                                                    <input type="text" id="note-has-title" class="form-control" minlength="15" placeholder="Title">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="note-description">
                                                    <textarea id="note-has-description" class="form-control" minlength="60" placeholder="Description" rows="25"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-n-save" class="float-left btn btn-success" style="display: none;">Save</button>
                            <button class="btn btn-danger" data-bs-dismiss="modal">Discard</button>
                            <button id="btn-n-add" class="btn btn-info" disabled="disabled">Add</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
        <script>
            // Function to decode HTML entities
            function decodeHtmlEntities(str) {
                var doc = new DOMParser().parseFromString(str, 'text/html');
                return doc.documentElement.textContent;
            }
        </script>
        <script>
            $(function () {
            
                // Function to check token validity
                function checkTokenValidity() {
                    const accessToken = localStorage.getItem('access_token');
                    if (!accessToken) {
                        // Redirect to login page if no token is found
                        window.location.href = 'login.php';
                        return;
                    }
            
                    // Send the token to your server to check its validity
                    $.ajax({
                        url: 'http://localhost/api/V1/auth/validatetoken.php',
                        type: 'POST',
                        headers: {
                            'Content-Type': 'Application/json'
                        },
                        data: JSON.stringify({ "token": accessToken }),
                        success: function (response) {
                            if (response.status === 'success') {
                                // Token is valid, load notes
                                loadNotes();
                            } else {
                                // Token is invalid or expired, try to refresh it
                                refreshToken();
                            }
                        },
                        error: function(xhr, status, error) {
                        if (xhr.status === 401) {
                            refreshToken();
                        } else {
                            // Handle other errors (network issues, server errors)
                            console.error("Error validating token:", error);
                        }
                    }
                }
                    );
                }
            
                // Function to refresh the token
                function refreshToken() {
                    var refreshToken = getCookie('refresh_token');
                    if (!refreshToken) {
                        // No refresh token, redirect to login
                        window.location.href = 'login.php';
                        return;
                    }
            
                    $.ajax({
                        url: 'http://localhost/api/V1/auth/refreshaccesstoken.php',
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + refreshToken
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                // Update the access token in local storage
                                localStorage.setItem('access_token', response.access_token);
                                // Reload notes with the new access token
                                loadNotes();
                            } else {
                                // Refresh token is invalid or expired, redirect to login
                                window.location.href = 'login.php';
                            }
                        },
                        error: function () {
                            // Error in refreshing token, redirect to login
                            window.location.href = 'login.php';
                        }
                    });
                }
            
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
            
                // Load notes function
                function loadNotes() {
                    const accessToken = localStorage.getItem('access_token');
                    // Fetch notes using the access token
                    $.ajax({
                        url: 'http://localhost/api/V1/notes/fetchall.php',
                        type: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + accessToken
                        },
                        success: function (response) {
                            console.log(response);
                            displayNotes(response);
                        },
                        error: function (error) {
                            console.error("Error fetching notes:", error);
                        }
                    });
                }
            
                // Call checkTokenValidity on page load
                checkTokenValidity();
            
                // Function to display notes
                function displayNotes(responseData) {
            if (responseData && responseData.status === 'success' && Array.isArray(responseData.notes)) {
            $('#note-full-container').empty(); // Clear existing notes
            
            responseData.notes.forEach(note => {
                let shortContent = note.content.length > 100 ? note.content.substring(0, 100) + "..." : note.content;
                let noteItem = `
                    <div class="col-md-4 single-note-item all-category note-important">
                        <div class="card card-body">
                            <span class="side-stick"></span>
                            <h5 class="note-title text-truncate w-75 mb-0" data-noteheading="${note.title}">
                                ${note.title} 
                            </h5>
                            <p class="note-date font-12 text-muted">${note.created_at}</p>
                            <div class="note-content">
                                <p class="note-inner-content text-muted" data-notecontent="${note.content}">
                                    ${shortContent}
                                </p>
                            </div>
                            <div class="d-flex align-items-start">
                                <button type="button" class="btn btn-sm btn-success view-note-btn rounded-pill me-2" data-bs-toggle="modal" data-bs-target="#viewNoteModal" data-note-id="${note.id}">View</button>
                                <button class="btn btn-sm btn-primary edit-note-btn rounded-pill" data-note-id="${note.id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-note-btn rounded-pill" data-note-id="${note.id}">Delete</button>
                            </div>
                        </div>
                    </div>
                `;
                $('#note-full-container').append(noteItem);
            });
            
            // Reattach event listeners to the dynamically created buttons
            attachEventListenersToNotes();
            } else {
            $('#note-full-container').html(`
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading">No journals found!</h4>
                    <p>Let's start with creating a new Journal.</p>
                    <p>If you have existing journals, please log in again to see them.</p>
                </div>
            `);
            }
            }
            
                // Function to attach event listeners to note buttons
                function attachEventListenersToNotes() {
                    // View Note
                    $('.view-note-btn').off('click').on('click', function () {
                        var noteId = $(this).data('note-id');
                        viewNote(noteId);
                    });
            
                    // Edit Note
                    $('.edit-note-btn').off('click').on('click', function () {
                        var noteId = $(this).data('note-id');
                        editNote(noteId);
                    });
            
                    // Delete Note
                    $('.delete-note-btn').off('click').on('click', function () {
                        var noteId = $(this).data('note-id');
                        deleteNote(noteId);
                    });
                }
            
                // Function to handle viewing a note
                function viewNote(noteId) {
                    const accessToken = localStorage.getItem('access_token');
                    $.ajax({
                        url: `http://localhost/api/V1/notes/read.php?note_id=${noteId}`,
                        type: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + accessToken
                        },
                        success: function (response) {
                            if (response.status === "success" && response.note) {
                                var note = response.note;
                                var decodedTitle = decodeHtmlEntities(note.title);
                                var decodedContent = decodeHtmlEntities(note.content);
            
                                $('#viewNoteModal #note-title').text(decodedTitle);
                                $('#viewNoteModal #note-content').html(decodedContent);
                                $('#viewNoteModal').modal('show');
                            } else {
                                console.error("Note not found in the response:", response);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error fetching note:", error);
                        }
                    });
                }
            
                // Function to handle editing a note
                function editNote(noteId) {
                    const accessToken = localStorage.getItem('access_token');
                    $.ajax({
                        url: `http://localhost/api/V1/notes/read.php?note_id=${noteId}`,
                        type: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + accessToken
                        },
                        success: function (response) {
                            if (response.status === "success" && response.note) {
                                var note = response.note;
                                var decodedTitle = decodeHtmlEntities(note.title);
                                var decodedContent = decodeHtmlEntities(note.content);
            
                                $('#addEditNotesModalLabel').text("Edit Note");
                                $('#note-id').val(note.id);
                                $('#note-title-input').val(decodedTitle);
                                $('#note-description-input').val(decodedContent);
                                $('#update-note').show();
                                $('#addEditNotesModal').modal('show');
                            } else {
                                console.error("Note not found in the response:", response);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error fetching note:", error);
                        }
                    });
                }
            
                // Function to handle deleting a note
                function deleteNote(noteId) {
                    const accessToken = localStorage.getItem('access_token');
                    if (confirm("Are you sure you want to delete this note?")) {
                        $.ajax({
                            url: 'http://localhost/api/V1/notes/delete.php',
                            type: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + accessToken
                            },
                            data: JSON.stringify({
                                note_id: noteId
                            }),
                            success: function (response) {
                                if (response.status === 'success') {
                                    console.log("Note deleted successfully");
                                    // Reload the notes to reflect the deletion
                                    loadNotes();
                                } else {
                                    console.error("Error deleting note:", response.message);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("Error deleting note:", error);
                            }
                        });
                    }
                }
            
                // "Update" button functionality
                $('#update-note').off('click').on('click', function () {
                    const accessToken = localStorage.getItem('access_token');
                    var noteId = $('#note-id').val();
                    var updatedTitle = $('#note-title-input').val();
                    var updatedContent = $('#note-description-input').val();
            
                    var noteIdInt = parseInt(noteId, 10);
            
                    $.ajax({
                        url: 'http://localhost/api/V1/notes/update.php',
                        type: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + accessToken
                        },
                        data: JSON.stringify({
                            note_id: noteIdInt,
                            title: updatedTitle,
                            content: updatedContent
                        }),
                        success: function (response) {
                            console.log("Note updated successfully:", response);
                            $('#addEditNotesModal').modal('hide');
                            loadNotes(); // Reload notes after update
                        },
                        error: function (xhr, status, error) {
                            console.error("Error updating note:", error);
                        }
                    });
                });
            
                // Function to remove a note (currently not in use but can be used if you implement a remove button)
                function removeNote() {
                    $(".remove-note").off('click').on('click', function (event) {
                        event.stopPropagation();
                        $(this).parents('.single-note-item').remove();
                    });
                }
            
                // Initialize add new note modal
                $('#add-notes').click(function () {
                    $('#addnotesmodal').modal('show');
                });
            
                // Enable Add button when title and description are filled
                $('#note-has-title, #note-has-description').on('input', function () {
                    if ($('#note-has-title').val() && $('#note-has-description').val()) {
                        $('#btn-n-add').prop('disabled', false);
                    } else {
                        $('#btn-n-add').prop('disabled', true);
                    }
                });
            
                // Add new note functionality
                $('#btn-n-add').off('click').on('click', function () {
                    const accessToken = localStorage.getItem('access_token');
                    var noteTitle = $('#note-has-title').val();
                    var noteDescription = $('#note-has-description').val();
            
                    $.ajax({
                        url: 'http://localhost/api/V1/notes/create.php',
                        type: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + accessToken
                        },
                        data: JSON.stringify({
                            title: noteTitle,
                            content: noteDescription
                        }),
                        success: function (response) {
                            if (response.status === 'success') {
                                console.log("Note added successfully:", response);
                                $('#addnotesmodal').modal('hide');
                                // Reset the form fields
                                $('#note-has-title').val('');
                                $('#note-has-description').val('');
                                loadNotes(); // Reload notes after adding a new one
                            } else {
                                console.error("Error adding note:", response.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error adding note:", error);
                        }
                    });
                });
            });
            
            /** Handles the sign-out process.
            *
            * Sends a request to the logout API endpoint, clears local storage data,
            * and redirects the user to the login page.
            */
            function signOut() {
            const accessToken = localStorage.getItem('access_token');
            $.ajax({
            url: 'http://localhost/api/V1/auth/logout.php',
            type: 'POST', 
            headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + accessToken
            },
            success: function(response) {
            // Handle successful logout
            console.log("Logout successful:", response);
            
            // Clear access token from local storage
            localStorage.removeItem('access_token');

            // Clear refresh token cookie (call a new function)
            clearRefreshTokenCookie();
            
            // Redirect to the login page
            window.location.href = 'login.php';
            },
            error: function(xhr, status, error) {
            // Handle logout error
            console.error("Error during logout:", error);
            // Optionally, display an error message to the user
            alert("Logout failed. Please check the console for details.");
            }
            });
            }

            // New function to clear the refresh_token cookie
            function clearRefreshTokenCookie() {
                document.cookie = "refresh_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }
            
            // Attach the signOut function to the "Sign off" button's click event:
            $('#sign-off-button').on('click', function(event) {
            event.preventDefault(); // Prevent default link behavior
            signOut();
            });
            
            
            function exportDatabase() {
            // Create a temporary link element
            const link = document.createElement('a');
            link.href = 'http://localhost/api/V1/misc/export.php'; // URL to your export API endpoint
            link.download = 'data.db'; // Set the desired filename for the download
            link.target = '_self'; // Optional: Open in the same tab to initiate the download
            
            // Append the link to the body, click it, and then remove it
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            }
            
            // Attach the exportDatabase function to the "Export" button's click event:
            $('#export-button').on('click', function (event) {
            event.preventDefault(); // Prevent default link behavior
            exportDatabase();
            });
            
            function printNote(title, content) {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            
            // Write the HTML content to the new window
            printWindow.document.write(`
            <html>
            <head>
            <title>This system is developed by Courts' Interpreter Srirajeswaran Raguraj. For any bugs/ errors, please contact: +(94)777958841.</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                }
                h1 {
                    text-align: center;
                }
                .content {
                    white-space: pre-wrap; /* Preserve line breaks */
                }
            </style>
            </head>
            <body>
            <h1>${title}</h1>
            <div class="content">${content}</div>
            </body>
            </html>
            `);
            
            // Close the document stream to indicate end of content
            printWindow.document.close();
            
            // Wait for the content to load and then print
            printWindow.onload = function() {
            printWindow.print();
            // Optional: Close the window after printing
            printWindow.close(); 
            };
            }$(function() {
            // ... other JavaScript functions ...
            
            // View Note Modal Print Button
            $('#print-view-note').on('click', function() {
            const title = $('#viewNoteModal #note-title').text(); // Get title from modal
            const content = $('#viewNoteModal #note-content').html(); // Get content from modal (using .html() to preserve formatting)
            printNote(title, content);
            });
            
            // Edit Note Modal Print Button
            $('#print-edit-note').on('click', function() {
            const title = $('#note-title-input').val(); // Get title from input field
            const content = $('#note-description-input').val(); // Get content from textarea
            printNote(title, content);
            });
            
            // ... rest of your JavaScript code ...
            });
            
            
            // Function to filter and display notes based on search query
            function searchNotes(query) {
            query = query.toLowerCase(); // Convert query to lowercase for case-insensitive search
            
            // Iterate through each note item
            $('#note-full-container .single-note-item').each(function() {
            const title = $(this).find('.note-title').text().toLowerCase();
            const content = $(this).find('.note-inner-content').text().toLowerCase();
            
            // Check if the title or content contains the search query
            if (title.includes(query) || content.includes(query)) {
                $(this).show(); // Show the note if it matches
            } else {
                $(this).hide(); // Hide the note if it doesn't match
            }
            });
            }
            
            // Attach an event listener to the search box for real-time filtering
            $('#search-box').on('input', function() {
            const query = $(this).val();
            searchNotes(query);
            });
            
        </script>
    </body>
</html>