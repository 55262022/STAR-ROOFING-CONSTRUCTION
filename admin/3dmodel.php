<?php
session_start();
require_once '../database/starroofing_db.php';

if (!isset($_GET['product_id'])) {
    die('Product not specified.');
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$meshyApiKey = $_ENV['MESHY_KEY'];

$product_id = intval($_GET['product_id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die('Product not found.');
}

// For 3D model
$modelPathFromDb = $product['model_path'] ?? null;
$fullModelPath = $_SERVER['DOCUMENT_ROOT'] . '/STARROOFING/' . ltrim($modelPathFromDb, '/');
$modelPath = ($modelPathFromDb && file_exists($fullModelPath)) ? '/STARROOFING/' . ltrim($modelPathFromDb, '/') : null;

// For image path
$imagePathFromDb = $product['image_path'] ?? 'images/no-image.png';
$imagePath = '/STARROOFING/' . ltrim($imagePathFromDb, '/');
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

        /* Bottom Button */
        .bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px 10px;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
        }

        .bottom-bar button {
            background: #1a365d;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bottom-bar button:hover {
            background: #152b4a;
        }

        input[type="file"] {
            display: none;
        }

        @media (max-width: 600px) {
            .bottom-bar button {
                width: 90%;
            }
        }
    </style>
</head>
<body>

<a href="inventory.php" class="back"><i class="fa fa-arrow-left"></i> Back</a>

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
        <?php else: ?>
            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <?php endif; ?>

        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <p class="price">₱<?= number_format($product['price'], 2) ?></p>

        <div class="description">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($product['description'] ?: 'No description available.')) ?></p>
        </div>
    </div>
</div>

<!-- Bottom Button -->
<div class="bottom-bar">
    <button onclick="document.getElementById('imageInput').click()">
        <i class="fa fa-cubes"></i> Create 3D Model
    </button>
    <input type="file" id="imageInput" accept="image/*" multiple>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', async function(event) {
    const files = event.target.files;
    if (!files.length) return;

    const MESHY_KEY = <?= json_encode($meshyApiKey) ?>;
    const productName = <?= json_encode($product['name']) ?>;
    const productId = <?= json_encode($product['product_id']) ?>;

    alert("Uploading " + files.length + " image(s) and generating 3D model...");

    // Step 1: Upload images to your server
    const formData = new FormData();
    for (const file of files) {
        formData.append("images[]", file);
    }

    try {
        const uploadResponse = await fetch("upload_images.php", {
            method: "POST",
            body: formData
        });

        const uploadData = await uploadResponse.json();

        if (!uploadData.success || !uploadData.urls) {
            alert("Image upload failed: " + uploadData.message);
            return;
        }

        const imageUrls = uploadData.urls;
        console.log("Uploaded images:", imageUrls);

        // Step 2: Request 3D model generation from Meshy.ai
        const response = await fetch("https://api.meshy.ai/v2/image-to-3d", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${apiKey}`,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                image_urls: imageUrls,
                prompt: `Generate a 3D model of ${productName}`,
                art_style: "realistic"
            })
        });

        const data = await response.json();
        console.log("Meshy response:", data);

        if (data.task_id) {
            alert("3D model generation started! Task ID: " + data.task_id);
            // You can now save data.task_id or model URL to your DB when completed
        } else {
            alert("Failed to start 3D model generation: " + JSON.stringify(data));
        }

    } catch (error) {
        console.error("Error:", error);
        alert("Error generating 3D model: " + error.message);
    }
});
</script>

</body>
</html>
