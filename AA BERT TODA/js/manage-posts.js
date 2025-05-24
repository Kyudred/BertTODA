let currentPostType = 'all';

function loadPosts(type = 'all', searchQuery = '') {
    const postsTable = document.getElementById('posts-list');
    postsTable.innerHTML = '<tr class="loading-row"><td colspan="5">Loading posts...</td></tr>';

    fetch(`get_posts.php?type=${type}&search=${searchQuery}`)
        .then(response => response.json())
        .then(posts => {
            postsTable.innerHTML = '';
            if (!Array.isArray(posts) || posts.length === 0) {
                postsTable.innerHTML = '<tr><td colspan="5">No posts found</td></tr>';
                return;
            }
            posts.forEach(post => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${post.title || 'No Title'}</td>
                    <td>${post.type || 'N/A'}</td>
                    <td>${new Date(post.date_posted).toLocaleDateString()}</td>
                    <td>${post.category || 'N/A'}</td>
                    <td>
                        <button onclick="editPost(${post.id}, '${post.type}')" class="edit-btn">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="deletePost(${post.id}, '${post.type}')" class="delete-btn">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                `;
                postsTable.appendChild(row);
            });
        })
        .catch(error => {
            postsTable.innerHTML = `<tr><td colspan="5">Error loading posts: ${error.message}</td></tr>`;
        });
}

window.editPost = function(postId, postType) {
    fetch(`get_post_details.php?id=${postId}&type=${postType}`)
        .then(response => response.json())
        .then(post => {
            document.getElementById('edit-post-id').value = post.id;
            document.getElementById('edit-post-type').value = postType;
            document.getElementById('edit-current-image').value = post.image || '';
            document.getElementById('edit-image-preview').src = post.image || '#';

            // Reset file input
            document.getElementById('edit-image').value = '';

            // Remove required from all fields first
            document.getElementById('edit-title').removeAttribute('required');
            document.getElementById('edit-summary').removeAttribute('required');
            document.getElementById('edit-content').removeAttribute('required');
            document.getElementById('edit-category').removeAttribute('required');
            document.getElementById('edit-title-project').removeAttribute('required');
            document.getElementById('edit-description').removeAttribute('required');
            document.getElementById('edit-category-project').removeAttribute('required');

            if (postType === 'news') {
                document.getElementById('edit-news-fields').style.display = '';
                document.getElementById('edit-project-fields').style.display = 'none';
                document.getElementById('edit-title').value = post.title || '';
                document.getElementById('edit-summary').value = post.summary || '';
                document.getElementById('edit-content').value = post.content || '';
                document.getElementById('edit-category').value = post.category || '';
                document.getElementById('edit-facebook').value = post.facebook || '';
                // Add required only to visible fields
                document.getElementById('edit-title').setAttribute('required', 'required');
                document.getElementById('edit-content').setAttribute('required', 'required');
                document.getElementById('edit-category').setAttribute('required', 'required');
            } else {
                document.getElementById('edit-news-fields').style.display = 'none';
                document.getElementById('edit-project-fields').style.display = '';
                document.getElementById('edit-title-project').value = post.title || '';
                document.getElementById('edit-description').value = post.description || post.content || '';
                document.getElementById('edit-category-project').value = post.category || '';
                document.getElementById('edit-facebook-project').value = post.facebook || '';
                // Add required only to visible fields
                document.getElementById('edit-title-project').setAttribute('required', 'required');
                document.getElementById('edit-description').setAttribute('required', 'required');
                document.getElementById('edit-category-project').setAttribute('required', 'required');
            }

            // Show the modal (use flex or block depending on your CSS)
            document.getElementById('edit-post-modal').style.display = 'flex';
        });
};

window.deletePost = function(postId, postType) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch('delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: postId,
                type: postType
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadPosts(currentPostType);
            } else {
                alert('Error deleting post');
            }
        })
    }
};

function showToast(message, isSuccess = true) {
    const toast = document.getElementById('toast');
    const toastIcon = toast.querySelector('.toast-icon');
    const toastMessage = toast.querySelector('.toast-message');
    const toastProgress = toast.querySelector('.toast-progress');
    toastMessage.textContent = message;
    if (isSuccess) {
        toastIcon.classList.remove('error');
        toastIcon.classList.add('success');
        toastIcon.className = 'fas fa-check-circle toast-icon success';
        toastProgress.style.backgroundColor = '#4caf50';
    } else {
        toastIcon.classList.remove('success');
        toastIcon.classList.add('error');
        toastIcon.className = 'fas fa-times-circle toast-icon error';
        toastProgress.style.backgroundColor = '#f44336';
    }
    toast.style.display = 'block';
    toastProgress.style.animation = 'progress 3s linear forwards';
    setTimeout(() => {
        toast.style.display = 'none';
        toastProgress.style.animation = 'none';
    }, 3000);
}

// Ensure DOM is loaded before attaching event listeners
document.addEventListener('DOMContentLoaded', function() {
    loadPosts(currentPostType);

    // Attach submit event after DOM is ready
    const editPostForm = document.getElementById('edit-post-form');
    if (editPostForm) {
        editPostForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Edit post form submitted');

            const postType = document.getElementById('edit-post-type').value;
            const formData = new FormData();
            formData.append('id', document.getElementById('edit-post-id').value);
            formData.append('type', postType);

            if (postType === 'news') {
                formData.append('title', document.getElementById('edit-title').value);
                formData.append('summary', document.getElementById('edit-summary').value);
                formData.append('content', document.getElementById('edit-content').value);
                formData.append('category', document.getElementById('edit-category').value);
                formData.append('facebook', document.getElementById('edit-facebook').value);
            } else {
                formData.append('title', document.getElementById('edit-title-project').value);
                formData.append('description', document.getElementById('edit-description').value);
                formData.append('category', document.getElementById('edit-category-project').value);
                formData.append('facebook', document.getElementById('edit-facebook-project').value);
            }

            // Image
            const imageInput = document.getElementById('edit-image');
            if (imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            } else {
                formData.append('current_image', document.getElementById('edit-current-image').value);
            }

            console.log('Sending data to update_post.php...');
            fetch('update_post.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Received response from update_post.php', response);
                return response.json();
            })
            .then(result => {
                console.log('Result from update_post.php:', result);
                if (result.success) {
                    document.getElementById('edit-post-modal').style.display = 'none';
                    loadPosts(currentPostType);
                    showToast('Post updated successfully!', true);
                } else {
                    showToast(result.error || 'Error updating post', false);
                    alert('Error: ' + (result.error || 'Unknown error'));
                }
            })
            .catch((err) => {
                showToast('Error updating post', false);
                alert('Fetch error: ' + err);
            });
        });
    } else {
        console.log('edit-post-form not found in DOM');
    }
});

// Modal close
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit-post-modal').style.display = 'none';
        document.getElementById('delete-modal').style.display = 'none';
    });
});

// Also close modal on cancel button
document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit-post-modal').style.display = 'none';
        document.getElementById('delete-modal').style.display = 'none';
    });
});

// Filter buttons
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', (e) => {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        e.target.classList.add('active');
        currentPostType = e.target.dataset.type;
        loadPosts(currentPostType);
    });
});

// Search functionality
document.getElementById('search-btn').addEventListener('click', () => {
    const searchQuery = document.getElementById('search-posts').value;
    loadPosts(currentPostType, searchQuery);
});

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    loadPosts(currentPostType);
});