<?php
class Gemini1 {

    public $baseUrl = "https://generativelanguage.googleapis.com";
    public $key = 'AIzaSyBWSvkRpl9b86bQ1zBtdqIi0RUrn3LgqxA';
    //public $model = 'models/gemini-2.0-flash-001';
    //public $model = 'models/gemini-2.5-pro-exp-03-25';
    //public $model = 'models/gemini-2.5-flash-preview-04-17';
    //public $model = 'models/gemini-2.5-pro-preview-03-25';
    public $model = 'models/gemini-2.0-flash';
    //public $model = 'models/gemini-1.5-pro';
    //public $model = 'models/gemini-1.5-flash-latest';
    //public $model = 'models/gemini-2.0-flash-lite';
    public $files = [];

    public function models() {

    }
    public function upload($file) {

        // Get file name (with extension)
        $name = basename($file);

        // Check file exists
        if (file_get_contents($file) === false) {
            jflush(false, "Failed to read file: $file\n");
        }

        // Get mime type and size
        $mime = Indi::mime($name);
        $size = filesize($file);

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

        //echo "Initiating resumable upload...\n";
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
            jflush(false, "Failed to get upload URL from response headers.\nResponse Headers:\n" . $responseHeaders . "\n");
        }
        //echo "Obtained upload_url: " . $uploadUrl . "\n";

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

        //echo "Uploading file bytes...\n";
        $fileInfoJson = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            jflush(false, "File upload failed with HTTP code: " . $httpCode . "\nResponse:\n" . $fileInfoJson . "\n");
        }

        // Parse file info JSON
        $fileInfo = json_decode($fileInfoJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            jflush(false, "Failed to parse file info JSON: " . json_last_error_msg() . "\nResponse:\n" . $fileInfoJson . "\n");
        }

        $fileUri = $fileInfo['file']['uri'] ?? null;
        if ($fileUri === null) {
            jflush(false, "Failed to get file URI from file info response.\nResponse:\n" . $fileInfoJson . "\n");
        }
        return $this->files[$name] = (object) $fileInfo['file'];
    }

    public function files(string $file = null) {

        if (!$this->files) {

            curl_setopt_array($ch = curl_init(), [
                CURLOPT_URL => "$this->baseUrl/v1beta/files?key=$this->key",
                CURLOPT_RETURNTRANSFER => true,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            //
            if ($code !== 200) {
                jflush(false, "File list retrieval failed with HTTP code: $code.\nResponse:\n$resp\n");
            }

            // Parse file info JSON
            $json = json_decode($resp);
            if (json_last_error() !== JSON_ERROR_NONE) {
                jflush(false, "Failed to parse file list JSON: " . json_last_error_msg() . "\nResponse:\n$resp\n");
            }

            //
            if (!property_exists($json, 'files')) {
                return null;
            }

            $names = array_column($json->files, 'displayName');
            $this->files = array_combine($names, $json->files);
        }

        //
        return $file
            ? $this->files[basename($file)] ?? null
            : $this->files;
    }

    public function deleteFile(string $name) {

        if (!$path = $this->files($name)->name) {
            return;
        }

        // --- Construct the DELETE request URL ---
        $deleteUrl = "$this->baseUrl/v1beta/$path?key=$this->key";

        $ch = curl_init($deleteUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Specify the DELETE method
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            jflush(false, "cURL Error: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        if ($httpCode === 200) {
            return true;
        } else {
            jflush(false,"Failed to delete file '$name'.\n$httpCode: $response");
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

        // Get files uris to be further mentioned in prompt
        $pref = "data/gemini/Indi Engine";
        $doc = $this->uploadIfNeed("$pref - docs.pdf")->uri;
        $gen = $this->uploadIfNeed("$pref - generation rules.md")->uri;
        $eds = $this->uploadIfNeed("$pref - examples - data-structures.md")->uri;
        $edv = $this->uploadIfNeed("$pref - examples - data-views.md")->uri;

        // Prepare full prompt
        $prompt = file_get_contents('data/gemini/prompt.md') . "\n$prompt";

        // Get response
        msg('Waiting for response from GPT...');
        mt();
        $resp = $this->scratch([$doc, $gen, $eds, $edv], $prompt);
        msg(mt());
        i($resp);

        // Exctract php code
        if (!$code = between('~```php\s(|\n<\?php|)~', '~(\?>\n|)```~', $resp)) {
            jtextarea(false, "No php code detected in: $resp");
        }

        // Return
        return $code[0];
    }

    public function scratch(array $uri, string $systemInstruction) {

        $ch = curl_init("$this->baseUrl/v1beta/$this->model:generateContent?key=$this->key");
        $mime = 'application/pdf';
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "contents" => [[
                "parts" => [
                    ["text" => $systemInstruction],
                    ["file_data" => ["mime_type" => 'application/pdf', "file_uri" => $uri[0] ]],
                    ["file_data" => ["mime_type" => 'text/markdown'  , "file_uri" => $uri[1] ]],
                    ["file_data" => ["mime_type" => 'text/markdown'  , "file_uri" => $uri[2] ]],
                    ["file_data" => ["mime_type" => 'text/markdown'  , "file_uri" => $uri[3] ]],
                ],
                "role" => "user"
            ]],
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $cacheJson = curl_exec($ch);

        if (curl_errno($ch)) {
            jflush(false, "cURL Error: " . curl_error($ch));
        }

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