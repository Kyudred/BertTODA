<?php
include 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts</title>
    <link rel="stylesheet" href="css/manage-posts.css">
</head>
<body>
    <div class="container">
        <h1>Manage Posts</h1>
        
        <div class="post-type-selector">
            <button onclick="loadPosts('news')" class="type-btn active" id="newsBtn">News Posts</button>
            <button onclick="loadPosts('projects')" class="type-btn" id="projectsBtn">Project Posts</button>
        </div>

        <div id="postsContainer">
            <!-- Posts will be loaded here dynamically -->
        </div>

        <div id="edit-post-modal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Edit Post</h2>
                <form id="edit-post-form">
                    <input type="hidden" id="edit-post-id">
                    <input type="hidden" id="edit-post-type">
                    <input type="text" id="edit-title" placeholder="Title">
                    <textarea id="edit-content" placeholder="Content"></textarea>
                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
    <script src="js/manage-posts.js"></script>
</body>
</html>