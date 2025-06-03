<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $tenantId }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 2rem;
        }
        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 300;
        }
        .highlight {
            color: #667eea;
            font-weight: 600;
        }
        p {
            color: #666;
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .tenant-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
            border-left: 4px solid #667eea;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hello <span class="highlight">World</span>!</h1>
        <p>Welcome to your tenant application. You have successfully set up your own subdomain and can now configure your system.</p>
        
        <div class="tenant-info">
            <h3>Tenant Information</h3>
            <p><strong>Tenant ID:</strong> {{ $tenantId }}</p>
            <p><strong>Domain:</strong> {{ $tenantId }}.tempahjer.test</p>
        </div>
        
        <a href="/dashboard" class="btn">Go to Dashboard</a>
    </div>
</body>
</html>