document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const projectForm = document.getElementById('project-form');
    const titleInput = document.getElementById('project-title');
    const categorySelect = document.getElementById('project-category');
    const descriptionInput = document.getElementById('project-description');
    const descriptionCharCount = document.getElementById('description-char-count');
    const imageInput = document.getElementById('project-featured-image');
    const imagePreview = document.getElementById('image-preview');
    const saveDraftBtn = document.getElementById('save-draft');
    const facebookInput = document.getElementById('project-facebook');

    // Preview elements
    const previewTitle = document.getElementById('preview-title');
    const previewDate = document.getElementById('preview-date');
    const previewDescription = document.getElementById('preview-description');
    const previewImage = document.getElementById('preview-image');
    const previewFacebook = document.getElementById('preview-facebook');

    // Check if all required elements exist
    if (!projectForm || !titleInput || !categorySelect || !descriptionInput || 
        !imageInput || !imagePreview || !saveDraftBtn || !facebookInput) {
        console.error('Required form elements not found');
        return;
    }

    if (!previewTitle || !previewDate || !previewDescription || 
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
            previewTitle.textContent = titleInput.value || 'Your project title will appear here';
        }
    });

    // Update description char count and preview
    descriptionInput.addEventListener('input', () => {
        if (descriptionCharCount) {
            descriptionCharCount.textContent = descriptionInput.value.length;
        }
        if (previewDescription) {
            previewDescription.textContent = descriptionInput.value || 'Your project description will appear here...';
        }
    });

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

    // Save Draft
    saveDraftBtn.addEventListener('click', e => {
        e.preventDefault();

        const draft = {
            title: titleInput.value,
            category: categorySelect.value,
            description: descriptionInput.value,
            lastSaved: new Date().toISOString()
        };

        localStorage.setItem('projectDraft', JSON.stringify(draft));

        const confirm = document.createElement('div');
        confirm.className = 'save-confirmation';
        confirm.innerHTML = `<i class="fas fa-check-circle"></i> Draft saved successfully!`;
        const formActions = document.querySelector('.form-actions');
        if (formActions) {
            formActions.appendChild(confirm);
            setTimeout(() => confirm.remove(), 3000);
        }
    });

    // Load draft
    const savedDraft = localStorage.getItem('projectDraft');
    if (savedDraft) {
        const draft = JSON.parse(savedDraft);
        const loadDraft = confirm('You have a saved draft. Would you like to load it?');
        if (loadDraft) {
            try {
                titleInput.value = draft.title || '';
                descriptionInput.value = draft.description || '';
                categorySelect.value = draft.category || '';
                
                if (descriptionCharCount) {
                    descriptionCharCount.textContent = descriptionInput.value.length;
                }

                // Update preview
                if (previewTitle) {
                    previewTitle.textContent = draft.title || 'Your project title will appear here';
                }
                if (previewDescription) {
                    previewDescription.textContent = draft.description || 'Your project description will appear here...';
                }
            } catch (error) {
                console.error('Error loading draft:', error);
                localStorage.removeItem('projectDraft');
                showToast('Error loading draft. Starting fresh.', true);
            }
        } else {
            // If user chooses not to load draft, clear it
            localStorage.removeItem('projectDraft');
        }
    }

    // Set default image preview
    if (previewImage) {
        previewImage.src = "Logo/SK.png";
    }
    if (imagePreview) {
        imagePreview.innerHTML = `<i class="fas fa-image"></i><span>No image selected</span>`;
    }

    // Validate before submit
    projectForm.addEventListener('submit', e => {
        e.preventDefault();

        // Validation
        if (!titleInput.value.trim()) {
            alert('Please enter a project title.');
            titleInput.focus();
            return;
        }

        if (!categorySelect.value) {
            alert('Please select a category.');
            categorySelect.focus();
            return;
        }

        if (!descriptionInput.value.trim()) {
            alert('Please enter project description.');
            descriptionInput.focus();
            return;
        }

        const formData = new FormData(projectForm);

        // Send with AJAX
        fetch('submit-project.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || "Project published successfully!");
                
                // Reset form inputs
                titleInput.value = '';
                categorySelect.value = '';
                descriptionInput.value = '';
                facebookInput.value = '';
                imageInput.value = '';
                
                // Reset character count
                if (descriptionCharCount) {
                    descriptionCharCount.textContent = '0';
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
                    previewTitle.textContent = 'Your project title will appear here';
                }
                if (previewDescription) {
                    previewDescription.textContent = 'Your project description will appear here...';
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
