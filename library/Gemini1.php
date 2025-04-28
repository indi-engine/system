<?php
class Gemini1 {

    public $baseUrl = "https://generativelanguage.googleapis.com";
    public $key = 'AIzaSyDzfM5JBowqj_eq7dHI1N30a9-ENn32we8';
    public $model = 'models/gemini-2.0-flash-001';

    public function upload($file) {

        // Get file name (with extension)
        $name = basename($file);

        // Check file exists
        if (file_get_contents($file) === false) {
            die("Failed to read file: $file\n");
        }

        // Get mime type and size
        $mime = Indi::mime($name);
        $size = filesize($file);

        echo "MIME_TYPE: " . $mime . "\n";
        echo "NUM_BYTES: " . $size . "\n";

        // --- 3. Initiate Resumable Upload (Start) ---
        $ch = curl_init("$this->baseUrl/upload/v1beta/files?key=$this->key");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['file' => ['display_name' => $name]]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Goog-Upload-Protocol: resumable",
            "X-Goog-Upload-Command: start",
            "X-Goog-Upload-Header-Content-Length: $size",
            "X-Goog-Upload-Header-Content-Type: $mime",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        echo "Initiating resumable upload...\n";
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);
        curl_close($ch);

        // Extract upload URL from headers
        $uploadUrl = null;
        $headers = explode("\r\n", $responseHeaders);
        foreach ($headers as $header) {
            if (stripos($header, "x-goog-upload-url:") === 0) {
                $uploadUrl = trim(substr($header, strlen("x-goog-upload-url:")));
                break;
            }
        }

        if ($uploadUrl === null) {
            die("Failed to get upload URL from response headers.\nResponse Headers:\n" . $responseHeaders . "\n");
        }
        echo "Obtained upload_url: " . $uploadUrl . "\n";

        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Length: $size",
            "X-Goog-Upload-Offset: 0",
            "X-Goog-Upload-Command: upload, finalize",
            "Content-Type: $mime"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        echo "Uploading file bytes...\n";
        $fileInfoJson = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            die("File upload failed with HTTP code: " . $httpCode . "\nResponse:\n" . $fileInfoJson . "\n");
        }

        // Parse file info JSON
        $fileInfo = json_decode($fileInfoJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            die("Failed to parse file info JSON: " . json_last_error_msg() . "\nResponse:\n" . $fileInfoJson . "\n");
        }

        $fileUri = $fileInfo['file']['uri'] ?? null;
        if ($fileUri === null) {
            die("Failed to get file URI from file info response.\nResponse:\n" . $fileInfoJson . "\n");
        }
        return (object) $fileInfo['file'];
    }

    public function files(string $file = null) {
        $name = basename($file);
        $resp = file_get_contents("https://generativelanguage.googleapis.com/v1beta/files?key=$this->key");
        $files = json_decode($resp)->files ?? [];
        return $file ? array_combine(array_column($files, 'displayName'), $files)[$name] : $files;
    }

    public function deleteFile(string $name) {

        // --- Construct the DELETE request URL ---
        $deleteUrl = "$this->baseUrl/v1beta/files/" . $name . "?key=" . $this->key;

        // --- Initialize cURL session ---
        $ch = curl_init($deleteUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Specify the DELETE method
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        echo "Attempting to delete file: " . $name . "\n";
        $response = curl_exec($ch);

        // --- Check for cURL errors ---
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch) . "\n";
        }

        // --- Get HTTP status code ---
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // --- Close cURL session ---
        curl_close($ch);

        // --- Process the response ---
        echo "HTTP Status Code: " . $httpCode . "\n";
        echo "Response Body: " . $response . "\n";

        // Check if the deletion was successful (HTTP status 200 OK indicates success)
        if ($httpCode === 200) {
            echo "File '" . $name . "' deleted successfully.\n";
        } else {
            echo "Failed to delete file '" . $name . "'.\n";
            // You might want to add more specific error handling based on the response body
        }
    }

    /**
     * @param $file
     * @return array|mixed|object
     */
    public function uploadIfNeed(string $file) {
        return $this->files($file) ?? $this->upload($file);
    }

    public function prompt($prompt) {

        // Get Indi Engine docs uri to be further mentioned in prompt
        $docs = $this->uploadIfNeed("data/gemini/Indi Engine - docs.pdf")->uri;

        // Prepare full prompt
        $prompt = file_get_contents('data/gemini/prompt.md') . "\n$prompt";

        // Get response
        msg('Waiting for response from GPT...');
        $resp = $this->ask($docs, $prompt);

        i($resp);

        // Exctract php code
        if (!$code = between('~```php\s(|\n<\?php|)~', '~(\?>\n|)```~', $resp)) {
            jtextarea(false, "No php code detected in: $resp");
        }

        // Return
        return $code[0];
    }

    public function ask(string $fileUri, string $systemInstruction) {

        $ch = curl_init("$this->baseUrl/v1beta/$this->model:generateContent?key=$this->key");
        $mime = 'application/pdf';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["contents" => [[
            "parts" => [
                ["text" => $systemInstruction],
                ["file_data" => [
                    "mime_type" => $mime,
                    "file_uri" => $fileUri
                ]]
            ],
            "role" => "user"
        ]]]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $cacheJson = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 201) { // Expect 200 or 201 for creation
            jflush(false, "Request failed with HTTP code: $httpCode\nResponse:\n$cacheJson\n");
        }

        // Parse cache JSON
        $cacheInfo = json_decode($cacheJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            jflush(false, "Failed to parse response JSON: " . json_last_error_msg() . "\nResponse:\n$cacheJson\n");
        }

        if (!$resp = $cacheInfo['candidates'][0]['content']['parts'][0]['text'] ?? 0) {
            jtextarea(false, $cacheJson);
        }
        return $resp;
    }
}