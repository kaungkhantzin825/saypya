import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Global JavaScript functions
window.showNotification = function(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
};

// Wishlist toggle function
window.toggleWishlist = function(courseId) {
    fetch(`/courses/${courseId}/wishlist`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const button = document.querySelector(`[data-course-id="${courseId}"]`);
            if (button) {
                const icon = button.querySelector('i');
                if (data.in_wishlist) {
                    icon.className = 'fas fa-heart text-red-500';
                } else {
                    icon.className = 'far fa-heart text-gray-400';
                }
            }
            showNotification(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
};

// Video progress tracking
window.trackVideoProgress = function(lessonId, currentTime, duration) {
    if (currentTime > 0 && duration > 0) {
        const watchTime = Math.floor(currentTime);
        const isCompleted = (currentTime / duration) >= 0.9; // 90% completion
        
        fetch(`/lessons/${lessonId}/progress`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                watch_time: watchTime,
                is_completed: isCompleted
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.completed) {
                showNotification('Lesson completed!', 'success');
                // Update UI to show completion
                const lessonItem = document.querySelector(`[data-lesson-id="${lessonId}"]`);
                if (lessonItem) {
                    lessonItem.classList.add('completed');
                }
            }
        })
        .catch(error => console.error('Error tracking progress:', error));
    }
};

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form[action*="search"]');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="q"]');
        let searchTimeout;
        
        searchInput?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3) {
                    // Implement live search suggestions here
                    fetchSearchSuggestions(this.value);
                }
            }, 300);
        });
    }
    
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('[data-mobile-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});

// Search suggestions (placeholder function)
function fetchSearchSuggestions(query) {
    // This would typically make an AJAX request to get search suggestions
    console.log('Searching for:', query);
}

// Course rating functionality
window.rateCourse = function(courseId, rating) {
    fetch(`/courses/${courseId}/rate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ rating })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Rating submitted successfully!');
            // Update the rating display
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to submit rating', 'error');
    });
};

// Discussion functionality
window.submitDiscussion = function(form) {
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Discussion posted successfully!');
            form.reset();
            // Refresh discussions or add new discussion to DOM
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to post discussion', 'error');
    });
    
    return false; // Prevent default form submission
};

// Lazy loading for images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});