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
        
        /* Server Configuration Section */
        .config-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .config-title {
            color: white;
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .config-title::before {
            content: "⚙️";
        }
        
        .config-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .config-form label {
            color: white;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .config-form input {
            padding: 10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 14px;
        }
        
        .config-form input:focus {
            outline: none;
            border-color: white;
            background: white;
        }
        
        .btn-config {
            background: white;
            color: #667eea;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-config:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
        }
        
        .server-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 15px;
        }
        
        .server-status.connected {
            background: rgba(72, 187, 120, 0.2);
            color: #48bb78;
        }
        
        .server-status.disconnected {
            background: rgba(245, 101, 101, 0.2);
            color: #f56565;
        }
        
        .server-status::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .server-status.connected::before {
            background: #48bb78;
            box-shadow: 0 0 0 2px rgba(72, 187, 120, 0.3);
        }
        
        .server-status.disconnected::before {
            background: #f56565;
            box-shadow: 0 0 0 2px rgba(245, 101, 101, 0.3);
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
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
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
        
        .current-server {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .current-server strong {
            color: white;
        }
        
        @media (max-width: 768px) {
            .config-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 PSITS-NEXUS Member API Testing</h1>
            <p>Test member-only API endpoints for PSITS-NEXUS system</p>
        </div>
        
        <!-- Server Configuration Section -->
        <div class="config-section">
            <h3 class="config-title">Server Configuration</h3>
            <form id="serverConfigForm">
                <div class="config-form">
                    <div class="form-group">
                        <label for="serverIp">Server IP Address</label>
                        <input type="text" id="serverIp" placeholder="10.98.66.168" required>
                    </div>
                    <div class="form-group">
                        <label for="serverPort">Port</label>
                        <input type="text" id="serverPort" placeholder="8000" required>
                    </div>
                    <button type="submit" class="btn-config">Update Server Settings</button>
                </div>
            </form>
            <div class="current-server">
                <span>Current Server:</span>
                <strong id="currentServerUrl">http://10.98.66.168:8000</strong>
                <span id="serverStatus" class="server-status disconnected">Disconnected</span>
            </div>
            <p style="color: rgba(255, 255, 255, 0.8); margin-top: 10px; font-size: 14px;">
                <strong>Tip:</strong> Change this when connecting from different networks. Example: Home WiFi might use 192.168.1.100
            </p>
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
                <!-- Support Ticket Buttons -->
                <button class="btn" onclick="createSupportTicket()">Create Support Ticket</button>
                <button class="btn" onclick="getMyTickets()">View My Tickets</button>
                <button class="btn" onclick="getTicketDetails()">Get Ticket Details</button>
            </div>
            
            <div class="instructions">
                <h3>📋 Member API Features:</h3>
                <ul>
                    <li><strong>Profile:</strong> View your personal information</li>
                    <li><strong>Dashboard:</strong> Get summary of your activities</li>
                    <li><strong>Payments:</strong> View your payment history</li>
                    <li><strong>Requirements:</strong> Check your requirements status</li>
                    <li><strong>Events:</strong> Browse upcoming events</li>
                    <li><strong>Support Tickets:</strong> Create and view support requests</li>
                    <li><strong>Security:</strong> Bearer token authentication</li>
                </ul>
            </div>
        </div>

        <!-- Add a new section for ticket creation -->
        <div class="api-section" id="ticketSection" style="display: none;">
            <h2 class="section-title">🎫 Create Support Ticket</h2>
            <form id="ticketForm">
                <div class="form-group">
                    <label for="ticketSubject">Subject *</label>
                    <input type="text" id="ticketSubject" placeholder="Brief description of your issue" required>
                </div>
                <div class="form-group">
                    <label for="ticketMessage">Message *</label>
                    <textarea id="ticketMessage" rows="4" placeholder="Describe your issue in detail..." required></textarea>
                </div>
                <div class="grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="form-group">
                        <label for="ticketCategory">Category *</label>
                        <select id="ticketCategory" required>
                            <option value="">Select Category</option>
                            <option value="technical">Technical Issue</option>
                            <option value="billing">Billing/Payment</option>
                            <option value="account">Account Issue</option>
                            <option value="general">General Inquiry</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ticketPriority">Priority *</label>
                        <select id="ticketPriority" required>
                            <option value="">Select Priority</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="ticketAttachments">Attachments (optional)</label>
                    <input type="text" id="ticketAttachments" placeholder="Comma-separated file paths/URLs">
                    <small>Note: In production, you would implement file upload separately</small>
                </div>
                <button type="submit" class="btn">Submit Ticket</button>
                <button type="button" class="btn btn-logout" onclick="toggleTicketForm()">Cancel</button>
            </form>
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
        // Server Configuration Management
        class ServerConfig {
            constructor() {
                this.ip = localStorage.getItem('psits_server_ip') || '10.98.66.168';
                this.port = localStorage.getItem('psits_server_port') || '8000';
                this.baseUrl = `http://${this.ip}:${this.port}`;
                this.apiBaseUrl = `${this.baseUrl}/api`;
            }
            
            save(ip, port) {
                this.ip = ip;
                this.port = port;
                this.baseUrl = `http://${ip}:${port}`;
                this.apiBaseUrl = `${this.baseUrl}/api`;
                
                localStorage.setItem('psits_server_ip', ip);
                localStorage.setItem('psits_server_port', port);
                
                // Update axios defaults
                axios.defaults.baseURL = this.baseUrl;
                
                // Update UI
                this.updateUI();
                this.testConnection();
                
                return this.baseUrl;
            }
            
            updateUI() {
                document.getElementById('serverIp').value = this.ip;
                document.getElementById('serverPort').value = this.port;
                document.getElementById('currentServerUrl').textContent = this.baseUrl;
                document.title = `PSITS-NEXUS API Testing - ${this.baseUrl}`;
            }
            
            async testConnection() {
                const statusElement = document.getElementById('serverStatus');
                statusElement.textContent = 'Testing...';
                statusElement.className = 'server-status disconnected';
                
                try {
                    // Try to reach the server with a simple HEAD request or timeout
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 3000);
                    
                    const response = await fetch(`${this.baseUrl}/api/hello`, {
                        method: 'GET',
                        signal: controller.signal
                    });
                    
                    clearTimeout(timeoutId);
                    
                    if (response.ok) {
                        statusElement.textContent = 'Connected';
                        statusElement.className = 'server-status connected';
                    } else {
                        statusElement.textContent = 'Server Error';
                        statusElement.className = 'server-status disconnected';
                    }
                } catch (error) {
                    statusElement.textContent = 'Disconnected';
                    statusElement.className = 'server-status disconnected';
                }
            }
            
            getBaseUrl() {
                return this.baseUrl;
            }
            
            getApiBaseUrl() {
                return this.apiBaseUrl;
            }
        }

        // Initialize server configuration
        const serverConfig = new ServerConfig();
        let authToken = localStorage.getItem('psits_api_token') || null;
        let currentUser = null;
        
        // Initialize UI
        document.addEventListener('DOMContentLoaded', () => {
            serverConfig.updateUI();
            serverConfig.testConnection();
            
            // Check if user is already logged in
            if (authToken) {
                checkAndRestoreSession();
            }
            
            // Pre-fill login form
            document.getElementById('email').value = 'member@example.com';
            document.getElementById('password').value = 'password123';
            document.getElementById('device_name').value = 'PSITS Web Browser';
        });
        
        // Configure axios defaults
        axios.defaults.baseURL = serverConfig.getBaseUrl();
        
        // Server configuration form handler
        document.getElementById('serverConfigForm').addEventListener('submit', (e) => {
            e.preventDefault();
            
            const ip = document.getElementById('serverIp').value.trim();
            const port = document.getElementById('serverPort').value.trim();
            
            // Basic validation
            if (!ip || !port) {
                alert('Please enter both IP address and port');
                return;
            }
            
            // Simple IP validation
            const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipPattern.test(ip)) {
                alert('Please enter a valid IPv4 address (e.g., 10.98.66.168)');
                return;
            }
            
            // Save configuration
            serverConfig.save(ip, port);
            
            // Clear auth token when changing servers (for security)
            if (authToken) {
                authToken = null;
                localStorage.removeItem('psits_api_token');
                document.getElementById('authSection').style.display = 'none';
                document.getElementById('tokenInfo').style.display = 'none';
                
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">Server configuration updated</div>` +
                    `<p>Server changed to: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `<p>You have been logged out. Please login again with the new server.</p>`;
            } else {
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">Server configuration updated</div>` +
                    `<p>Server changed to: <strong>${serverConfig.getBaseUrl()}</strong></p>`;
            }
        });
        
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
                    resultDiv.innerHTML = `<div class="status status-error">Network Error</div>Cannot connect to server at ${serverConfig.getBaseUrl()}. Check if server is running.`;
                    serverConfig.testConnection();
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
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `<p>Token generated for: ${device_name}</p>`;
                    
                // Update server status
                serverConfig.testConnection();
                    
            } catch (error) {
                // Error handled by interceptor
            }
        });
        
        // Test Public Endpoint
        async function testPublic() {
            try {
                const response = await axios.get('/api/hello');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Public API Working</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
                    
                // Update server status
                serverConfig.testConnection();
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
                    `<div class="status status-success">✅ User Info Retrieved</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Member Profile
        async function getMemberProfile() {
            try {
                const response = await axios.get('/api/member/profile');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Member Profile</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Get Member Dashboard
        async function getMemberDashboard() {
            try {
                const response = await axios.get('/api/member/dashboard');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Dashboard Summary</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
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
                    `<div class="status status-success">✅ Member Payments (${payments.length})</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
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
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
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
                    `<div class="status status-success">✅ Events (${events.length})</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Test Protected Endpoint
        async function testProtected() {
            try {
                const response = await axios.get('/api/protected-test');
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Protected Endpoint Accessed</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
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
                    `<div class="status status-success">✅ Token Refreshed</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
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
                    `<div class="status status-success">✅ Logged out successfully</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>`;
                    
            } catch (error) {
                // Error handled by interceptor
            }
        }
        
        // Show/hide ticket form
        function toggleTicketForm() {
            const ticketSection = document.getElementById('ticketSection');
            ticketSection.style.display = ticketSection.style.display === 'none' ? 'block' : 'none';
        }

        // Show ticket creation form with pre-filled data
        function createSupportTicket() {
            if (!authToken) {
                document.getElementById('result').innerHTML = 
                    `<div class="status status-error">❌ Not Authenticated</div>Please login first to create a support ticket.`;
                return;
            }
            
            // Show the form
            toggleTicketForm();
            
            // Pre-fill form with test data
            document.getElementById('ticketSubject').value = 'Unable to access member dashboard';
            document.getElementById('ticketMessage').value = 'I\'m experiencing issues accessing my member dashboard. When I try to log in, I get redirected to the homepage. This started happening yesterday.';
            document.getElementById('ticketCategory').value = 'technical';
            document.getElementById('ticketPriority').value = 'medium';
            document.getElementById('ticketAttachments').value = '';
        }

        // Get user's support tickets
        async function getMyTickets() {
            if (!authToken) {
                document.getElementById('result').innerHTML = 
                    `<div class="status status-error">❌ Not Authenticated</div>Please login first.`;
                return;
            }
            
            try {
                const response = await axios.get('/api/support-tickets');
                const tickets = response.data.data.tickets || [];
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Your Support Tickets (${tickets.length})</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }

        // Get specific ticket details
        async function getTicketDetails() {
            if (!authToken) {
                document.getElementById('result').innerHTML = 
                    `<div class="status status-error">❌ Not Authenticated</div>Please login first.`;
                return;
            }
            
            const ticketId = prompt('Enter Ticket ID:');
            if (!ticketId) return;
            
            try {
                const response = await axios.get(`/api/support-tickets/${ticketId}`);
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Ticket Details</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `${JSON.stringify(response.data, null, 2)}`;
            } catch (error) {
                // Error handled by interceptor
            }
        }

        // Handle ticket form submission
        document.getElementById('ticketForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!authToken) {
                document.getElementById('result').innerHTML = 
                    `<div class="status status-error">❌ Not Authenticated</div>Please login first.`;
                return;
            }
            
            const ticketData = {
                subject: document.getElementById('ticketSubject').value,
                message: document.getElementById('ticketMessage').value,
                category: document.getElementById('ticketCategory').value,
                priority: document.getElementById('ticketPriority').value,
                attachments: document.getElementById('ticketAttachments').value 
                    ? document.getElementById('ticketAttachments').value.split(',').map(item => item.trim())
                    : []
            };
            
            try {
                const response = await axios.post('/api/support-tickets', ticketData);
                document.getElementById('result').innerHTML = 
                    `<div class="status status-success">✅ Ticket Created Successfully</div>` +
                    `<p>Server: <strong>${serverConfig.getBaseUrl()}</strong></p>` +
                    `<p><strong>Ticket ID:</strong> ${response.data.data.ticket.id}</p>` +
                    `<p><strong>Reference:</strong> ${response.data.data.reference_number}</p>` +
                    `<p><strong>Message:</strong> ${response.data.message}</p>` +
                    `<hr/>${JSON.stringify(response.data, null, 2)}`;
                
                // Hide form and reset
                toggleTicketForm();
                document.getElementById('ticketForm').reset();
                
            } catch (error) {
                // Error handled by interceptor
            }
        });

        // Quick server presets (optional - you can add this feature)
        function setServerPreset(presetName) {
            const presets = {
                'lab': { ip: '10.98.66.168', port: '8000' },
                'home': { ip: '192.168.1.100', port: '8000' },
                'mobile': { ip: '192.168.43.1', port: '8000' }
            };
            
            if (presets[presetName]) {
                serverConfig.save(presets[presetName].ip, presets[presetName].port);
            }
        }
    </script>
</body>
</html>