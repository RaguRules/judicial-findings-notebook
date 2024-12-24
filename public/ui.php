<!DOCTYPE html>
<html>
<head>
    <title>Judicial Notes App</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .notes-list {
            margin-top: 20px;
        }

        .note {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Judicial Notes App</h1>

        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="create.php"> 
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="content" placeholder="Enter your notes here..." rows="5" required></textarea>
            <button type="submit">Create Note</button>
        </form>

        <div class="notes-list">
            <h2>Your Notes</h2>
            <?php
            require_once("db.class.php"); 
            $db = new Database("../../../model/data.db");
            $conn = $db->getConn();

            // Authentication and authorization (replace with your actual implementation)
            // ... (get $userId from authentication/authorization logic) ...

            try {
                $stmt = $conn->prepare("SELECT id, title, content, created_at FROM notes WHERE user_id = :user_id");
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($notes as $note) {
                    echo '<div class="note">';
                    echo '<h3>' . $note['title'] . '</h3>';
                    echo '<p>' . $note['content'] . '</p>';
                    echo '<p>Created at: ' . $note['created_at'] . '</p>';

                    // Edit and delete buttons/links
                    echo '<a href="edit.php?id=' . $note['id'] . '">Edit</a> | '; 
                    echo '<a href="delete.php?id=' . $note['id'] . '">Delete</a>';

                    echo '</div>';
                }
            } catch (PDOException $e) {
                echo "Error fetching notes: " . $e->getMessage();
            }
            ?>
        </div>
    </div>

    <script>
        // Function to fetch and display notes
        function fetchNotes() {
            // ... (your AJAX code to fetch notes using GET request to /api/notes)
        }

        // Function to create a new note
        document.getElementById('note-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // ... (your AJAX code to create a note using POST request to /api/notes)
        });

        // Function to update a note (you'll need to implement this)
        function updateNote(noteId) {
            // ... (your AJAX code to update a note using PUT request to /api/notes/{note_id})
        }

        // Function to delete a note (you'll need to implement this)
        function deleteNote(noteId) {
            // ... (your AJAX code to delete a note using DELETE request to /api/notes/{note_id})
        }

        // Initial fetch of notes
        fetchNotes(); 
    </script>
</body>
</html>