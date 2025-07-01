<?php
// Database connection and functions
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "allura_estrella";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get all categories
function getCategories($conn) {
    $sql = "SELECT id, name, description FROM categories";
    $result = $conn->query($sql);
    
    $categories = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

// Function to get products by category name
function getProductsByCategory($conn, $categoryName) {
    $sql = "SELECT * FROM products WHERE category = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoryName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    return $products;
}

$categories = getCategories($conn);

// Get category from URL if specified
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$products = $selectedCategory ? getProductsByCategory($conn, $selectedCategory) : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allura Estrella</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f7;
        }
        
        /* Navigation Styles */
        .navbar {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Logo Styles */
        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        
        .logo-text {
            color: white;
            font-size: 24px;
            font-weight: 700;
            position: relative;
        }
        
        .logo-text::before {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: white;
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }
        
        .logo:hover .logo-text::before {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        .logo-highlight {
            color: #ffd700;
            font-weight: 800;
            text-shadow: 0 0 5px rgba(255, 215, 0, 0.3);
        }
        
        .logo-icon {
            margin-right: 10px;
            font-size: 28px;
            color: #ffd700;
            text-shadow: 0 0 5px rgba(255, 215, 0, 0.3);
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            align-items: center;
        }
        
        .nav-item {
            position: relative;
            margin-left: 25px;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 0;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            opacity: 0.8;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            min-width: 200px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .nav-item:hover .dropdown {
            opacity: 1;
            visibility: visible;
        }
        
        .dropdown-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .dropdown-link {
            color: #333;
            text-decoration: none;
            display: block;
            transition: all 0.2s ease;
        }
        
        .dropdown-link:hover {
            color: #6a11cb;
            padding-left: 5px;
        }
        
        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            margin-left: 20px;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-left: 10px;
        }
        
        .btn-login {
            background-color: transparent;
            border: 1px solid white;
            color: white;
        }
        
        .btn-login:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .btn-signup {
            background-color: white;
            color: #6a11cb;
            border: 1px solid white;
        }
        
        .btn-signup:hover {
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        /* Mobile Menu */
        .menu-toggle {
            display: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        /* Product Grid */
        .products-container {
            padding: 40px 0;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .product-image-container {
            height: 220px;
            overflow: hidden;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.05);
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #6a11cb;
            margin-top: 10px;
        }
        
        .category-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .category-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .category-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 3px;
        }
        
        .category-description {
            font-size: 16px;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .nav-links {
                position: fixed;
                top: 0;
                left: -100%;
                width: 80%;
                max-width: 300px;
                height: 100vh;
                background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
                flex-direction: column;
                align-items: flex-start;
                padding: 30px;
                transition: all 0.5s ease;
                z-index: 1000;
            }
            
            .nav-links.active {
                left: 0;
            }
            
            .nav-item {
                margin: 15px 0;
            }
            
            .auth-buttons {
                flex-direction: column;
                margin-left: 0;
                width: 100%;
            }
            
            .btn {
                margin: 5px 0;
                width: 100%;
                text-align: center;
            }
            
            .dropdown {
                position: static;
                opacity: 1;
                visibility: visible;
                display: none;
                margin-top: 10px;
                background-color: rgba(255, 255, 255, 0.1);
                box-shadow: none;
            }
            
            .nav-item:hover .dropdown {
                display: block;
            }
            
            .dropdown-item {
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            .dropdown-link {
                color: white;
            }
            
            .dropdown-link:hover {
                color: white;
                opacity: 0.8;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .category-title {
                font-size: 28px;
            }
        }
        
        /* Main Content */
        .main-content {
            padding: 40px 0;
            text-align: center;
        }
        
        .welcome-message h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 36px;
        }
        
        .welcome-message p {
            color: #666;
            max-width: 800px;
            margin: 0 auto 30px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <span class="logo-icon">★</span>
                <span class="logo-text">Allura <span class="logo-highlight">Estrella</span></span>
            </a>
            <div class="menu-toggle" id="mobile-menu">☰</div>
            <ul class="nav-links" id="nav-links">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
                <?php foreach ($categories as $category): ?>
                <li class="nav-item">
                    <a href="index.php?category=<?= urlencode($category['name']) ?>" class="nav-link">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                    <ul class="dropdown">
                        <li class="dropdown-item">
                            <a href="index.php?category=<?= urlencode($category['name']) ?>" class="dropdown-link">
                                <?= htmlspecialchars($category['description']) ?>
                            </a>
                        </li>
                        <li class="dropdown-item">
                            <a href="index.php?category=<?= urlencode($category['name']) ?>" class="dropdown-link">
                                View All <?= htmlspecialchars($category['name']) ?>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endforeach; ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-login">Login</a>
                    <a href="signup.php" class="btn btn-signup">Sign Up</a>
                </div>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <?php if ($selectedCategory): ?>
                <div class="products-container">
                    <?php 
                        $currentCategory = null;
                        foreach ($categories as $cat) {
                            if ($cat['name'] == $selectedCategory) {
                                $currentCategory = $cat;
                                break;
                            }
                        }
                    ?>
                    <div class="category-header">
                        <h1 class="category-title"><?= htmlspecialchars($selectedCategory) ?></h1>
                        <?php if ($currentCategory && $currentCategory['description']): ?>
                            <p class="category-description"><?= htmlspecialchars($currentCategory['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($products)): ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
                                <div class="product-card">
                                    <div class="product-image-container">
                                        <?php if ($product['image_url']): ?>
                                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/400x300?text=No+Image" alt="Placeholder" class="product-image">
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                        <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; font-size: 18px; color: #666;">No products found in this category.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="welcome-message">
                    <h1>Welcome to Allura Estrella</h1>
                    <p>Discover our exquisite collection by browsing through the categories above. Each category offers unique, handcrafted items that reflect elegance and style.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('nav-links').classList.remove('active');
            });
        });
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>