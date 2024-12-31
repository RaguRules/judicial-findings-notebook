<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notes App</title>

    <!-- Link to Bootstrap CSS -->
    <link href="./fe2_files/bootstrap.min.css" rel="stylesheet">
    <!-- Link to Font Awesome CSS for icons -->
    <link href="./fe2_files/font-awesome.css" rel="stylesheet">
    <!-- Link to custom style -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Link to jQuery -->
    <script type="text/javascript" src="./fe2_files/jquery.min.js.download"></script>
</head>

<body class="snippet-body">

    <!-- Modal for Viewing Full Note -->
    <div class="modal fade" id="viewNoteModal" tabindex="-1" role="dialog" aria-labelledby="viewNoteModalLabel" aria-hidden="true">
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


    <!-- Main Content Container -->
    <div class="page-content container note-has-grid">

        <!-- Navigation Pills -->
        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center">
            <!-- New Note Button -->
            <li class="nav-item ml-auto">
                <a href="javascript:void(0)" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                    <i class="icon-note m-1"></i><span class="d-none d-md-block font-14">New +</span>
                </a>
            </li>

            <!-- Import Note Button -->
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-business">
                    <i class="icon-briefcase mr-1"></i><span class="d-none d-md-block">Import</span>
                </a>
            </li>

            <!-- Export Note Button -->
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-social">
                    <i class="icon-share-alt mr-1"></i><span class="d-none d-md-block">Export</span>
                </a>
            </li>

            <!-- Sign off Button -->
            <li class="nav-item">
                <a href="javascript:void(0)" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-important">
                    <i class="icon-tag mr-1"></i><span class="d-none d-md-block">Sign off</span>
                </a>
            </li>




             <!-- Search Box -->
            <li class="nav-item ml-auto">
                <input type="text" id="search-box" class="form-control" placeholder="Search notes..." />
            </li>
            <a href="javascript:void(0)" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes"> 
                <i class="icon-note m-1"></i><span class="d-none d-md-block font-14">Search</span>
            </a> 
            <!-- </li>  -->
           
        </ul>

        <!-- Content Area -->
        <div class="tab-content bg-transparent">
            <div id="note-full-container" class="note-has-grid row">


                <!-- Single Note Item -->
                 <?php

                 // 1. Define the API endpoint URL
                $apiUrl = "http://localhost/api/V1/notes/fetchall.php";

                // 2. Prepare the request data (if any)
                $data = [
                    'key1' => 'value1',
                    'key2' => 'value2'
                ];

                // 3. Create a context with options
                $contextOptions = [
                    'http' => [
                        'method' => 'GET', // Use POST request (if applicable)
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                                // . "Authorization: Bearer your_access_token\r\n", // Include headers
                        // 'content' => http_build_query($data) // Set POST data (if applicable)
                    ]
                ];
                $context = stream_context_create($contextOptions);

                // 4. Fetch the API response
                $response = file_get_contents($apiUrl, false, $context);

                // 5. Check for errors
                if ($response === false) {
                    echo 'Error fetching API response.';
                }

                // 6. Process the response
                $responseData = json_decode($response, true); // Assuming the API returns JSON data

                if ($responseData && is_array($responseData) && isset($responseData['notes'])) { 

                    $notes = $responseData['notes'];
                
                    foreach ($responseData['notes'] as $note) {
                        $shortContent = substr($note['content'], 0, 100);
                        echo <<<EOT
                        <div class="col-md-4 single-note-item all-category note-important">
                            <div class="card card-body">
                                <span class="side-stick"></span>
                                <h5 class="note-title text-truncate w-75 mb-0" data-noteheading="Launch new template">
                                    {$note['title']} <i class="point fa fa-circle ml-1 font-10"></i> 
                                </h5>
                                <p class="note-date font-12 text-muted">{$note['created_at']}</p> 
                                <div class="note-content">
                                    <p class="note-inner-content text-muted" data-notecontent="Blandit tempus porttitor aasfs. Integer posuere erat a ante venenatis.">
                                        {$shortContent} 
                                    </p>
                                </div>
                            <!--<div class="d-flex align-items-center justify-content-between"> -->
                            <div class="d-flex align-items-start"> 
                                <button type="button" class="btn btn-sm btn-success view-note-btn rounded-pill me-2" data-bs-toggle="modal" data-bs-target="#viewNoteModal" data-note-id="{$note['id']}">View</button>
                                <button class="btn btn-sm btn-primary edit-note-btn rounded-pill" data-note-id="{$note['id']}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-note-btn rounded-pill" data-note-id="{$note['id']}">Delete</button>
                            </div>
                            </div>
                        </div>
                        EOT;
                    }
                
                }else {
                    // Handle the case where the API response is invalid or doesn't contain notes
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading">No journals found !</h4> 
                        <p>Let\'s start with creating new Journal.</p>
                        <p>If you have existing journals, please log in again to see them.</p>
                    </div>';
                }

                
                ?>

                <!-- Empty Note Item for Layout -->
                <div class="col-md-4 single-note-item all-category note-important"></div>
            </div>
        </div>

        <!-- Modal for Editing Notes -->
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


        <!-- Modal for Adding New Notes -->
        <div class="modal fade" id="addnotesmodal" tabindex="-1" role="dialog" aria-labelledby="addnotesmodalTitle" style="display: none;" aria-hidden="true">
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
                                                <!-- <label>Note Title2</label> -->
                                                <input type="text" id="note-has-title" class="form-control" minlength="15" placeholder="Title">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="note-description">
                                                <!-- <label>Note Description2</label> -->
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
                        <button class="btn btn-danger" data-dismiss="modal">Discard</button>
                        <button id="btn-n-add" class="btn btn-info" disabled="disabled">Add</button>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- Scripts -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function decodeHtmlEntities(str) {
        var doc = new DOMParser().parseFromString(str, 'text/html');
        return doc.documentElement.textContent;
            // Example usage
            // let encodedString = "5 &lt; 6";
            // let decodedString = decodeHtmlEntities(encodedString);
            // console.log(decodedString);  // Output: 5 < 6
    }
    </script>


    <script>
        $(function () {
            // Function to remove a note
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
            $('#btn-n-add').click(function () {
                var noteTitle = $('#note-has-title').val();
                var noteDescription = $('#note-has-description').val();
                var noteItem = `
                    <div class="col-md-4 single-note-item all-category note-important">
                        <div class="card card-body">
                            <h5 class="note-title text-truncate w-75 mb-0">${noteTitle}</h5>
                            <p class="note-date font-12 text-muted">${new Date().toLocaleDateString()}</p>
                            <p class="note-inner-content">${noteDescription}</p>
                            <div class="d-flex align-items-center">
                                <span class="mr-1"><i class="fa fa-star favourite-note"></i></span>
                                <span class="mr-1"><i class="fa fa-trash remove-note"></i></span>
                                <div class="ml-auto">
                                    <div class="category-selector btn-group">
                                        <a class="nav-link dropdown-toggle category-dropdown label-group p-0" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="true">
                                            <div class="category">
                                                <div class="category-business"></div>
                                                <div class="category-social"></div>
                                                <div class="category-important"></div>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right category-menu">
                                            <a class="note-business badge-group-item badge-business dropdown-item position-relative category-business text-success" href="javascript:void(0);">
                                                <i class="mdi mdi-checkbox-blank-circle-outline mr-1"></i>Business
                                            </a>
                                            <a class="note-social badge-group-item badge-social dropdown-item position-relative category-social text-info" href="javascript:void(0);">
                                                <i class="mdi mdi-checkbox-blank-circle-outline mr-1"></i> Social
                                            </a>
                                            <a class="note-important badge-group-item badge-important dropdown-item position-relative category-important text-danger" href="javascript:void(0);">
                                                <i class="mdi mdi-checkbox-blank-circle-outline mr-1"></i> Important
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('#note-full-container').append(noteItem);
                $('#addnotesmodal').modal('hide');
                removeNote();
                favouriteNote();
                addLabelGroups();
            });

        });
    </script>





    <script>
    // Function to open the modal for editing an existing note
    $(document).ready(function () {
        $(document).on('click', '.edit-note-btn', function () {
        var noteId = $(this).data('note-id');

        // AJAX request to fetch the note data
        $.ajax({
            url: 'http://localhost/api/V1/notes/read.php?note_id=' + noteId,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' +
                    'b9214b682dd2de9e695083ffc51b71302cac6a1aa52d740c84ff1122a305e534'
            },
            success: function (response) {
                // Access the note data correctly from response.note
                if (response.status === "success" && response.note) {
                    var note = response.note; // Get the inner 'note' object

                    // Decode the note content to handle HTML entities like &lt;, &gt;, etc.
                    var decodedTitle = decodeHtmlEntities(note.title);
                    var decodedContent = decodeHtmlEntities(note.content);

                    // Update the modal with the note details
                    $('#addEditNotesModalLabel').text("Edit Note");
                    $('#note-id').val(note.id); // Access id from the 'note' object
                    $('#note-title-input').val(decodedTitle);
                    $('#note-description-input').val(decodedContent); // Insert decoded content here
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
    });

    // "Update" button functionality
    $('#update-note').click(function () {
        var noteId = $('#note-id').val();
        var updatedTitle = $('#note-title-input').val();
        var updatedContent = $('#note-description-input').val();

        var noteIdInt = parseInt(noteId, 10);
        console.log("noteId:", noteId);
        console.log("noteIdInt:", noteIdInt);

        // AJAX request to update the note
        $.ajax({
            url: 'http://localhost/api/V1/notes/update.php',
            type: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' +
                    'b9214b682dd2de9e695083ffc51b71302cac6a1aa52d740c84ff1122a305e534',
            },
            data: JSON.stringify({
                note_id: noteIdInt,
                title: updatedTitle,
                content: updatedContent
            }),
            success: function (response) {
                console.log("Note updated successfully:", response);
                $('#addEditNotesModal').modal('hide');
                location.reload();
            },
            error: function (xhr, status, error) {
                console.error("Error updating note:", error);
            }
        });
    });
});

    </script>

</body>

</html>
