// Kimlik doğrulama sayfaları JavaScript fonksiyonları

document.addEventListener('DOMContentLoaded', function() {
    // Şifre güçlülük kontrolü
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', checkPasswordStrength);
    }
    
    // Şifre eşleşme kontrolü
    const confirmPasswordInput = document.getElementById('confirm_password');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
    
    // Form validasyonu
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', validateForm);
    }
    
    // Telefon numarası formatı
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', formatPhoneNumber);
    }
});

// Şifre görünürlüğünü değiştir
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentNode.querySelector('.password-toggle');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Şifre güçlülük kontrolü
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthIndicator = document.getElementById('password-strength') || createStrengthIndicator();
    
    if (password.length === 0) {
        strengthIndicator.style.display = 'none';
        return;
    }
    
    let strength = 0;
    let strengthText = '';
    let strengthClass = '';
    
    // Uzunluk kontrolü
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    
    // Karakter çeşitliliği
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    if (strength < 3) {
        strengthText = 'Zayıf';
        strengthClass = 'strength-weak';
    } else if (strength < 5) {
        strengthText = 'Orta';
        strengthClass = 'strength-medium';
    } else {
        strengthText = 'Güçlü';
        strengthClass = 'strength-strong';
    }
    
    strengthIndicator.textContent = `Şifre gücü: ${strengthText}`;
    strengthIndicator.className = `password-strength ${strengthClass}`;
    strengthIndicator.style.display = 'block';
}

// Şifre güçlülük göstergesi oluştur
function createStrengthIndicator() {
    const passwordGroup = document.getElementById('password').closest('.form-group');
    const strengthIndicator = document.createElement('small');
    strengthIndicator.id = 'password-strength';
    strengthIndicator.className = 'password-strength';
    strengthIndicator.style.display = 'none';
    passwordGroup.appendChild(strengthIndicator);
    return strengthIndicator;
}

// Şifre eşleşme kontrolü
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchIndicator = document.getElementById('password-match') || createMatchIndicator();
    
    if (confirmPassword.length === 0) {
        matchIndicator.style.display = 'none';
        return;
    }
    
    if (password === confirmPassword) {
        matchIndicator.textContent = 'Şifreler eşleşiyor';
        matchIndicator.className = 'password-strength strength-strong';
    } else {
        matchIndicator.textContent = 'Şifreler eşleşmiyor';
        matchIndicator.className = 'password-strength strength-weak';
    }
    
    matchIndicator.style.display = 'block';
}

// Şifre eşleşme göstergesi oluştur
function createMatchIndicator() {
    const confirmPasswordGroup = document.getElementById('confirm_password').closest('.form-group');
    const matchIndicator = document.createElement('small');
    matchIndicator.id = 'password-match';
    matchIndicator.className = 'password-strength';
    matchIndicator.style.display = 'none';
    confirmPasswordGroup.appendChild(matchIndicator);
    return matchIndicator;
}

// Telefon numarası formatı
function formatPhoneNumber() {
    const input = document.getElementById('phone');
    let value = input.value.replace(/\D/g, ''); // Sadece rakamları al
    
    if (value.length > 0) {
        if (value.startsWith('90')) {
            value = value.substring(2);
        }
        
        if (value.length >= 1) {
            value = value.substring(0, 10); // Maksimum 10 hane
        }
        
        if (value.length >= 4) {
            value = value.substring(0, 3) + ' ' + value.substring(3);
        }
        if (value.length >= 8) {
            value = value.substring(0, 7) + ' ' + value.substring(7);
        }
        if (value.length >= 11) {
            value = value.substring(0, 10) + ' ' + value.substring(10);
        }
        
        value = '+90 ' + value;
    }
    
    input.value = value;
}

// Form validasyonu
function validateForm(event) {
    const form = event.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Loading durumu
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kayıt oluşturuluyor...';
    submitButton.disabled = true;
    
    // Form verilerini kontrol et
    const formData = new FormData(form);
    const password = formData.get('password');
    const confirmPassword = formData.get('confirm_password');
    
    if (password !== confirmPassword) {
        event.preventDefault();
        showAlert('Şifreler eşleşmiyor!', 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        return;
    }
    
    if (password.length < 6) {
        event.preventDefault();
        showAlert('Şifre en az 6 karakter olmalıdır!', 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        return;
    }
    
    // E-posta formatı kontrolü
    const email = formData.get('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        event.preventDefault();
        showAlert('Geçerli bir e-posta adresi girin!', 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        return;
    }
    
    // Kullanım şartları kontrolü
    const terms = formData.get('terms');
    if (!terms) {
        event.preventDefault();
        showAlert('Kullanım şartlarını kabul etmelisiniz!', 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        return;
    }
}

// Alert gösterme
function showAlert(message, type = 'info') {
    // Mevcut alert'i kaldır
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Yeni alert oluştur
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
    `;
    
    // Form'un başına ekle
    const form = document.querySelector('.auth-form');
    form.insertBefore(alert, form.firstChild);
    
    // 5 saniye sonra kaldır
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Enter tuşu ile form gönderme
document.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        const form = event.target.closest('form');
        if (form) {
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                submitButton.click();
            }
        }
    }
});

// Sayfa yüklendiğinde form alanlarına odaklan
window.addEventListener('load', function() {
    const firstInput = document.querySelector('input:not([type="hidden"]):not([type="checkbox"])');
    if (firstInput) {
        firstInput.focus();
    }
});