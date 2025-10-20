<?php
session_start();
require_once '../../database/starroofing_db.php';

if (!isset($_GET['product_id'])) {
    die('Product not specified.');
}

$product_id = intval($_GET['product_id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die('Product not found.');
}
// for 3d models
$modelPathFromDb = $product['model_path'] ?? null;

$fullModelPath = $_SERVER['DOCUMENT_ROOT'] . '/STARROOFING/' . ltrim($modelPathFromDb, '/');

if ($modelPathFromDb && file_exists($fullModelPath) && is_file($fullModelPath)) {
    $modelPath = '/STARROOFING/' . ltrim($modelPathFromDb, '/');
} else {
    $modelPath = null;
}

$imagePathFromDb = $product['image_path'] ?? 'images/no-image.png';

// for images
$imagePath = '/STARROOFING/' . ltrim($imagePathFromDb, '/');


// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = max(1, intval($_POST['quantity']));
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    echo "<script>
        alert('Added to cart successfully!');
        window.location.href='../materials.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f5f7f9;
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .back {
            display: inline-block;
            text-decoration: none;
            color: #1a365d;
            font-weight: 600;
            padding: 16px;
        }

        .content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .product-details {
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            max-width: 800px;
            margin: 0 auto 100px auto;
            box-shadow: 0 2px 8px rgba(26,54,93,0.1);
        }

        .product-details img {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .product-details h1 {
            font-size: 1.8rem;
            color: #1a365d;
            margin-bottom: 10px;
        }

        .price {
            color: #e9b949;
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 12px;
        }

        .description {
            margin-top: 20px;
            line-height: 1.6;
            color: #333;
        }

        /* Footer (fixed button bar) */
        .bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 15px 10px;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
        }

        .bottom-bar button {
            flex: 1;
            margin: 0 5px;
            background: #1a365d;
            color: #fff;
            padding: 12px 0;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .bottom-bar button.chat {
            background: #e9b949;
            color: #1a365d;
        }

        .bottom-bar button:hover {
            opacity: 0.9;
        }

        .quantity {
            margin: 15px 0;
        }

        .quantity input {
            width: 70px;
            padding: 6px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .product-details {
                padding: 16px;
            }

            .bottom-bar {
                flex-direction: column;
                gap: 10px;
            }

            .bottom-bar button {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <a href="../materials.php" class="back"><i class="fa fa-arrow-left"></i> Back</a>

    <div class="content">
        <div class="product-details">
            <?php if ($modelPath): ?>
                <model-viewer 
                    id="modelViewer"
                    src="<?= htmlspecialchars($modelPath) ?>"
                    alt="3D model"
                    auto-rotate
                    camera-controls
                    ar
                    style="width: 100%; height: 400px; background: #f5f5f5; border-radius: 10px;">
                </model-viewer>

                <div style="margin-top: 20px;">
                    <label for="colorPicker">Select Color:</label>
                    <input type="color" id="colorPicker" value="#ffffff">

                    <label for="sizeSlider" style="margin-left: 20px;">Size:</label>
                    <input type="range" id="sizeSlider" min="0.5" max="2" step="0.1" value="1">
                </div>
            <?php else: ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php endif; ?>

            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="price">₱<?= number_format($product['price'], 2) ?></p>
          
            <div class="quantity">
                <form method="post" id="cartForm">
                    <label>Quantity: </label>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                    <input type="hidden" name="add_to_cart" value="1">
                </form>
            </div>

            <div class="description">
                <h3>Description</h3>
                <p><?= nl2br(htmlspecialchars($product['description'] ?: 'No description available.')) ?></p>
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Buttons -->
    <div class="bottom-bar">
        <button class="chat" onclick="openChat(<?= $product['product_id'] ?>)">
            <i class="fa fa-comments"></i> Chat / Inquire
        </button>
        <button type="submit" form="cartForm">
            <i class="fa fa-cart-plus"></i> Add to Cart
        </button>
    </div>

<script>
    function openChat(productId) {
        window.location.href = 'inquiry.php?product_id=' + productId;
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewer = document.getElementById('modelViewer');
    if (!viewer) return; // If no model-viewer on page, skip

    const colorPicker = document.getElementById('colorPicker');
    const sizeSlider = document.getElementById('sizeSlider');

    // Wait for model to load before accessing materials
    viewer.addEventListener('load', () => {
        const materials = viewer.model?.materials;
        if (!materials || materials.length === 0) {
            console.warn("No editable materials found in model.");
            return;
        }

        // Handle color change
        colorPicker.addEventListener('input', () => {
            const color = colorPicker.value;
            const rgb = [
                parseInt(color.slice(1, 3), 16) / 255,
                parseInt(color.slice(3, 5), 16) / 255,
                parseInt(color.slice(5, 7), 16) / 255,
                1.0 // Alpha
            ];

            materials.forEach(material => {
                material.pbrMetallicRoughness.setBaseColorFactor(rgb);
            });
        });
    });

    // Handle scale change
    sizeSlider.addEventListener('input', () => {
        const scale = parseFloat(sizeSlider.value);
        viewer.scale = `${scale} ${scale} ${scale}`;
    });
});
</script>

</body>
</html>
