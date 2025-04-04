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
    <link style="text/css" rel="stylesheet" href="css/global.css">
    <style>
        * {
            box-sizing: border-box;
        }

        .auth-container {
            max-width: 500px;
            margin: 4rem auto;
            padding: 2rem 2.5rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .form-group {
            margin: 1.5rem 0;
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
            font-size: 0.9rem;
            color: #7f8c8d;
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

        .validation-message,
        .error-message {
            color: #ff4444;
            margin: 1rem 0;
            text-align: center;
        }

        .error-message {
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
            text-align: left;
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
        <div class="auth-container">
            <div class="close-btn" onclick="window.location.href='/'">×</div>
            <div class="validation-message"></div>

            <div class="tab-controls">
                <button onclick="showForm('login')" class="active">Login</button>
                <button onclick="showForm('register')">Sign Up</button>
            </div>
        
        <!-- login form -->
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

        <!-- register form -->
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

        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const validationMessage = document.querySelector('.validation-message');
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

        function showForm(formType) {
            
            const buttons = document.querySelectorAll('.tab-controls > button');

            loginForm.style.display = formType === 'login' ? 'block' : 'none';
            registerForm.style.display = formType === 'register' ? 'block' : 'none';

            buttons.forEach((button, index) => {
                if ((formType === 'login' && index === 0) || (formType === 'register' && index === 1)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
            validationMessage.innerHTML = '';
        }

        function showGlobalError(message) {
            validationMessage.innerHTML = `<p>${message}</p>`;
        }


        loginForm.addEventListener('submit', e => handleSubmit('login', e));
        registerForm.addEventListener('submit', e => handleSubmit('register', e));

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
    </script>
</body>

</html>
