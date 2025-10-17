// Ana JavaScript dosyası
document.addEventListener('DOMContentLoaded', function() {
    // Sepet sayısını güncelle
    updateCartCount();
    
    // Arama fonksiyonu
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    
    if(searchBtn && searchInput) {
        searchBtn.addEventListener('click', function() {
            const searchTerm = searchInput.value.trim();
            if(searchTerm) {
                window.location.href = 'products.php?search=' + encodeURIComponent(searchTerm);
            }
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if(e.key === 'Enter') {
                searchBtn.click();
            }
        });
    }
});

// Sepete ürün ekleme
function addToCart(productId) {
    <?php if(isset($_SESSION['user_id'])): ?>
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId + '&quantity=1'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showMessage('Ürün sepete eklendi!', 'success');
            updateCartCount();
        } else {
            showMessage('Hata: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('Bir hata oluştu!', 'error');
    });
    <?php else: ?>
    showMessage('Sepete ürün eklemek için giriş yapmalısınız!', 'error');
    setTimeout(() => {
        window.location.href = 'login.php';
    }, 2000);
    <?php endif; ?>
}

// Ürün detayını görüntüleme
function viewProduct(productId) {
    window.location.href = 'product-detail.php?id=' + productId;
}

// Sepet sayısını güncelleme
function updateCartCount() {
    <?php if(isset($_SESSION['user_id'])): ?>
    fetch('ajax/get_cart_count.php')
    .then(response => response.json())
    .then(data => {
        const cartCount = document.getElementById('cartCount');
        if(cartCount) {
            cartCount.textContent = data.count;
        }
    });
    <?php endif; ?>
}

// Mesaj gösterme
function showMessage(message, type) {
    // Mevcut mesajları kaldır
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Yeni mesaj oluştur
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message ' + type;
    messageDiv.textContent = message;
    messageDiv.style.position = 'fixed';
    messageDiv.style.top = '20px';
    messageDiv.style.right = '20px';
    messageDiv.style.zIndex = '9999';
    messageDiv.style.padding = '1rem';
    messageDiv.style.borderRadius = '5px';
    messageDiv.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    messageDiv.style.animation = 'fadeInUp 0.3s ease-out';
    
    document.body.appendChild(messageDiv);
    
    // 3 saniye sonra kaldır
    setTimeout(() => {
        messageDiv.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => {
            if(messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 3000);
}

// Smooth scroll
function smoothScroll(target) {
    document.querySelector(target).scrollIntoView({
        behavior: 'smooth'
    });
}

// Form validasyonu
function validateForm(formId) {
    const form = document.getElementById(formId);
    if(!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if(!field.value.trim()) {
            field.style.borderColor = '#e74c3c';
            isValid = false;
        } else {
            field.style.borderColor = '#ddd';
        }
    });
    
    return isValid;
}

// Loading göstergesi
function showLoading(element) {
    const loading = document.createElement('div');
    loading.className = 'loading';
    loading.id = 'loading-indicator';
    element.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('loading-indicator');
    if(loading) {
        loading.remove();
    }
}

// Sayfa yükleme animasyonu
window.addEventListener('load', function() {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease-in-out';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});

// CSS animasyonları
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-30px);
        }
    }
`;
document.head.appendChild(style);