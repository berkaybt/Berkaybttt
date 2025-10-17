<?php

function find_user_by_email(string $email): ?array {
    $stmt = getPDO()->prepare('SELECT id, name, email, password_hash, is_admin FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function register_user(string $name, string $email, string $password): array {
    $name = trim($name);
    $email = strtolower(trim($email));
    if ($name === '' || $email === '' || $password === '') {
        return ['success' => false, 'error' => 'Tüm alanlar zorunludur.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'E-posta geçerli değil.'];
    }
    if (find_user_by_email($email)) {
        return ['success' => false, 'error' => 'Bu e-posta zaten kayıtlı.'];
    }
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = getPDO()->prepare('INSERT INTO users (name, email, password_hash, is_admin) VALUES (?, ?, ?, 0)');
    $stmt->execute([$name, $email, $hash]);
    return ['success' => true, 'error' => null];
}

function login_user(string $email, string $password): bool {
    $user = find_user_by_email(strtolower(trim($email)));
    if (!$user) { return false; }
    if (!password_verify($password, $user['password_hash'])) { return false; }
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'is_admin' => (int)$user['is_admin'],
    ];
    return true;
}

function logout_user(): void {
    unset($_SESSION['user']);
}
