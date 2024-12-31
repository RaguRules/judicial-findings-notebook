<?php
// PHP based homepage

$isLoggedIn = false;
$notes = [];
$errorMessage = ""; // Variable to store error messages

// Check if refresh token is present in the cookie
if (isset($_COOKIE['refresh_token'])) {
    $refreshToken = $_COOKIE['refresh_token'];
    $newAccessTokenResponse = refreshAccessToken($refreshToken); // Implement this in auth_functions.php

    if ($newAccessTokenResponse['status'] === 'success') {
        // Use the new access token for subsequent requests
        $accessToken = $newAccessTokenResponse['access_token'];
        $user = validateAccessToken($accessToken);
        if ($user) {
            $isLoggedIn = true;
            $notes = fetchAllNotes($user['id']); // Implement fetchAllNotes in auth_functions.php
        } else {
            $errorMessage = "Invalid access token after refresh attempt.";
        }
    } else {
        $errorMessage = $newAccessTokenResponse['message']; // Capture error message from refresh attempt
    }
}

// If no valid access token after refresh attempt, check for Authorization header
if (!$isLoggedIn && isset(getallheaders()['Authorization'])) {
    $accessToken = str_replace('Bearer ', '', getallheaders()['Authorization']);
    $user = validateAccessToken($accessToken);
    if ($user) {
        $isLoggedIn = true;
        $notes = fetchAllNotes($user['id']);
    } else {
        $errorMessage = "Invalid access token.";
    }
}

