// Ürünler sayfası JavaScript fonksiyonları

document.addEventListener('DOMContentLoaded', function() {
    // Görünüm değiştirme
    const viewButtons = document.querySelectorAll('.view-btn');
    const productsGrid = document.getElementById('products-grid');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Aktif butonu güncelle
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Grid görünümünü değiştir
            if (view === 'list') {
                productsGrid.classList.add('list-view');
            } else {
                productsGrid.classList.remove('list-view');
            }
        });
    });
    
    // Sepete ekleme
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            addToCart(productId);
        });
    });
    
    // İstek listesi
    const wishlistButtons = document.querySelectorAll('.wishlist-btn');
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            toggleWishlist(productId, this);
        });
    });
    
    // Fiyat filtresi
    const priceInputs = document.querySelectorAll('.price-inputs input');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            validatePriceInputs();
        });
    });
});

// Sıralama değiştirme
function changeSort() {
    const sortSelect = document.getElementById('sort');
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('sort', sortSelect.value);
    currentUrl.searchParams.set('page', '1'); // İlk sayfaya dön
    window.location.href = currentUrl.toString();
}

// Sepete ekleme fonksiyonu
function addToCart(productId) {
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Ürün sepete eklendi!', 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Bir hata oluştu!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bir hata oluştu!', 'error');
    });
}

// İstek listesi toggle
function toggleWishlist(productId, button) {
    fetch('ajax/toggle_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.in_wishlist) {
                button.classList.add('active');
                button.innerHTML = '<i class="fas fa-heart"></i>';
                showNotification('Ürün istek listesine eklendi!', 'success');
            } else {
                button.classList.remove('active');
                button.innerHTML = '<i class="far fa-heart"></i>';
                showNotification('Ürün istek listesinden çıkarıldı!', 'info');
            }
        } else {
            showNotification(data.message || 'Bir hata oluştu!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bir hata oluştu!', 'error');
    });
}

// Fiyat input validasyonu
function validatePriceInputs() {
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    
    const minPrice = parseFloat(minPriceInput.value);
    const maxPrice = parseFloat(maxPriceInput.value);
    
    if (minPrice && maxPrice && minPrice > maxPrice) {
        maxPriceInput.setCustomValidity('Maksimum fiyat minimum fiyattan küçük olamaz');
    } else {
        maxPriceInput.setCustomValidity('');
    }
}

// Bildirim gösterme
function showNotification(message, type = 'info') {
    // Mevcut bildirimi kaldır
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Yeni bildirim oluştur
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Stil ekle
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        padding: 1rem 1.5rem;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    // 3 saniye sonra kaldır
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 3000);
}

// Sepet sayısını güncelle
function updateCartCount() {
    fetch('ajax/get_cart_count.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.count;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// CSS animasyonları ekle
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);