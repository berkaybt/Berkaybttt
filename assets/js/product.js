// Ürün detay sayfası JavaScript fonksiyonları

document.addEventListener('DOMContentLoaded', function() {
    // Galeri thumbnail'ları
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('main-product-image');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const imageSrc = this.dataset.image;
            
            // Aktif thumbnail'ı güncelle
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Ana resmi değiştir
            mainImage.src = imageSrc;
        });
    });
    
    // Tab değiştirme
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Aktif tab butonunu güncelle
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Aktif tab pane'i güncelle
            tabPanes.forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === targetTab) {
                    pane.classList.add('active');
                }
            });
        });
    });
    
    // Sepete ekleme
    const addToCartButton = document.querySelector('.add-to-cart');
    if (addToCartButton) {
        addToCartButton.addEventListener('click', function() {
            const productId = this.dataset.id;
            const quantity = document.getElementById('quantity').value;
            addToCart(productId, quantity);
        });
    }
    
    // İstek listesi
    const wishlistButton = document.querySelector('.wishlist-btn');
    if (wishlistButton) {
        wishlistButton.addEventListener('click', function() {
            const productId = this.dataset.id;
            toggleWishlist(productId, this);
        });
    }
    
    // İlgili ürünlerde sepete ekleme
    const relatedAddToCartButtons = document.querySelectorAll('.related-products .add-to-cart');
    relatedAddToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            addToCart(productId, 1);
        });
    });
});

// Miktar değiştirme
function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    const currentQuantity = parseInt(quantityInput.value);
    const newQuantity = Math.max(1, currentQuantity + delta);
    const maxQuantity = parseInt(quantityInput.getAttribute('max'));
    
    if (newQuantity <= maxQuantity) {
        quantityInput.value = newQuantity;
    }
}

// Sepete ekleme fonksiyonu
function addToCart(productId, quantity = 1) {
    const addToCartButton = document.querySelector('.add-to-cart');
    const originalText = addToCartButton.innerHTML;
    
    // Loading durumu
    addToCartButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ekleniyor...';
    addToCartButton.disabled = true;
    
    fetch('ajax/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: parseInt(quantity)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${quantity} adet ürün sepete eklendi!`, 'success');
            updateCartCount();
        } else {
            showNotification(data.message || 'Bir hata oluştu!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bir hata oluştu!', 'error');
    })
    .finally(() => {
        // Butonu eski haline getir
        addToCartButton.innerHTML = originalText;
        addToCartButton.disabled = false;
    });
}

// İstek listesi toggle
function toggleWishlist(productId, button) {
    const originalHTML = button.innerHTML;
    
    // Loading durumu
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
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
                button.innerHTML = '<i class="fas fa-heart"></i> İstek Listesinde';
                button.classList.add('active');
                showNotification('Ürün istek listesine eklendi!', 'success');
            } else {
                button.innerHTML = '<i class="far fa-heart"></i> İstek Listesi';
                button.classList.remove('active');
                showNotification('Ürün istek listesinden çıkarıldı!', 'info');
            }
        } else {
            showNotification(data.message || 'Bir hata oluştu!', 'error');
            button.innerHTML = originalHTML;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bir hata oluştu!', 'error');
        button.innerHTML = originalHTML;
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Ürün paylaşma
function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: 'Bu ürünü inceleyin!',
            url: window.location.href
        });
    } else {
        // Fallback: URL'yi kopyala
        navigator.clipboard.writeText(window.location.href).then(() => {
            showNotification('Ürün linki kopyalandı!', 'success');
        }).catch(() => {
            showNotification('Link kopyalanamadı!', 'error');
        });
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
    
    .wishlist-btn.active {
        background: #e74c3c;
        color: white;
    }
`;
document.head.appendChild(style);