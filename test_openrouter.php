<?php
// test_openrouter.php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;

$apiKey = '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';
$apiUrl = 'https://openrouter.ai/api/v1';
$model = 'deepseek/deepseek-r1:free';

echo "🧪 اختبار الاتصال مع OpenRouter...\n\n";

try {
    $client = new Client([
        'timeout' => 30,
        'verify' => false, // تجربة بدون SSL verification
    ]);

    $response = $client->post($apiUrl . '/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => 'http://localhost:8000',
            'X-Title' => 'HR Management System'
        ],
        'json' => [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'مرحبا، قل لي "مرحبا" بالعربية'
                ]
            ],
            'max_tokens' => 100,
            'temperature' => 0.7
        ]
    ]);

    $statusCode = $response->getStatusCode();
    $body = json_decode($response->getBody(), true);

    echo "✅ نجح الاتصال!\n";
    echo "📊 Status Code: $statusCode\n";
    echo "💬 Response: " . ($body['choices'][0]['message']['content'] ?? 'لا توجد رسالة') . "\n";
    echo "🔧 Model Used: " . ($body['model'] ?? 'غير محدد') . "\n";

} catch (Exception $e) {
    echo "❌ فشل الاتصال:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    
    if (method_exists($e, 'getResponse') && $e->getResponse()) {
        echo "Response Body: " . $e->getResponse()->getBody() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🔍 معلومات إضافية:\n";
echo "API Key: " . substr($apiKey, 0, 20) . "...\n";
echo "Model: $model\n";
echo "URL: $apiUrl\n";
echo "PHP Version: " . phpversion() . "\n";
echo "cURL Version: " . (function_exists('curl_version') ? curl_version()['version'] : 'غير مثبت') . "\n";
?>