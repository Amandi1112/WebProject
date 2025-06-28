<?php
session_start();

// Database Configuration
$host = 'localhost';
$dbname = 'allura_estrella';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Simple authentication (in production, use proper password hashing)
$admin_username = 'admin';
$admin_password = 'allura2025'; // Change this password

if (isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = "Invalid credentials";
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Check if admin is logged in
$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];

// Handle AJAX requests
if ($is_logged_in && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_products':
            $stmt = $pdo->query("
                SELECT p.*, 
                (SELECT SUM(co.stock_quantity) FROM color_options co WHERE co.product_id = p.id) as color_stock,
                (SELECT SUM(so.stock_quantity) FROM size_options so WHERE so.product_id = p.id) as size_stock
                FROM products p 
                ORDER BY created_at DESC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
            
        case 'get_product_colors':
            $stmt = $pdo->prepare("SELECT * FROM color_options WHERE product_id = ?");
            $stmt->execute([$_GET['product_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
            
        case 'get_product_sizes':
            $stmt = $pdo->prepare("SELECT * FROM size_options WHERE product_id = ?");
            $stmt->execute([$_GET['product_id']]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
            
        case 'get_available_sizes':
            $stmt = $pdo->query("SELECT * FROM sizes ORDER BY id");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
            
        case 'get_orders':
            $stmt = $pdo->query("
                SELECT o.*, c.name as customer_name, c.email as customer_email 
                FROM orders o 
                LEFT JOIN customers c ON o.customer_id = c.id 
                ORDER BY o.created_at DESC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
            
        case 'get_customers':
            $stmt = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            exit;
            
        case 'get_stats':
            $stats = [];
            
            // Total products
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
            $stats['total_products'] = $stmt->fetch()['count'];
            
            // Total orders
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $stmt->fetch()['count'];
            
            // Total customers
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM customers");
            $stats['total_customers'] = $stmt->fetch()['count'];
            
            // Total revenue
            $stmt = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'completed'");
            $stats['total_revenue'] = $stmt->fetch()['revenue'] ?? 0;
            
            // Recent orders count
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
            $stats['today_orders'] = $stmt->fetch()['count'];
            
            echo json_encode($stats);
            exit;
    }
}

// Handle form submissions
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        try {
            $pdo->beginTransaction();
            
            // Insert product
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, category, image_url, status) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['product_name'],
                $_POST['product_description'],
                $_POST['product_price'],
                $_POST['product_category'],
                $_POST['image_url'],
                $_POST['product_status']
            ]);
            
            $product_id = $pdo->lastInsertId();
            
            // Insert color options
            $colors = json_decode($_POST['color_options'], true);
            $color_stmt = $pdo->prepare("
                INSERT INTO color_options (product_id, color_name, color_code, stock_quantity, image_url)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($colors as $color) {
                $color_stmt->execute([
                    $product_id,
                    $color['name'],
                    $color['code'],
                    $color['stock'],
                    $color['image']
                ]);
            }
            
            // Insert size options
            $sizes = json_decode($_POST['size_options'], true);
            $size_stmt = $pdo->prepare("
                INSERT INTO size_options (product_id, size_name, stock_quantity)
                VALUES (?, ?, ?)
            ");
            
            foreach ($sizes as $size) {
                $size_stmt->execute([
                    $product_id,
                    $size['name'],
                    $size['stock']
                ]);
            }
            
            $pdo->commit();
            $success_message = "Product added successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Error adding product: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_order_status'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['new_status'], $_POST['order_id']]);
        $success_message = "Order status updated successfully!";
    }
    
    if (isset($_POST['add_customer'])) {
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, email, phone, address, city, postal_code) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['customer_name'],
            $_POST['customer_email'],
            $_POST['customer_phone'],
            $_POST['customer_address'],
            $_POST['customer_city'],
            $_POST['customer_postal']
        ]);
        $success_message = "Customer added successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allura Estrella - Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-form {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-form h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.2s;
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
            width: auto;
        }

        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: rgba(255,255,255,0.95);
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }

        .nav-item {
            padding: 12px 16px;
            margin-bottom: 8px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .nav-item:hover, .nav-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .header {
            background: rgba(255,255,255,0.95);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #333;
            font-size: 28px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255,255,255,0.95);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .content-section {
            background: rgba(255,255,255,0.95);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .content-section h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e1e1;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status.active { background: #d4edda; color: #155724; }
        .status.inactive { background: #f8d7da; color: #721c24; }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.completed { background: #d4edda; color: #155724; }
        .status.cancelled { background: #f8d7da; color: #721c24; }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 8px;
        }

        .success {
            color: #155724;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #d4edda;
            border-radius: 8px;
        }

        /* Color and Size management styles */
        .options-container {
            margin: 20px 0;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 15px;
        }

        .option-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            align-items: center;
        }

        .color-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #ddd;
        }

        .add-option-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .remove-option-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: auto;
        }

        .option-form-row {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .option-form-row input, .option-form-row select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Product details modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close-btn {
            float: right;
            font-size: 24px;
            cursor: pointer;
        }

        .chips-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .color-chip {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 20px;
            background: #f0f0f0;
        }

        .color-chip-swatch {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 5px;
            border: 1px solid #ddd;
        }

        .size-chip {
            padding: 5px 15px;
            border-radius: 20px;
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .main-content {
                order: 1;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>

<?php if (!$is_logged_in): ?>
    <div class="login-container">
        <form method="POST" class="login-form">
            <h2>Allura Estrella Admin</h2>
            
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn">Login</button>
        </form>
    </div>

<?php else: ?>
    <div class="dashboard">
        <div class="sidebar">
            <h2>Allura Estrella</h2>
            <div class="nav-item active" onclick="showSection('dashboard')">Dashboard</div>
            <div class="nav-item" onclick="showSection('products')">Products</div>
            <div class="nav-item" onclick="showSection('orders')">Orders</div>
            <div class="nav-item" onclick="showSection('customers')">Customers</div>
            <div class="nav-item" onclick="showSection('add-product')">Add Product</div>
            <div class="nav-item" onclick="showSection('add-customer')">Add Customer</div>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <form method="POST" style="margin: 0;">
                    <button type="submit" name="logout" class="btn btn-sm">Logout</button>
                </form>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard" class="content-section active">
                <h2>Dashboard Overview</h2>
                <div class="stats-grid" id="stats-grid">
                    <!-- Stats will be loaded here via JavaScript -->
                </div>
            </div>

            <!-- Products Section -->
            <div id="products" class="content-section">
                <h2>Products Management</h2>
                <table class="table" id="products-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Colors</th>
                            <th>Sizes</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Products will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Orders Section -->
            <div id="orders" class="content-section">
                <h2>Orders Management</h2>
                <table class="table" id="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Orders will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Customers Section -->
            <div id="customers" class="content-section">
                <h2>Customers Management</h2>
                <table class="table" id="customers-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Customers will be loaded here via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Add Product Section -->
            <div id="add-product" class="content-section">
                <h2>Add New Product</h2>
                <form method="POST" id="product-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label>Price (Rs)</label>
                            <input type="number" name="product_price" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="product_category" required>
                                <option value="">Select Category</option>
                                <option value="Dresses">Dresses</option>
                                <option value="Skirts">Skirts</option>
                                <option value="Shirts">Shirts</option>
                                <option value="Shorts">Shorts</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="product_status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Main Image URL</label>
                            <input type="url" name="image_url" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="product_description" rows="4" required></textarea>
                    </div>
                    
                    <!-- Color Options Section -->
                    <div class="form-group">
                        <label>Color Options</label>
                        <div class="options-container" id="color-options-container">
                            <!-- Color options will be added here -->
                        </div>
                        <button type="button" class="add-option-btn" onclick="addColorOption()">+ Add Color Option</button>
                        <input type="hidden" name="color_options" id="color-options-input">
                    </div>
                    
                    <!-- Size Options Section -->
                    <div class="form-group">
                        <label>Size Options</label>
                        <div class="options-container" id="size-options-container">
                            <!-- Size options will be added here -->
                        </div>
                        <button type="button" class="add-option-btn" onclick="addSizeOption()">+ Add Size Option</button>
                        <input type="hidden" name="size_options" id="size-options-input">
                    </div>
                    
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </form>
            </div>

            <!-- Add Customer Section -->
            <div id="add-customer" class="content-section">
                <h2>Add New Customer</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="customer_email" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="customer_phone" required>
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="customer_city" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="customer_address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Postal Code</label>
                            <input type="text" name="customer_postal" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="add_customer" class="btn">Add Customer</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div id="product-details-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h2 id="modal-product-name"></h2>
            <div class="form-row">
                <div>
                    <p><strong>Price:</strong> Rs <span id="modal-product-price"></span></p>
                    <p><strong>Category:</strong> <span id="modal-product-category"></span></p>
                    <p><strong>Status:</strong> <span id="modal-product-status"></span></p>
                    <p><strong>Total Stock:</strong> <span id="modal-product-stock"></span></p>
                </div>
                <div>
                    <img id="modal-product-image" src="" alt="Product Image" style="max-width: 200px; max-height: 200px;">
                </div>
            </div>
            
            <h3>Description</h3>
            <p id="modal-product-description"></p>
            
            <h3>Color Options</h3>
            <div class="chips-container" id="modal-color-chips">
                <!-- Color chips will be added here -->
            </div>
            
            <h3>Size Options</h3>
            <div class="chips-container" id="modal-size-chips">
                <!-- Size chips will be added here -->
            </div>
        </div>
    </div>

    <script>
        // Navigation
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');
            
            // Add active class to clicked nav item
            event.target.classList.add('active');
            
            // Load data for the section
            loadSectionData(sectionId);
        }

        // Load data for sections
        function loadSectionData(sectionId) {
            switch(sectionId) {
                case 'dashboard':
                    loadStats();
                    break;
                case 'products':
                    loadProducts();
                    break;
                case 'orders':
                    loadOrders();
                    break;
                case 'customers':
                    loadCustomers();
                    break;
                case 'add-product':
                    initOptions();
                    break;
            }
        }

        // Load statistics
        function loadStats() {
            fetch('?action=get_stats')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('stats-grid').innerHTML = `
                        <div class="stat-card">
                            <div class="stat-number">${data.total_products}</div>
                            <div class="stat-label">Total Products</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">${data.total_orders}</div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">${data.total_customers}</div>
                            <div class="stat-label">Total Customers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">Rs ${parseFloat(data.total_revenue).toLocaleString()}</div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">${data.today_orders}</div>
                            <div class="stat-label">Today's Orders</div>
                        </div>
                    `;
                });
        }

        // Load products
        function loadProducts() {
            fetch('?action=get_products')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#products-table tbody');
                    tbody.innerHTML = data.map(product => `
                        <tr>
                            <td>${product.id}</td>
                            <td>${product.name}</td>
                            <td>Rs ${parseFloat(product.price).toLocaleString()}</td>
                            <td>${product.category}</td>
                            <td>${(product.color_stock || 0) + (product.size_stock || 0)}</td>
                            <td>
                                <button class="btn-sm" onclick="viewProductColors(${product.id})">View Colors</button>
                            </td>
                            <td>
                                <button class="btn-sm" onclick="viewProductSizes(${product.id})">View Sizes</button>
                            </td>
                            <td><span class="status ${product.status}">${product.status}</span></td>
                            <td>${new Date(product.created_at).toLocaleDateString()}</td>
                            <td>
                                <button class="btn-sm" onclick="viewProductDetails(${product.id})">Details</button>
                            </td>
                        </tr>
                    `).join('');
                });
        }

        // View product details
        function viewProductDetails(productId) {
            fetch('?action=get_products')
                .then(response => response.json())
                .then(products => {
                    const product = products.find(p => p.id == productId);
                    if (product) {
                        document.getElementById('modal-product-name').textContent = product.name;
                        document.getElementById('modal-product-price').textContent = product.price;
                        document.getElementById('modal-product-category').textContent = product.category;
                        document.getElementById('modal-product-status').textContent = product.status;
                        document.getElementById('modal-product-stock').textContent = (product.color_stock || 0) + (product.size_stock || 0);
                        document.getElementById('modal-product-description').textContent = product.description;
                        document.getElementById('modal-product-image').src = product.image_url;
                        
                        // Load color options
                        fetch(`?action=get_product_colors&product_id=${productId}`)
                            .then(response => response.json())
                            .then(colors => {
                                const colorChips = document.getElementById('modal-color-chips');
                                colorChips.innerHTML = colors.map(color => `
                                    <div class="color-chip">
                                        <div class="color-chip-swatch" style="background-color: ${color.color_code};"></div>
                                        ${color.color_name} (${color.stock_quantity})
                                    </div>
                                `).join('');
                            });
                        
                        // Load size options
                        fetch(`?action=get_product_sizes&product_id=${productId}`)
                            .then(response => response.json())
                            .then(sizes => {
                                const sizeChips = document.getElementById('modal-size-chips');
                                sizeChips.innerHTML = sizes.map(size => `
                                    <div class="size-chip">
                                        ${size.size_name} (${size.stock_quantity})
                                    </div>
                                `).join('');
                            });
                        
                        // Show modal
                        document.getElementById('product-details-modal').style.display = 'block';
                    }
                });
        }

        // View product colors
        function viewProductColors(productId) {
            fetch(`?action=get_product_colors&product_id=${productId}`)
                .then(response => response.json())
                .then(colors => {
                    alert(`Color Options:\n\n${colors.map(c => `${c.color_name} (${c.stock_quantity} in stock)`).join('\n')}`);
                });
        }

        // View product sizes
        function viewProductSizes(productId) {
            fetch(`?action=get_product_sizes&product_id=${productId}`)
                .then(response => response.json())
                .then(sizes => {
                    alert(`Size Options:\n\n${sizes.map(s => `${s.size_name} (${s.stock_quantity} in stock)`).join('\n')}`);
                });
        }

        // Close modal
        function closeModal() {
            document.getElementById('product-details-modal').style.display = 'none';
        }

        // Load orders
        function loadOrders() {
            fetch('?action=get_orders')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#orders-table tbody');
                    tbody.innerHTML = data.map(order => `
                        <tr>
                            <td>#${order.id}</td>
                            <td>${order.customer_name || 'Guest'}</td>
                            <td>Rs ${parseFloat(order.total_amount).toLocaleString()}</td>
                            <td><span class="status ${order.status}">${order.status}</span></td>
                            <td>${new Date(order.created_at).toLocaleDateString()}</td>
                            <td>
                                <select onchange="updateOrderStatus(${order.id}, this.value)" class="btn-sm">
                                    <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                    <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </td>
                        </tr>
                    `).join('');
                });
        }

        // Load customers
        function loadCustomers() {
            fetch('?action=get_customers')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#customers-table tbody');
                    tbody.innerHTML = data.map(customer => `
                        <tr>
                            <td>${customer.id}</td>
                            <td>${customer.name}</td>
                            <td>${customer.email}</td>
                            <td>${customer.phone || 'N/A'}</td>
                            <td>${customer.city || 'N/A'}</td>
                            <td>${new Date(customer.created_at).toLocaleDateString()}</td>
                        </tr>
                    `).join('');
                });
        }

        // Update order status
        function updateOrderStatus(orderId, newStatus) {
            const formData = new FormData();
            formData.append('update_order_status', '1');
            formData.append('order_id', orderId);
            formData.append('new_status', newStatus);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(() => {
                loadOrders(); // Reload orders
            });
        }

        // Options management
        function initOptions() {
            initColorOptions();
            initSizeOptions();
        }

        // Color options management
        function initColorOptions() {
            const container = document.getElementById('color-options-container');
            container.innerHTML = '';
            updateColorOptionsInput();
        }

        function addColorOption() {
            const container = document.getElementById('color-options-container');
            const colorId = Date.now(); // Unique ID for each color option
            
            const colorOption = document.createElement('div');
            colorOption.className = 'option-item';
            colorOption.innerHTML = `
                <div class="color-preview" id="color-preview-${colorId}"></div>
                <div class="option-form-row">
                    <input type="text" placeholder="Color Name" oninput="updateColorOptionsInput()">
                    <input type="color" oninput="updateColorPreview('${colorId}', this.value); updateColorOptionsInput()">
                    <input type="number" placeholder="Stock" min="0" oninput="updateColorOptionsInput()">
                    <input type="text" placeholder="Image URL" oninput="updateColorOptionsInput()">
                </div>
                <button type="button" class="remove-option-btn" onclick="removeOption(this, 'color');">×</button>
            `;
            
            container.appendChild(colorOption);
            updateColorOptionsInput();
        }

        function updateColorPreview(colorId, colorCode) {
            const preview = document.getElementById(`color-preview-${colorId}`);
            if (preview) {
                preview.style.backgroundColor = colorCode;
            }
        }

        function updateColorOptionsInput() {
            const container = document.getElementById('color-options-container');
            const colorOptions = [];
            
            container.querySelectorAll('.option-item').forEach(option => {
                const inputs = option.querySelectorAll('input');
                colorOptions.push({
                    name: inputs[0].value,
                    code: inputs[1].value,
                    stock: inputs[2].value,
                    image: inputs[3].value
                });
            });
            
            document.getElementById('color-options-input').value = JSON.stringify(colorOptions);
        }

        // Size options management
        function initSizeOptions() {
            const container = document.getElementById('size-options-container');
            container.innerHTML = '';
            updateSizeOptionsInput();
        }

        function addSizeOption() {
            fetch('?action=get_available_sizes')
                .then(response => response.json())
                .then(sizes => {
                    const container = document.getElementById('size-options-container');
                    const sizeId = Date.now(); // Unique ID for each size option
                    
                    const sizeOption = document.createElement('div');
                    sizeOption.className = 'option-item';
                    
                    // Create select element for sizes
                    const sizeSelect = document.createElement('select');
                    sizeSelect.oninput = updateSizeOptionsInput;
                    sizes.forEach(size => {
                        const option = document.createElement('option');
                        option.value = size.name;
                        option.textContent = size.name;
                        sizeSelect.appendChild(option);
                    });
                    
                    sizeOption.innerHTML = `
                        <div class="option-form-row">
                            <div style="min-width: 100px;">
                                <select oninput="updateSizeOptionsInput()">
                                    ${sizes.map(size => `<option value="${size.name}">${size.name}</option>`).join('')}
                                </select>
                            </div>
                            <input type="number" placeholder="Stock" min="0" oninput="updateSizeOptionsInput()">
                        </div>
                        <button type="button" class="remove-option-btn" onclick="removeOption(this, 'size');">×</button>
                    `;
                    
                    container.appendChild(sizeOption);
                    updateSizeOptionsInput();
                });
        }

        function updateSizeOptionsInput() {
            const container = document.getElementById('size-options-container');
            const sizeOptions = [];
            
            container.querySelectorAll('.option-item').forEach(option => {
                const inputs = option.querySelectorAll('select, input');
                sizeOptions.push({
                    name: inputs[0].value,
                    stock: inputs[1].value
                });
            });
            
            document.getElementById('size-options-input').value = JSON.stringify(sizeOptions);
        }

        // Remove option (color or size)
        function removeOption(button, type) {
            button.parentElement.remove();
            if (type === 'color') {
                updateColorOptionsInput();
            } else {
                updateSizeOptionsInput();
            }
        }

        // Initialize form submission
        document.getElementById('product-form').addEventListener('submit', function(e) {
            // Make sure options are updated before submission
            updateColorOptionsInput();
            updateSizeOptionsInput();
            
            // Validate at least one color option
            const colorOptions = JSON.parse(document.getElementById('color-options-input').value || '[]');
            const sizeOptions = JSON.parse(document.getElementById('size-options-input').value || '[]');
            
            if (colorOptions.length === 0 && sizeOptions.length === 0) {
                e.preventDefault();
                alert('Please add at least one color or size option for the product.');
                return;
            }
            
            // Validate each color option has required fields
            for (const color of colorOptions) {
                if (!color.name || !color.code || color.stock === '') {
                    e.preventDefault();
                    alert('Please fill in all fields for each color option.');
                    return;
                }
            }
            
            // Validate each size option has required fields
            for (const size of sizeOptions) {
                if (!size.name || size.stock === '') {
                    e.preventDefault();
                    alert('Please fill in all fields for each size option.');
                    return;
                }
            }
        });

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            
            // Close modal when clicking outside
            window.onclick = function(event) {
                const modal = document.getElementById('product-details-modal');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        });
    </script>

<?php endif; ?>

</body>
</html>