// Modal Sistemi
function showModal(title, content, size = 'medium') {
    const sizes = {
        small: '400px',
        medium: '600px',
        large: '800px',
        xlarge: '1000px'
    };
    
    const modalHTML = `
        <div class="modal-overlay" id="modalOverlay" onclick="closeModal()">
            <div class="modal-container" style="max-width: ${sizes[size]};" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <button class="modal-close" onclick="closeModal()">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    setTimeout(() => {
        document.getElementById('modalOverlay').classList.add('active');
    }, 10);
}

function closeModal() {
    const overlay = document.getElementById('modalOverlay');
    if (overlay) {
        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Form submit helper
function submitModalForm(formId, url, successCallback) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModal();
            if (successCallback) successCallback(data);
            location.reload();
        } else {
            alert(data.message || 'Bir hata oluştu!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu!');
    });
}