// Redirect to login page if not logged in and no valid token is found
if (!$isLoggedIn) {
    // Unset cookies
    setcookie('refresh_token', '', time() - 3600, '/'); 

    // Redirect to the login page 
    header("Location: login.php"); 
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notes App</title>

    <link href="./fe2_files/bootstrap.min.css" rel="stylesheet">
    <link href="./fe2_files/font-awesome.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script type="text/javascript" src="./fe2_files/jquery.min.js.download"></script>
</head>

<body class="snippet-body">

    <div class="modal fade" id="viewNoteModal" tabindex="-1" role="dialog" aria-labelledby="viewNoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 80%; height: auto;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewNoteModalLabel">View Full Note</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="note-title"></div>
                    <div id="note-content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="page-content container note-has-grid">

        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center">
            <li class="nav-item ml-auto">
                <a href="javascript:void(0)"
                    class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                    <i class="icon-note m-1"></i><span class="d-none d-md-block font-14">New +</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="javascript:void(0)"
                    class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2"
                    id="note-business">
                    <i class="icon-briefcase mr-1"></i><span class="d-none d-md-block">Import</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="javascript:void(0)"
                    class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2"
                    id="note-social">
                    <i class="icon-share-alt mr-1"></i><span class="d-none d-md-block">Export</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="javascript:void(0)"
                    class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2"
                    id="note-important">
                    <i class="icon-tag mr-1"></i><span class="d-none d-md-block">Sign off</span>
                </a>
            </li>




            <li class="nav-item ml-auto">
                <input type="text" id="search-box" class="form-control" placeholder="Search notes..." />
            </li>
            <a href="javascript:void(0)" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3"
                id="add-notes">
                <i class="icon-note m-1"></i><span class="d-none d-md-block font-14">Search</span>
            </a>
        </ul>

        <div class="tab-content bg-transparent">
            <div id="note-full-container" class="note-has-grid row">
                <?php if ($isLoggedIn): ?>
                    <?php foreach ($notes as $note): ?>
                        <?php
                        $shortContent = strlen($note['content']) > 100 ? substr($note['content'], 0, 100) . "..." : $note['content'];
                        ?>
                        <div class="col-md-4 single-note-item all-category note-important">
                            <div class="card card-body">
                                <span class="side-stick"></span>
                                <h5 class="note-title text-truncate w-75 mb-0" data-noteheading="<?= htmlspecialchars($note['title']) ?>">
                                    <?= htmlspecialchars($note['title']) ?> <i class="point fa fa-circle ml-1 font-10"></i>
                                </h5>
                                <p class="note-date font-12 text-muted"><?= htmlspecialchars($note['created_at']) ?></p>
                                <div class="note-content">
                                    <p class="note-inner-content text-muted" data-notecontent="<?= htmlspecialchars($note['content']) ?>">
                                        <?= htmlspecialchars($shortContent) ?>
                                    </p>
                                </div>
                                <div class="d-flex align-items-start">
                                    <button type="button" class="btn btn-sm btn-success view-note-btn rounded-pill me-2" data-bs-toggle="modal" data-bs-target="#viewNoteModal" data-note-id="<?= $note['id'] ?>">View</button>
                                    <button class="btn btn-sm btn-primary edit-note-btn rounded-pill" data-note-id="<?= $note['id'] ?>">Edit</button>
                                    <button class="btn btn-sm btn-danger delete-note-btn rounded-pill" data-note-id="<?= $note['id'] ?>">Delete</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading">No journals found!</h4>
                        <p>Let's start with creating a new Journal.</p>
                        <p>If you have existing journals, please log in again to see them.</p>
                    </div>
                <?php endif; ?>

                <div class="col-md-4 single-note-item all-category note-important"></div>
            </div>
        </div>

        <div class="modal fade" id="addEditNotesModal" tabindex="-1" role="dialog"
            aria-labelledby="addEditNotesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                <div class="modal-content border-0">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title text-white" id="addEditNotesModalLabel"></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="notes-box">
                            <div class="notes-content">
                                <form action="javascript:void(0);" id="noteForm">
                                    <input type="hidden" id="note-id">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="note-title">
                                                <label>Note Title</label>
                                                <input type="text" id="note-title-input" class="form-control"
                                                    minlength="25" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="note-description">
                                                <label>Note Description</label>
                                                <textarea id="note-description-input" class="form-control" minlength="60"
                                                    placeholder="Description" rows="25"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="update-note" class="btn btn-success">Save</button>
                        <button class="btn btn-danger" data-dismiss="modal">Discard</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="addnotesmodal" tabindex="-1" role="dialog" aria-labelledby="addnotesmodalTitle"
            style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content border-0">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title text-white">Create a Journal</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
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
                                                <input type="text" id="note-has-title" class="form-control" minlength="15"
                                                    placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="note-description">
                                                <textarea id="note-has-description" class="form-control" minlength="60"
                                                    placeholder="Description" rows="25"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="btn-n-save" class="float-left btn btn-success" style="display: none;">Save</button>
                        <button class="btn btn-danger" data-dismiss="modal">Discard</button>
                        <button id="btn-n-add" class="btn btn-info" disabled="disabled">Add</button>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to handle viewing a note
        function viewNote(noteId) {
            $.ajax({
                url: `api/V1/notes/read.php`,
                type: 'GET',
                data: { note_id: noteId },
                success: function (response) {
                    if (response.status === "success" && response.note) {
                        var note = response.note;
                        // No need to decode, PHP will handle HTML escaping
                        $('#viewNoteModal #note-title').text(note.title);
                        $('#viewNoteModal #note-content').html(note.content);
                        $('#viewNoteModal').modal('show');
                    } else {
                        console.error("Note not found or unauthorized:", response);
                        // Optionally redirect to login if unauthorized
                        // if (response.message === 'Unauthorized') { window.location.href = 'login.html'; }
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching note:", error);
                }
            });
        }

        // Function to handle editing a note
        function editNote(noteId) {
            $.ajax({
                url: `api/V1/notes/read.php`,
                type: 'GET',
                data: { note_id: noteId },
                success: function (response) {
                    if (response.status === "success" && response.note) {
                        var note = response.note;
                        // No need to decode, PHP will handle HTML escaping
                        $('#addEditNotesModalLabel').text("Edit Note");
                        $('#note-id').val(note.id);
                        $('#note-title-input').val(note.title);
                        $('#note-description-input').val(note.content);
                        $('#update-note').show();
                        $('#addEditNotesModal').modal('show');
                    } else {
                        console.error("Note not found or unauthorized:", response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching note:", error);
                }
            });
        }

        // Function to handle deleting a note
        function deleteNote(noteId) {
            if (confirm("Are you sure you want to delete this note?")) {
                $.ajax({
                    url: 'api/V1/notes/delete.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ note_id: noteId }),
                    success: function (response) {
                        if (response.status === 'success') {
                            console.log("Note deleted successfully");
                            // You might want to refresh the notes list here
                            // Since you're using PHP to load notes initially, you might just reload the page
                            location.reload(); 
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
            var noteId = $('#note-id').val();
            var updatedTitle = $('#note-title-input').val();
            var updatedContent = $('#note-description-input').val();

            var noteIdInt = parseInt(noteId, 10);

            $.ajax({
                url: 'api/V1/notes/update.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    note_id: noteIdInt,
                    title: updatedTitle,
                    content: updatedContent
                }),
                success: function (response) {
                    console.log("Note updated successfully:", response);
                    $('#addEditNotesModal').modal('hide');
                    location.reload(); // Reload notes after update
                },
                error: function (xhr, status, error) {
                    console.error("Error updating note:", error);
                }
            });
        });

        // Initialize add new note modal
        $('#add-notes').click(function () {
            $('#addnotesmodal').modal('show');
        });

        // Enable Add button when title and description are filled
        <span class="math-inline">\('\#note\-has\-title, \#note\-has\-description'\)\.on\('input', function \(\) \{
if \(</span>('#note-has-title').val() && $('#note-has-description').val()) {
                $('#btn-n-add').prop('disabled', false);
            } else {
                $('#btn-n-add').prop('disabled', true);
            }
        });

        // Add new note functionality
        $('#btn-n-add').off('click').on('click', function () {
            var noteTitle = $('#note-has-title').val();
            var noteDescription = $('#note-has-description').val();

            $.ajax({
                url: 'api/V1/notes/create.php',
                type: 'POST',
                contentType: 'application/json',
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
                        location.reload(); // Reload notes after adding a new one
                    } else {
                        console.error("Error adding note:", response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error adding note:", error);
                }
            });
        });

        $(function() {
            // Attach event listeners to dynamically created buttons
            $('#note-full-container').on('click', '.view-note-btn', function() {
                var noteId = $(this).data('note-id');
                viewNote(noteId);
            });

            $('#note-full-container').on('click', '.edit-note-btn', function() {
                var noteId = $(this).data('note-id');
                editNote(noteId);
            });

            $('#note-full-container').on('click', '.delete-note-btn', function() {
                var noteId = $(this).data('note-id');
                deleteNote(noteId);
            });
        });
    </script>

</body>

</html>