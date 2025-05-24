document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const newsForm = document.getElementById('news-form');
    const titleInput = document.getElementById('news-title');
    const categorySelect = document.getElementById('news-category');
    const summaryInput = document.getElementById('news-summary');
    const summaryCharCount = document.getElementById('summary-char-count');
    const contentInput = document.getElementById('news-content');
    const contentCharCount = document.getElementById('content-char-count');
    const imageInput = document.getElementById('news-featured-image');
    const imagePreview = document.getElementById('image-preview');
    const facebookInput = document.getElementById('news-facebook');

    // Preview elements
    const previewTitle = document.getElementById('preview-title');
    const previewDate = document.getElementById('preview-date');
    const previewSummary = document.getElementById('preview-summary');
    const previewContent = document.getElementById('preview-content');
    const previewImage = document.getElementById('preview-image');
    const previewFacebook = document.getElementById('preview-facebook');

    // Check if all required elements exist
    if (!newsForm || !titleInput || !categorySelect || !summaryInput || !contentInput || 
        !imageInput || !imagePreview || !facebookInput) {
        console.error('Required form elements not found');
        return;
    }

    if (!previewTitle || !previewDate || !previewSummary || !previewContent || 
        !previewImage || !previewFacebook) {
        console.error('Required preview elements not found');
        return;
    }

    // Set current date
    const today = new Date();
    if (previewDate) {
        previewDate.textContent = today.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Update title
    titleInput.addEventListener('input', () => {
        if (previewTitle) {
            previewTitle.textContent = titleInput.value || 'Your news title will appear here';
        }
    });

    // Update summary char count and preview
    summaryInput.addEventListener('input', () => {
        if (summaryCharCount) {
            summaryCharCount.textContent = summaryInput.value.length;
        }
        if (previewSummary) {
            previewSummary.textContent = summaryInput.value || 'Your news summary will appear here...';
        }
    });

    // Update content char count and preview
    contentInput.addEventListener('input', () => {
        if (contentCharCount) {
            contentCharCount.textContent = contentInput.value.length;
        }
        if (previewContent) {
            previewContent.textContent = contentInput.value || 'Your news content will appear here...';
        }
    });

    // Update image preview to use SK logo as default
    if (previewImage) {
        previewImage.src = "Logo/SK.png";
    }
    if (imagePreview) {
        imagePreview.innerHTML = `<i class="fas fa-image"></i><span>No image selected</span>`;
    }

    // Update image
    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                if (previewImage) {
                    previewImage.src = e.target.result;
                }
                if (imagePreview) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
            };
            reader.readAsDataURL(file);
        } else {
            if (previewImage) {
                previewImage.src = "Logo/SK.png";
            }
            if (imagePreview) {
                imagePreview.innerHTML = `<i class="fas fa-image"></i><span>No image selected</span>`;
            }
        }
    });

    // Facebook link preview
    facebookInput.addEventListener('input', () => {
        const link = facebookInput.value.trim();
        if (previewFacebook) {
            if (link) {
                previewFacebook.href = link;
                previewFacebook.style.display = 'inline-block';
            } else {
                previewFacebook.style.display = 'none';
            }
        }
    });

    // Image preview click triggers file picker
    if (imagePreview) {
        imagePreview.addEventListener('click', () => imageInput.click());
    }

    // Validate before submit
    newsForm.addEventListener('submit', e => {
        e.preventDefault();

        // Validation
        if (!titleInput.value.trim()) {
            alert('Please enter a news title.');
            titleInput.focus();
            return;
        }

        if (!categorySelect.value) {
            alert('Please select a category.');
            categorySelect.focus();
            return;
        }

        if (!summaryInput.value.trim()) {
            alert('Please enter a news summary.');
            summaryInput.focus();
            return;
        }

        if (!contentInput.value.trim()) {
            alert('Please enter news content.');
            contentInput.focus();
            return;
        }

        const formData = new FormData(newsForm);

        // Send with AJAX
        fetch('submit-news.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || "News published successfully!");
                
                // Reset form inputs
                titleInput.value = '';
                categorySelect.value = '';
                summaryInput.value = '';
                contentInput.value = '';
                facebookInput.value = '';
                imageInput.value = '';
                
                // Reset character counts
                if (summaryCharCount) {
                    summaryCharCount.textContent = '0';
                }
                if (contentCharCount) {
                    contentCharCount.textContent = '0';
                }
                
                // Reset image preview
                if (imagePreview) {
                    imagePreview.innerHTML = `<i class="fas fa-image"></i><span>No image selected</span>`;
                }
                if (previewImage) {
                    previewImage.src = "Logo/SK.png";
                }
                
                // Reset Facebook preview
                if (previewFacebook) {
                    previewFacebook.style.display = 'none';
                }
                
                // Reset preview text
                if (previewTitle) {
                    previewTitle.textContent = 'Your news title will appear here';
                }
                if (previewSummary) {
                    previewSummary.textContent = 'Your news summary will appear here...';
                }
                if (previewContent) {
                    previewContent.textContent = 'Your news content will appear here...';
                }
            } else {
                showToast("❌ Something went wrong while publishing.", true);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            showToast("❌ Submission failed. Please try again.", true);
        });
    });

    // Toast function for both success and error
    function showToast(message, isError = false) {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'submission-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.backgroundColor = isError ? '#f44336' : '#4caf50';
        toast.style.color = '#fff';
        toast.style.position = 'fixed';
        toast.style.bottom = '30px';
        toast.style.right = '30px';
        toast.style.padding = '16px 24px';
        toast.style.borderRadius = '8px';
        toast.style.zIndex = 9999;
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
});
