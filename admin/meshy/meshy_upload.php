<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';
use Dotenv\Dotenv;

// Load API key
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$apiKey = $_ENV['MESHY_KEY'];

if (!$apiKey) {
    echo json_encode(['status' => 'error', 'message' => 'API key not set']);
    exit;
}


// Ensure images were uploaded
if (empty($_FILES['images'])) {
    echo json_encode(['status' => 'error', 'message' => 'No images uploaded']);
    exit;
}

// Prepare files for upload
$curlFiles = [];
foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
    $curlFiles[] = new CURLFile($tmpName, mime_content_type($tmpName), $_FILES['images']['name'][$i]);
}

// POST to Meshy
$url = "https://api.meshy.ai/v1/image-to-3d";
$ch = curl_init($url);

$postFields = ['mode' => 'preview'];
foreach($curlFiles as $i => $file){
    $postFields["files[$i]"] = $file;
}

curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $apiKey"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['status' => 'error', 'message' => $error]);
    exit;
}

$data = json_decode($response, true);

if (isset($data['result_url'])) {
    echo json_encode(['status' => 'success', 'model_url' => $data['result_url']]);
} elseif (isset($data['task_id'])) {
    $resultUrl = pollMeshyTask($data['task_id'], $apiKey);
    if($resultUrl){
        echo json_encode(['status'=>'success','model_url'=>$resultUrl]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Task timed out']);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unexpected response from Meshy API.',
        'raw_response' => $response
    ]);
}

function pollMeshyTask($taskId, $apiKey){
    $statusUrl = "https://api.meshy.ai/v1/tasks/$taskId";
    for($i=0;$i<20;$i++){
        sleep(5);
        $ch = curl_init($statusUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $apiKey"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response,true);
        if(!empty($data['result_url'])) return $data['result_url'];
    }
    return null;
}
?>
