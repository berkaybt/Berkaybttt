// Sepet sayfası JavaScript fonksiyonları

document.addEventListener('DOMContentLoaded', function() {
    // Miktar değiştirme olaylarını dinle
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.closest('.cart-item').dataset.productId;
            const quantity = parseInt(this.value);
            updateQuantity(productId, 0, quantity);
        });
    });
    
    // İndirim kodu formu
    const promoForm = document.querySelector('.promo-form');
    if (promoForm) {
        promoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const promoCode = this.querySelector('input[name="promo_code"]').value;
            applyPromoCode(promoCode);
        });
    }
});

// Miktar güncelleme
function updateQuantity(productId, delta, newQuantity = null) {
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    const quantityInput = cartItem.querySelector('.quantity-input');
    const currentQuantity = parseInt(quantityInput.value);
    
    let quantity;
    if (newQuantity !== null) {
        quantity = Math.max(1, parseInt(newQuantity));
    } else {
        quantity = Math.max(1, currentQuantity + delta);
    }
    
    // Maksimum stok kontrolü
    const maxStock = parseInt(quantityInput.getAttribute('max'));
    if (quantity > maxStock) {
        showNotification(`Maksimum ${maxStock} adet sipariş verebilirsiniz!`, 'error');
        quantity = maxStock;
    }
    
    // Loading durumu
    const quantityControls = cartItem.querySelector('.quantity-controls');
    const originalHTML = quantityControls.innerHTML;
    quantityControls.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // AJAX isteği
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_quantity&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.text())
    .then(() => {
        // Sayfayı yenile
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        quantityControls.innerHTML = originalHTML;
        showNotification('Bir hata oluştu!', 'error');
    });
}

// Ürün kaldırma
function removeItem(productId) {
    if (!confirm('Bu ürünü sepetten kaldırmak istediğinizden emin misiniz?')) {
        return;
    }
    
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    const originalHTML = cartItem.innerHTML;
    
    // Loading durumu
    cartItem.innerHTML = '<div class="loading-item"><i class="fas fa-spinner fa-spin"></i> Kaldırılıyor...</div>';
    
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove_item&product_id=${productId}`
    })
    .then(response => response.text())
    .then(() => {
        // Animasyon ile kaldır
        cartItem.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            cartItem.remove();
            updateCartCount();
            
            // Eğer sepet boşsa sayfayı yenile
            if (document.querySelectorAll('.cart-item').length === 0) {
                location.reload();
            }
        }, 300);
    })
    .catch(error => {
        console.error('Error:', error);
        cartItem.innerHTML = originalHTML;
        showNotification('Bir hata oluştu!', 'error');
    });
}

// İndirim kodu uygulama
function applyPromoCode(promoCode) {
    if (!promoCode.trim()) {
        showNotification('Lütfen bir indirim kodu girin!', 'error');
        return;
    }
    
    const promoForm = document.querySelector('.promo-form');
    const submitBtn = promoForm.querySelector('button');
    const originalText = submitBtn.textContent;
    
    // Loading durumu
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uygulanıyor...';
    submitBtn.disabled = true;
    
    fetch('ajax/apply_promo_code.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            promo_code: promoCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('İndirim kodu başarıyla uygulandı!', 'success');
            // Sayfayı yenile
            location.reload();
        } else {
            showNotification(data.message || 'Geçersiz indirim kodu!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bir hata oluştu!', 'error');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
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
    
    .loading-item {
        text-align: center;
        padding: 2rem;
        color: #7f8c8d;
        font-style: italic;
    }
    
    .loading-item i {
        margin-right: 0.5rem;
    }
`;
document.head.appendChild(style);