<?php
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';


    $apiMap = [
        'login' => '/api/user/login',
        'register' => '/api/user/register'
    ];

    if (isset($apiMap[$action])) {
        $postData = [
            'username' => $_POST['username'],
            'password' => $_POST['password']
        ];

        if ($action === 'register') {
            $postData['email'] = $_POST['email'];
            $postData['phone'] = $_POST['phone'];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'http://localhost' . $apiMap[$action],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode === 200 && $result && isset($result['token'])) {
            echo "<script>
                    localStorage.setItem('token', '{$result['token']}');
                    window.location.href = '/';
                </script>";
            exit;
        } else {
            $errors[] = $result['message'] ?? 'Unknown error occurred';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login / Register</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        
        .auth-container {
            max-width: 500px;
            margin: 4rem auto;
            padding: 2.5rem;
            
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin: 1.8rem 0;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem 1.2rem;
            
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #3498db;
            outline: none;
        }

        .terms-group {
            margin: 2rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.2rem;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        .auth-container {
            max-width: 500px;
            margin: 4rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #2c3e50;
        }

        .tab-controls {
            margin-bottom: 2rem;
            border-bottom: 2px solid #3498db;
        }

        .tab-controls button {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border: none;
            background: none;
            color: #7f8c8d;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-controls button.active {
            color: #3498db;
            border-bottom: 2px solid #3498db;
        }

        .form-group {
            margin: 1.5rem 0;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-size: 1rem;
        }

        .terms-group {
            margin: 2rem 0;
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        button[type="submit"] {
            background: #3498db;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            transition: background 0.3s ease;
        }

        button[type="submit"]:hover {
            background: #2980b9;
        }

        .validation-message {
            color: #ff4444;
            margin: 1rem 0;
            text-align: center;
        }

        .error-message {
            color: #ff4444;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        input.invalid {
            border-color: #ff4444 !important;
        }
    </style>
</head>

<body>
    <!-- 保持原有HTML结构，移除所有HTML验证属性 -->
    <div class="auth-container">
        <div class="close-btn" onclick="window.location.href='/'">×</div>

        <div class="tab-controls">
            <button onclick="showForm('login')" class="active">Login</button>
            <button onclick="showForm('register')">Sign Up</button>
        </div>
        <!-- 登录表单 -->
        <form id="loginForm" method="post" style="display: block;">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username">
                <div class="error-message" data-error="username"></div>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password">
                <div class="error-message" data-error="password"></div>
            </div>
            <button type="submit">Login</button>
        </form>

        <!-- 注册表单 -->
        <form id="registerForm" method="post" style="display: none;">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username">
                <div class="error-message" data-error="username"></div>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email">
                <div class="error-message" data-error="email"></div>
            </div>
            <div class="form-group">
                <label>Phone Number:</label>
                <input type="tel" name="phone">
                <div class="error-message" data-error="phone"></div>
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password">
                <div class="error-message" data-error="password"></div>
            </div>
            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password">
                <div class="error-message" data-error="confirm_password"></div>
            </div>
            <div class="terms-group">
                <label>
                    <input type="checkbox" name="terms">
                    I agree to the Terms
                </label>
                <div class="error-message" data-error="terms"></div>
            </div>
            <button type="submit">Create Account</button>
        </form>
    </div>

    <script>
        
        const validationRules = {
            login: {
                username: value => value.trim().length >= 3 || 'Username must be at least 3 characters',
                password: value => value.length >= 6 || 'Password must be at least 6 characters'
            },
            register: {
                username: value => {
                    if (value.trim().length < 3) return 'Username must be at least 3 characters';
                    if (!/^[a-zA-Z0-9_]+$/.test(value)) return 'Username can only contain letters, numbers and underscores';
                    return true;
                },
                email: value => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || 'Invalid email address',
                phone: value => /^\d{10,15}$/.test(value) || 'Invalid phone number (10-15 digits)',
                password: value => value.length >= 6 || 'Password must be at least 6 characters',
                confirm_password: (value, formData) =>
                    value === formData.password || 'Passwords do not match',
                terms: checked => checked || 'You must agree to the terms'
            }
        };

        
        function handleSubmit(formType, e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            let isValid = true;

            
            form.querySelectorAll('.error-message').forEach(el => {
                el.classList.remove('show');
            });
            form.querySelectorAll('input').forEach(input => {
                input.classList.remove('invalid');
            });

            
            for (const [field, rule] of Object.entries(validationRules[formType])) {
                const value = field === 'terms' ? data[field] === 'on' : data[field] || '';
                const result = rule(value, data);

                if (result !== true) {
                    isValid = false;
                    const errorEl = form.querySelector(`[data-error="${field}"]`);
                    if (errorEl) {
                        errorEl.textContent = result;
                        errorEl.classList.add('show');
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) input.classList.add('invalid');
                    }
                }
            }
            console.log(JSON.stringify(data));

            
            if (isValid) {
                
                const apiPath = formType === 'login' ? '/api/user/login' : '/api/user/register';
                fetch(apiPath, { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => {
                        console.log('Response:', response); 
                        return response.json();
                    })
                    .then(result => {
                        if (result.token) {
                            localStorage.setItem('token', result.token);
                            localStorage.setItem('username', result.username);
                            window.location.href = 'index.php';
                        } else {
                            showGlobalError(result.error || 'Unknown error');
                        }
                    })
                    .catch(error => {
                        showGlobalError('Network error');
                        console.error('Error:', error);
                    });
            }
        }

        
        function showGlobalError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-message';
            errorDiv.innerHTML = `<p>${message}</p>`;
            document.querySelector('.auth-container').prepend(errorDiv);
        }

        
        document.getElementById('loginForm').addEventListener('submit', e => handleSubmit('login', e));
        document.getElementById('registerForm').addEventListener('submit', e => handleSubmit('register', e));

        
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function () {
                const formType = this.closest('form').id === 'loginForm' ? 'login' : 'register';
                const field = this.name;
                const rule = validationRules[formType][field];

                if (rule) {
                    const errorEl = this.closest('.form-group').querySelector('.error-message');
                    const formData = new FormData(this.closest('form'));
                    const data = Object.fromEntries(formData.entries());

                    const value = field === 'terms' ? data[field] === 'on' : data[field] || '';
                    const result = rule(value, data);

                    if (result !== true) {
                        errorEl.textContent = result;
                        errorEl.classList.add('show');
                        this.classList.add('invalid');
                    } else {
                        errorEl.classList.remove('show');
                        this.classList.remove('invalid');
                    }
                }
            });
        });
        function showForm(formType) {
            
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');

            loginForm.style.display = formType === 'login' ? 'block' : 'none';
            registerForm.style.display = formType === 'register' ? 'block' : 'none';

            
            document.querySelector('.validation-message').innerHTML = '';

            
            document.querySelectorAll('.tab-controls button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }
    </script>
</body>

</html>