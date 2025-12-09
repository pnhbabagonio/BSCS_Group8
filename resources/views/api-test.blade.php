<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSITS-NEXUS Member API Testing</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .header .subtitle {
            background: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            display: inline-block;
        }
        
        .api-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
        
        .btn-logout {
            background: #e53e3e;
        }
        
        .btn-logout:hover {
            background: #c53030;
        }
        
        .btn-member {
            background: #38a169;
        }
        
        .btn-member:hover {
            background: #2f855a;
        }
        
        .result-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .status-success {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-error {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .token-display {
            background: #2d3748;
            color: #81e6d9;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin-top: 10px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .instructions {
            background: #e6fffa;
            border-left: 4px solid #38b2ac;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        
        .instructions h3 {
            color: #234e52;
            margin-bottom: 10px;
        }
        
        .instructions ul {
            list-style-type: none;
            padding-left: 0;
        }
        
        .instructions li {
            padding: 5px 0;
            border-bottom: 1px solid #c6f6d5;
        }
        
        .instructions code {
            background: #c6f6d5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        
        .member-info {
            background: #ebf8ff;
            border-left: 4px solid #4299e1;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        
        .member-info h3 {
            color: #2c5282;
            margin-bottom: 10px;
        }
        
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .test-credentials {
            background: #fff5f5;
            border-left: 4px solid #fc8181;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        
        .test-credentials h3 {
            color: #742a2a;
            margin-bottom: 10px;
        }
        
        .info-box {
            display: flex;
            align-items: center;
            background: #e6fffa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .info-box::before {
            content: "ℹ️";
            margin-right: 10px;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 PSITS-NEXUS Member API Testing</h1>
            <p>Test member-only API endpoints for PSITS-NEXUS system</p>
            <p>Base URL: <strong>http://10.135.220.168:8000</strong></p>
            <div class="subtitle">
                <strong>Note:</strong> Registration is disabled. Only pre-registered PSITS members can login.
            </div>
        </div>
        
        <div class="api-section">
            <h2 class="section-title">🔐 Member Authentication</h2>
            
            <div class="grid">
                <!-- Login Form -->
                <div>
                    <h3>Member Login</h3>
                    <form id="loginForm">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" placeholder="member@psits-nexus.com" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" placeholder="Your PSITS password" required>
                        </div>
                        <div class="form-group">
                            <label for="device_name">Device Name</label>
                            <input type="text" id="device_name" placeholder="Web Browser" value="Web Browser" required>
                        </div>
                        <button type="submit" class="btn">Login as Member</button>
                    </form>
                    
                    <div class="test-credentials">
                        <h3>Test Credentials (if available)</h3>
                        <p><strong>Email:</strong> member@example.com</p>
                        <p><strong>Password:</strong> password123</p>
                        <p><em>Note: Contact admin for your actual credentials</em></p>
                    </div>
                    
                    <div id="tokenInfo" style="display: none;">
                        <h3>Your Authentication Token:</h3>
                        <div class="token-display" id="tokenDisplay"></div>
                    </div>
                </div>
                
                <!-- Information Section -->
                <div>
                    <div class="info-box">
                        <strong>System Information:</strong> Only PSITS-NEXUS members can access this API.
                    </div>
                    
                    <div class="member-info">
                        <h3>📋 Available Member Endpoints:</h3>
                        <ul>
                            <li><code>POST /api/auth/login</code> - Member Login</li>
                            <li><code>GET /api/auth/user</code> - Get current member info</li>
                            <li><code>POST /api/auth/logout</code> - Logout</li>
                            <li><code>POST /api/auth/refresh</code> - Refresh token</li>
                            <li><code>GET /api/member/profile</code> - Get member profile</li>
                            <li><code>GET /api/member/dashboard</code> - Get dashboard summary</li>
                            <li><code>GET /api/member/payments</code> - Get member payments</li>
                            <li><code>GET /api/member/requirements</code> - Get member requirements</li>
                            <li><code>GET /api/events</code> - View all events</li>
                            <li><code>GET /api/protected-test</code> - Test protected endpoint</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Authenticated Actions -->
        <div class="api-section" id="authSection" style="display: none;">
            <h2 class="section-title">🔒 Member Actions</h2>
            
            <div class="member-info">
                <h3>👤 Logged in as: <span id="loggedInUser"></span></h3>
                <p>Role: <strong><span id="loggedInRole"></span></strong></p>
            </div>
            
            <div class="btn-group">
                <button class="btn btn-logout" onclick="logout()">Logout</button>
                <button class="btn" onclick="getUser()">Get User Info</button>
                <button class="btn btn-member" onclick="getMemberProfile()">Member Profile</button>
                <button class="btn btn-member" onclick="getMemberDashboard()">Dashboard</button>
                <button class="btn btn-member" onclick="getMemberPayments()">My Payments</button>
                <button class="btn btn-member" onclick="getMemberRequirements()">Requirements</button>
                <button class="btn" onclick="getEvents()">View Events</button>
                <button class="btn" onclick="testProtected()">Test Protected</button>
                <button class="btn" onclick="refreshToken()">Refresh Token</button>
            </div>
            
            <div class="instructions">
                <h3>📋 Member API Features:</h3>
                <ul>
                    <li><strong>Profile:</strong> View your personal information</li>
                    <li><strong>Dashboard:</strong> Get summary of your activities</li>
                    <li><strong>Payments:</strong> View your payment history</li>
                    <li><strong>Requirements:</strong> Check your requirements status</li>
                    <li><strong>Events:</strong> Browse upcoming events</li>
                    <li><strong>Security:</strong> Bearer token authentication</li>
                </ul>
            </div>
        </div>
        
        <!-- Public API Test -->
        <div class="api-section">
            <h2 class="section-title">🌐 Public API Test</h2>
            <button class="btn" onclick="testPublic()">Test Public Endpoint</button>
            <div class="instructions">
                <p><strong>Note:</strong> Public endpoints don't require authentication.</p>
            </div>
        </div>
        
        <!-- Results Display -->
        <div class="api-section">
            <h2 class="section-title">📊 API Response</h2>
            <div id="resultContainer">
                <div class="result-box" id="result">
                    API responses will appear here...
                </div>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = 'http://10.135.220.168:8000/api';
        let authToken = localStorage.getItem('psits_api_token') || null;
        let currentUser = null;
        
        // Check if user is already logged in
        if (authToken) {
            checkAndRestoreSession();
        }
        
        // Configure axios defaults
        axios.defaults.baseURL = 'http://10.135.220.168:8000';
        
        // Add request interceptor to add token
        axios.interceptors.request.use(config => {
            if (authToken && !config.url.includes('/auth/login')) {
                config.headers.Authorization = `Bearer ${authToken}`;
            }
            return config;
        });
        
        // Add response interceptor to handle errors
        axios.interceptors.response.use(
            response => response,
            error => {
                const resultDiv = document.getElementById('result');
                if (error.response) {
                    if (error.response.status === 401) {
                        // Token expired or invalid
                        authToken = null;
                        localStorage.removeItem('psits_api_token');
                        document.getElementById('authSection').style.display = 'none';
                        document.getElementById('tokenInfo').style.display = 'none';
                        
                        resultDiv.innerHTML = `<div class="status status-error">Session Expired (401)</div>Please login again.`;
                    } else {
                        resultDiv.innerHTML = `<div class="status status-error">Error ${error.response.status}</div>${JSON.stringify(error.response.data, null, 2)}`;
                    }
                } else if (error.request) {
                    resultDiv.innerHTML = `<div class="status status-error">Network Error</div>Cannot connect to server. Check if server is running at ${BASE_URL}`;
                } else {
                    resultDiv.innerHTML = `<div class="status status-error">Request Error</div>${error.message}`;
                }
                return Promise.reject(error);
            }
        );
        
        // Check and restore session on page load
        async function checkAndRestoreSession() {
            try {
                const response = await axios.get('/api/auth/user');
                currentUser = response.data.user;
                showAuthenticatedUI();
            } catch (error) {
                // Session is invalid, clear token
                authToken = null;
                localStorage.removeItem('psits_api_token');
            }
        }
        
        // Show authenticated UI
        function showAuthenticatedUI() {
            document.getElementById('authSection').style.display = 'block';
            document.getElementById('tokenInfo').style.display = 'block';
            document.getElementById('tokenDisplay').textContent = authToken ? 
                authToken.substring(0, 50) + '...' : 'No token';
            
            if (currentUser) {
                document.getElementById('loggedInUser').textContent = currentUser.name || currentUser.email;
                document.getElementById('loggedInRole').textContent = currentUser.role || 'Member';
            }
        }
        
        // Login Form Handler
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const device_name = document.getElementById('device_name').value;
            
            try {
                const response = await axios.post('/api/auth/login', {
                    email,
                    password,
                    device_name
                });
                
                authToken = response.data.token;
                localStorage.setItem('psits_api_token', authToken);
                currentUser = response.data.user;
                
                showAuthenticatedUI();
                
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Login Successful</div>` +
                    `<p>Welcome back, ${currentUser.name}!</p>` +
                    `<p>Role: <strong>${currentUser.role}</strong></p>` +
                    `<p>Token generated for: ${device_name}</p>`;
                    
            } catch (error) {
                // Error handled by interceptor
            }
        });
        
        // Test Public Endpoint
        async function testPublic() {
            try {
                const response = await axios.get('/api/hello');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Public API Working</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Current User
        async function getUser() {
            try {
                const response = await axios.get('/api/auth/user');
                currentUser = response.data.user;
                document.getElementById('loggedInUser').textContent = currentUser.name || currentUser.email;
                document.getElementById('loggedInRole').textContent = currentUser.role || 'Member';
                
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ User Info Retrieved</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Member Profile
        async function getMemberProfile() {
            try {
                const response = await axios.get('/api/member/profile');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Member Profile</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Member Dashboard
        async function getMemberDashboard() {
            try {
                const response = await axios.get('/api/member/dashboard');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Dashboard Summary</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Member Payments
        async function getMemberPayments() {
            try {
                const response = await axios.get('/api/member/payments');
                const payments = response.data.payments || [];
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Member Payments (${payments.length})</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Member Requirements
        async function getMemberRequirements() {
            try {
                const response = await axios.get('/api/member/requirements');
                const requirements = response.data.requirements || [];
                const paidCount = requirements.filter(r => r.status === 'paid').length;
                const unpaidCount = requirements.filter(r => r.status === 'unpaid').length;
                
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Member Requirements</div>` +
                    `<p>Total: ${requirements.length} | Paid: ${paidCount} | Unpaid: ${unpaidCount}</p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Events
        async function getEvents() {
            try {
                const response = await axios.get('/api/events');
                const events = response.data.events || [];
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Events (${events.length})</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Test Protected Endpoint
        async function testProtected() {
            try {
                const response = await axios.get('/api/protected-test');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Protected Endpoint Accessed</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Refresh Token
        async function refreshToken() {
            try {
                const response = await axios.post('/api/auth/refresh');
                authToken = response.data.token;
                localStorage.setItem('psits_api_token', authToken);
                document.getElementById('tokenDisplay').textContent = authToken.substring(0, 50) + '...';
                
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Token Refreshed</div>${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Logout
        async function logout() {
            try {
                await axios.post('/api/auth/logout');
                
                authToken = null;
                currentUser = null;
                localStorage.removeItem('psits_api_token');
                
                document.getElementById('authSection').style.display = 'none';
                document.getElementById('tokenInfo').style.display = 'none';
                document.getElementById('loginForm').reset();
                
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Logged out successfully</div>`;
                    
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Pre-fill with test data for easy testing
        window.addEventListener('load', () => {
            document.getElementById('email').value = 'member@example.com';
            document.getElementById('password').value = 'password123';
            document.getElementById('device_name').value = 'PSITS Web Browser';
        });
    </script>
</body>
</html>