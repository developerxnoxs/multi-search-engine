<?php
require __DIR__ . '/../vendor/autoload.php';

$scraperApiKey = '1820c54a47ebf6d3557d9be57aa70c81';
$query = 'PHP tutorial';

echo "=== DEBUG BRAVE SEARCH ===\n\n";

echo "--- Test 1: Tanpa Proxy (Direct Request) ---\n";
$url = "https://search.brave.com/search?" . http_build_query([
    'q' => $query,
    'offset' => 0,
    'count' => 5,
    'lang' => 'en'
]);

echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
    ],
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => ''
]);

$html1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error1 = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode1\n";
if ($error1) echo "CURL Error: $error1\n";
echo "Response Length: " . strlen($html1) . " bytes\n";

file_put_contents('/tmp/brave_direct.html', $html1);
echo "HTML saved to /tmp/brave_direct.html\n";

if (preg_match('/captcha/i', $html1)) {
    echo "CAPTCHA DETECTED in response!\n";
}
if (preg_match('/verify.*human/i', $html1)) {
    echo "HUMAN VERIFICATION DETECTED!\n";
}

echo "\nFirst 2000 chars of HTML:\n";
echo substr($html1, 0, 2000) . "\n";

echo "\n\n--- Test 2: Dengan ScraperAPI Proxy ---\n";
$scraperUrl = 'http://api.scraperapi.com?' . http_build_query([
    'api_key' => $scraperApiKey,
    'url' => $url,
    'render' => 'false'
]);

echo "ScraperAPI URL: " . substr($scraperUrl, 0, 100) . "...\n\n";

$ch2 = curl_init();
curl_setopt_array($ch2, [
    CURLOPT_URL => $scraperUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 90,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => ''
]);

$html2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch2);
curl_close($ch2);

echo "HTTP Code: $httpCode2\n";
if ($error2) echo "CURL Error: $error2\n";
echo "Response Length: " . strlen($html2) . " bytes\n";

file_put_contents('/tmp/brave_scraperapi.html', $html2);
echo "HTML saved to /tmp/brave_scraperapi.html\n";

if (preg_match('/captcha/i', $html2)) {
    echo "CAPTCHA DETECTED in response!\n";
}
if (preg_match('/verify.*human/i', $html2)) {
    echo "HUMAN VERIFICATION DETECTED!\n";
}

echo "\nFirst 2000 chars of HTML:\n";
echo substr($html2, 0, 2000) . "\n";

echo "\n\n--- Test 3: ScraperAPI dengan render=true ---\n";
$scraperUrlRender = 'http://api.scraperapi.com?' . http_build_query([
    'api_key' => $scraperApiKey,
    'url' => $url,
    'render' => 'true'
]);

$ch3 = curl_init();
curl_setopt_array($ch3, [
    CURLOPT_URL => $scraperUrlRender,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 120,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING => ''
]);

$html3 = curl_exec($ch3);
$httpCode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
$error3 = curl_error($ch3);
curl_close($ch3);

echo "HTTP Code: $httpCode3\n";
if ($error3) echo "CURL Error: $error3\n";
echo "Response Length: " . strlen($html3) . " bytes\n";

file_put_contents('/tmp/brave_scraperapi_render.html', $html3);
echo "HTML saved to /tmp/brave_scraperapi_render.html\n";

if (preg_match('/captcha/i', $html3)) {
    echo "CAPTCHA DETECTED in response!\n";
}
if (preg_match('/verify.*human/i', $html3)) {
    echo "HUMAN VERIFICATION DETECTED!\n";
}
if (preg_match('/snippet/i', $html3)) {
    echo "SNIPPET CLASS FOUND - Results might be available!\n";
}

echo "\nFirst 2000 chars of HTML:\n";
echo substr($html3, 0, 2000) . "\n";

echo "\n=== DEBUG SELESAI ===\n";
