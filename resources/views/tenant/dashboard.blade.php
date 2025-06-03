<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ $tenantId }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f7fa;
            min-height: 100vh;
        }
        .header {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 1px solid #e1e5e9;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
        }
        .tenant-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-left: 1rem;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .welcome-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        .welcome-card h2 {
            color: #333;
            margin-top: 0;
            font-size: 1.5rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #667eea;
        }
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1.2rem;
        }
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .actions {
            margin-top: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-right: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard <span class="tenant-badge">{{ $tenantId }}</span></h1>
    </div>
    
    <div class="container">
        <div class="welcome-card">
            <h2>Welcome to Your Tenant Dashboard!</h2>
            <p>You have successfully created your tenant and can now configure your own system. This is your dedicated space where you can manage your organization's data and settings.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Tenant Status</h3>
                <p>Active and Ready</p>
            </div>
            <div class="stat-card">
                <h3>Database</h3>
                <p>Isolated tenant database created</p>
            </div>
            <div class="stat-card">
                <h3>Domain</h3>
                <p>{{ $tenantId }}.tempahjer.test</p>
            </div>
            <div class="stat-card">
                <h3>Configuration</h3>
                <p>Ready for customization</p>
            </div>
        </div>
        
        <div class="actions">
            <h3>Quick Actions</h3>
            <a href="#" class="btn">Configure Settings</a>
            <a href="#" class="btn">Manage Users</a>
            <a href="#" class="btn">View Reports</a>
            <a href="/" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
</body>
</html>