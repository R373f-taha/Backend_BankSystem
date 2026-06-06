// script.js - نسخة تعمل 100%

async function register() {
    const name = document.getElementById('regName')?.value;
    const email = document.getElementById('regEmail')?.value;
    const password = document.getElementById('regPass')?.value;
    const outputDiv = document.getElementById('output');

    if (!name || !email || !password) {
        outputDiv.innerHTML = '⚠️ Please fill all fields!';
        return;
    }

    outputDiv.innerHTML = '⏳ Registering...';
    
    try {
        const response = await fetch('http://127.0.0.1:8000/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name, email, password })
        });

        const data = await response.json();
        
        if (response.ok) {
            if (data.token) {
                localStorage.setItem('auth_token', data.token);
                outputDiv.innerHTML = `✅ Registration successful!\nWelcome ${data.user.name}\n\nToken: ${data.token.substring(0, 50)}...`;
            } else {
                outputDiv.innerHTML = `✅ Success:\n${JSON.stringify(data, null, 2)}`;
            }
        } else {
            outputDiv.innerHTML = `❌ Error (${response.status}):\n${JSON.stringify(data, null, 2)}`;
        }
    } catch (error) {
        console.error('Registration error:', error);
        outputDiv.innerHTML = `❌ Connection error:\n${error.message}\n\nMake sure Laravel is running on http://127.0.0.1:8000`;
    }
}

async function login() {
    const email = document.getElementById('loginEmail')?.value;
    const password = document.getElementById('loginPass')?.value;
    const outputDiv = document.getElementById('output');

    if (!email || !password) {
        outputDiv.innerHTML = '⚠️ Please fill all fields!';
        return;
    }

    outputDiv.innerHTML = '⏳ Logging in...';
    
    try {
        // جلب CSRF cookie (اختياري لكن مفيد)
        await fetch('http://127.0.0.1:8000/sanctum/csrf-cookie', {
            credentials: 'include'
        }).catch(() => {}); // تجاهل الخطأ إذا فشل

        const response = await fetch('http://127.0.0.1:8000/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();
        
        if (response.ok && data.token) {
            localStorage.setItem('auth_token', data.token);
            outputDiv.innerHTML = `✅ Login successful!\nWelcome ${data.user.name}\n\nToken saved!`;
        } else {
            outputDiv.innerHTML = `❌ Login failed (${response.status}):\n${data.message || JSON.stringify(data)}`;
        }
    } catch (error) {
        console.error('Login error:', error);
        outputDiv.innerHTML = `❌ Error: ${error.message}`;
    }
}

async function getUsers() {
    const token = localStorage.getItem('auth_token');
    const outputDiv = document.getElementById('output');
    
    if (!token) {
        outputDiv.innerHTML = '⚠️ No token found!\nPlease login first.';
        return;
    }

    outputDiv.innerHTML = '⏳ Fetching users...';
    
    try {
        const response = await fetch('http://127.0.0.1:8000/api/users', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (response.ok) {
            outputDiv.innerHTML = `✅ Users (${data.length} found):\n${JSON.stringify(data, null, 2)}`;
        } else {
            outputDiv.innerHTML = `❌ Failed (${response.status}):\n${JSON.stringify(data, null, 2)}`;
            
            // إذا كان التوكن منتهي، احذفه
            if (response.status === 401) {
                localStorage.removeItem('auth_token');
                outputDiv.innerHTML += '\n\n⚠️ Token expired. Please login again.';
            }
        }
    } catch (error) {
        console.error('Get users error:', error);
        outputDiv.innerHTML = `❌ Error: ${error.message}`;
    }
}

// اختبار الاتصال عند تحميل الصفحة
async function testConnection() {
    const outputDiv = document.getElementById('output');
    outputDiv.innerHTML = '🔍 Testing connection to Laravel...';
    
    try {
        const response = await fetch('http://127.0.0.1:8000/api/users');
        outputDiv.innerHTML = `✅ Laravel is reachable!\nResponse status: ${response.status}\n(401 is normal - means API requires authentication)`;
    } catch (error) {
        outputDiv.innerHTML = `❌ Cannot reach Laravel!\nError: ${error.message}\n\nMake sure:\n1. php artisan serve is running\n2. Laravel is on http://127.0.0.1:8000`;
    }
}

// شغل اختبار الاتصال تلقائياً
testConnection();