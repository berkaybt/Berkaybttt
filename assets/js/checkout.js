// Ödeme sayfası JavaScript fonksiyonları

document.addEventListener('DOMContentLoaded', function() {
    // Ödeme yöntemi değişikliği
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const creditCardDetails = document.getElementById('credit_card_details');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'credit_card') {
                creditCardDetails.style.display = 'block';
            } else {
                creditCardDetails.style.display = 'none';
            }
        });
    });
    
    // Kart numarası formatı
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', formatCardNumber);
    }
    
    // CVV formatı
    const cardCvvInput = document.getElementById('card_cvv');
    if (cardCvvInput) {
        cardCvvInput.addEventListener('input', formatCVV);
    }
    
    // Form validasyonu
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', validateCheckoutForm);
    }
});

// Fatura adresi toggle
function toggleBillingAddress() {
    const sameAddressCheckbox = document.getElementById('same_address');
    const billingAddressGroup = document.getElementById('billing_address_group');
    const billingAddressInput = document.getElementById('billing_address');
    const shippingAddressInput = document.getElementById('shipping_address');
    
    if (sameAddressCheckbox.checked) {
        billingAddressGroup.style.display = 'none';
        billingAddressInput.value = shippingAddressInput.value;
        billingAddressInput.required = false;
    } else {
        billingAddressGroup.style.display = 'block';
        billingAddressInput.required = true;
    }
}

// Kart numarası formatı
function formatCardNumber() {
    const input = document.getElementById('card_number');
    let value = input.value.replace(/\D/g, ''); // Sadece rakamları al
    
    // Maksimum 16 hane
    value = value.substring(0, 16);
    
    // 4'erli gruplar halinde formatla
    if (value.length > 0) {
        value = value.match(/.{1,4}/g).join(' ');
    }
    
    input.value = value;
}

// CVV formatı
function formatCVV() {
    const input = document.getElementById('card_cvv');
    let value = input.value.replace(/\D/g, ''); // Sadece rakamları al
    
    // Maksimum 4 hane
    value = value.substring(0, 4);
    
    input.value = value;
}

// Form validasyonu
function validateCheckoutForm(event) {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Loading durumu
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';
    submitButton.disabled = true;
    
    // Zorunlu alanları kontrol et
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#e74c3c';
            showFieldError(field, 'Bu alan zorunludur');
        } else {
            field.style.borderColor = '#e1e8ed';
            clearFieldError(field);
        }
    });
    
    // Kredi kartı seçildiyse kart bilgilerini kontrol et
    const selectedPaymentMethod = form.querySelector('input[name="payment_method"]:checked');
    if (selectedPaymentMethod && selectedPaymentMethod.value === 'credit_card') {
        const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
        const cardCvv = document.getElementById('card_cvv').value;
        const cardMonth = document.getElementById('card_month').value;
        const cardYear = document.getElementById('card_year').value;
        
        if (cardNumber.length < 16) {
            isValid = false;
            showFieldError(document.getElementById('card_number'), 'Geçerli bir kart numarası girin');
        }
        
        if (cardCvv.length < 3) {
            isValid = false;
            showFieldError(document.getElementById('card_cvv'), 'Geçerli bir CVV girin');
        }
        
        if (!cardMonth || !cardYear) {
            isValid = false;
            showFieldError(document.getElementById('card_month'), 'Kart geçerlilik tarihini seçin');
        }
    }
    
    if (!isValid) {
        event.preventDefault();
        showNotification('Lütfen tüm zorunlu alanları doğru şekilde doldurun!', 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        return;
    }
    
    // Form gönderiliyor, loading durumunu koru
    // Gerçek uygulamada burada ödeme işlemi yapılır
}

// Alan hatası göster
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = 'color: #e74c3c; font-size: 0.8rem; margin-top: 0.25rem;';
    
    field.parentNode.appendChild(errorDiv);
}

// Alan hatasını temizle
function clearFieldError(field) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
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
    
    // 5 saniye sonra kaldır
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
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
    
    .field-error {
        color: #e74c3c;
        font-size: 0.8rem;
        margin-top: 0.25rem;
    }
    
    .form-group input:invalid,
    .form-group textarea:invalid,
    .form-group select:invalid {
        border-color: #e74c3c;
    }
    
    .form-group input:valid,
    .form-group textarea:valid,
    .form-group select:valid {
        border-color: #27ae60;
    }
`;
document.head.appendChild(style);