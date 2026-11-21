<?php
/**
 * SQLite Database Connection & Setup
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $this->pdo = new PDO('sqlite:' . DB_PATH);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $this->initSchema();
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo() {
        return $this->pdo;
    }

    private function initSchema() {
        // Users
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                phone TEXT,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'customer' CHECK(role IN ('customer', 'admin')),
                customer_type TEXT DEFAULT 'retail' CHECK(customer_type IN ('retail', 'wholesale')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Categories
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                slug TEXT NOT NULL UNIQUE,
                icon TEXT DEFAULT '🛒'
            )
        ");

        // Products
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER,
                name TEXT NOT NULL,
                description TEXT,
                unit TEXT DEFAULT 'unit',
                retail_price REAL NOT NULL,
                wholesale_price REAL NOT NULL,
                wholesale_min_qty INTEGER DEFAULT 10,
                stock INTEGER DEFAULT 0,
                image TEXT,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )
        ");

        // Orders
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                order_number TEXT UNIQUE,
                customer_name TEXT,
                customer_phone TEXT,
                customer_email TEXT,
                shipping_address TEXT,
                order_type TEXT DEFAULT 'retail' CHECK(order_type IN ('retail', 'wholesale')),
                subtotal REAL NOT NULL,
                total REAL NOT NULL,
                status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled')),
                payment_method TEXT DEFAULT 'cash_on_delivery',
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        // Order Items
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                unit_price REAL NOT NULL,
                total_price REAL NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");

        // Seed if empty
        $count = $this->pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($count == 0) {
            $this->seedData();
        }
    }

    private function seedData() {
        // Admin user (password: admin123)
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $this->pdo->exec("INSERT INTO users (name, email, phone, password, role, customer_type) 
            VALUES ('Store Admin', 'admin@freshprovision.ng', '08012345678', '$adminPass', 'admin', 'wholesale')");

        // Demo customer (password: customer123)
        $custPass = password_hash('customer123', PASSWORD_DEFAULT);
        $this->pdo->exec("INSERT INTO users (name, email, phone, password, role, customer_type) 
            VALUES ('Demo Customer', 'customer@example.com', '08098765432', '$custPass', 'customer', 'retail')");

        // Categories
        $categories = [
            ['Grains & Cereals', 'grains', '🌾'],
            ['Oils & Fats', 'oils', '🫒'],
            ['Canned Goods', 'canned', '🥫'],
            ['Beverages', 'beverages', '🥤'],
            ['Spices & Seasonings', 'spices', '🌶️'],
            ['Household Essentials', 'household', '🧹'],
            ['Dairy & Eggs', 'dairy', '🥛'],
            ['Snacks & Sweets', 'snacks', '🍪'],
        ];
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute($cat);
        }

        // Products (Nigerian provision store style)
        $products = [
            [1, 'Premium Rice (50kg)', 'High quality long grain rice, perfect for wholesale', 'bag', 45000, 42000, 5, 120, 'rice'],
            [1, 'Rice (5kg)', 'Family size premium rice', 'bag', 5500, 5000, 10, 200, 'rice'],
            [1, 'Beans (White) 10kg', 'Quality white beans', 'bag', 12000, 11000, 5, 80, 'beans'],
            [1, 'Garri (Ijebu) 5kg', 'Fine Ijebu garri', 'bag', 3500, 3200, 10, 150, 'garri'],
            [1, 'Semolina 1kg', 'Fine semolina flour', 'pack', 1800, 1600, 20, 300, 'semolina'],
            [1, 'Wheat Flour 2kg', 'All purpose flour', 'pack', 2200, 2000, 15, 180, 'flour'],
            [2, 'Vegetable Oil 25L', 'Refined vegetable oil - wholesale', 'jerry', 38000, 35000, 3, 45, 'oil'],
            [2, 'Vegetable Oil 5L', 'Refined vegetable oil', 'bottle', 8500, 7800, 6, 90, 'oil'],
            [2, 'Palm Oil 5L', 'Pure red palm oil', 'bottle', 7500, 7000, 6, 70, 'palm-oil'],
            [2, 'Groundnut Oil 1L', 'Pure groundnut oil', 'bottle', 2800, 2500, 12, 100, 'oil'],
            [3, 'Sardine (Tin) x50', 'Carton of 50 tins - wholesale', 'carton', 18000, 16500, 3, 40, 'sardine'],
            [3, 'Sardine (Tin)', 'Single tin of sardine in oil', 'tin', 450, 400, 50, 500, 'sardine'],
            [3, 'Tomato Paste 400g x24', 'Carton tomato paste', 'carton', 15000, 13800, 3, 35, 'tomato'],
            [3, 'Baked Beans 400g', 'Heinz style baked beans', 'tin', 850, 750, 24, 200, 'beans-can'],
            [3, 'Corned Beef 340g', 'Premium corned beef', 'tin', 2200, 2000, 12, 150, 'corned-beef'],
            [4, 'Indomie Noodles (Carton)', '40 packs carton', 'carton', 9500, 8800, 5, 60, 'noodles'],
            [4, 'Indomie Noodles (Pack)', 'Chicken flavour', 'pack', 280, 250, 50, 800, 'noodles'],
            [4, 'Peak Milk 400g', 'Full cream milk powder', 'tin', 3200, 3000, 12, 120, 'milk'],
            [4, 'Milo 400g', 'Chocolate malt drink', 'tin', 2800, 2600, 12, 100, 'milo'],
            [4, 'Soft Drinks (Crate)', '24 bottles crate', 'crate', 4500, 4200, 5, 50, 'drinks'],
            [5, 'Maggi Cubes (Box 100)', 'Seasoning cubes wholesale', 'box', 4500, 4200, 5, 80, 'maggi'],
            [5, 'Curry Powder 100g', 'Pure curry powder', 'pack', 600, 550, 20, 200, 'curry'],
            [5, 'Thyme 50g', 'Dried thyme leaves', 'pack', 450, 400, 20, 180, 'thyme'],
            [5, 'Pepper (Ground) 100g', 'Hot ground pepper', 'pack', 500, 450, 20, 150, 'pepper'],
            [6, 'Detergent 1kg', 'Washing powder', 'pack', 1200, 1100, 20, 200, 'detergent'],
            [6, 'Toilet Soap (Pack of 12)', 'Bathing soap carton', 'pack', 2800, 2500, 10, 90, 'soap'],
            [6, 'Tissue Paper (12 rolls)', 'Soft tissue rolls', 'pack', 2200, 2000, 10, 70, 'tissue'],
            [7, 'Eggs (Crate of 30)', 'Fresh farm eggs', 'crate', 3800, 3500, 5, 40, 'eggs'],
            [7, 'Butter 500g', 'Creamy butter', 'pack', 2500, 2300, 10, 60, 'butter'],
            [8, 'Digestive Biscuits', 'Family pack biscuits', 'pack', 1500, 1350, 15, 100, 'biscuits'],
            [8, 'Chin Chin 500g', 'Crunchy chin chin', 'pack', 1200, 1100, 15, 80, 'chinchin'],
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO products (category_id, name, description, unit, retail_price, wholesale_price, wholesale_min_qty, stock, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($products as $p) {
            $stmt->execute($p);
        }
    }
}

function db() {
    return Database::getInstance()->getPdo();
}
