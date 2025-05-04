<?php
class Gemini {

    public $baseUrl = "https://generativelanguage.googleapis.com";
    public $key = '';
    public $model = '';
    public $ds = [];
    public $dv = [];
    public $uploaded = [];
    public $cached = [];

    /**
     * Gemini constructor.
     *
     * @param string $model
     */
    public function __construct(string $model) {
        $this->key = ini()->{strtolower(self::class)}->key;
        $this->model = $model;
    }

    /**
     * Get answer from AI model on a given prompt
     *
     * @param string $prompt
     * @param array $files
     * @return int|mixed
     */
    public function request(string $prompt, array $files = []) {

        // Prompt content parts
        $parts = [];

        // Upload (if needed) and append files
        foreach ($files as $file) {
            $uploaded = $this->uploadIfNeed($file);
            $parts []= ['file_data' => ['mime_type' => $uploaded->mimeType, 'file_uri' => $uploaded->uri]];
        }

        // Add prompt
        $parts []= ["text" => $prompt];

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/models/$this->model:generateContent?key=$this->key",
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode([
                "contents" => [[
                    "parts" => $parts,
                    "role" => "user"
                ]],
                //"cachedContent" => 'cachedContents/oegooqnrvjww'//$this->cacheIfNeed("data/gemini/Indi Engine - docs.pdf")
            ])
        ]);

        // Get response
        $resp = curl_exec($ch);

        // Flush curl error, if any
        if (curl_errno($ch)) jflush(false, "Curl error: " . curl_error($ch));

        // Get http status code and close curl
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);

        // If code is not ok - flush
        if ($code !== 200 && $code !== 201) jflush(false, "HTTP $code\nBody:\n$resp\n");

        // Try parse JSON and flush failure if needed
        $json = json_decode($resp, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            jflush(false, "Failed to parse response JSON: " . json_last_error_msg() . "\nBody:\n$resp\n");
        }

        // If no text at the expected depth - flush as is
        if (!$resp = $json['candidates'][0]['content']['parts'][0]['text'] ?? 0) {
            jtextarea(false, json_encode($json, JSON_PRETTY_PRINT));
        }

        // Return response
        return $resp;
    }

    public function upload($file) {

        // Get file name (with extension)
        $name = basename($file);

        // Get file path
        $path = "data/prompt/$file";

        // Check file exists
        if (file_get_contents($path) === false) {
            jflush(false, "Failed to read file: $path\n");
        }

        // Get mime type and size
        $mime = Indi::mime($name);
        $size = filesize($path);

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

        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($path));
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
        return $this->uploaded[$name] = (object) $fileInfo['file'];
    }

    public function uploaded(string $file = null) {

        if (!$this->uploaded) {

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
            $this->uploaded = array_combine($names, $json->files);
        }

        /*if ($file) {
            $info = $this->uploaded[basename($file)] ?? null;

            $created = strtotime($info->updateTime, '');
            $updated = filemtime("data/prompt/$file");

            i([$created, $updated, $updated - $created, ($updated - $created) / 60]);

        }*/


        //jflush(false, json_encode($info, JSON_PRETTY_PRINT));
        //
        return $file
            ? $this->uploaded[basename($file)] ?? null
            : $this->uploaded;
    }

    public function deleteCached(string $file) {
        if (!$path = $this->cached($file)->name) return;
        $ch = curl_init("$this->baseUrl/v1beta/$path?key=$this->key");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        if (curl_errno($ch)) jflush(false, "cURL Error: " . curl_error($ch));
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200) {
            return true;
        } else {
            jflush(false,"Failed to delete cached '$name'.\n$httpCode: $response");
        }
    }

    public function cached(string $file = null) {

        if (!$this->cached) {
            curl_setopt_array($ch = curl_init(), [
                CURLOPT_URL => "$this->baseUrl/v1beta/cachedContents?key=$this->key",
                CURLOPT_RETURNTRANSFER => true,
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            //
            if ($code !== 200) {
                jflush(false, "Cache list retrieval failed with HTTP code: $code.\nResponse:\n$resp\n");
            }

            // Parse file info JSON
            $json = json_decode($resp);
            if (json_last_error() !== JSON_ERROR_NONE) {
                jflush(false, "Failed to parse cache list JSON: " . json_last_error_msg() . "\nResponse:\n$resp\n");
            }

            //
            if (!property_exists($json, 'cachedContents')) {
                return null;
            }

            $names = array_column($json->cachedContents, 'displayName');
            $this->cached = array_combine($names, $json->cachedContents);
        }

        //
        return $file
            ? $this->cached[basename($file)] ?? null
            : $this->cached;
    }

    public function deleteUploaded(string $name, $skip = false) {

        if (!$skip && !$path = $this->uploaded($name)->name) {
            return;
        }

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/{$this->uploaded[$name]->name}?key=$this->key",
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_RETURNTRANSFER => true,
        ]);

        // Exec curl
        $resp = curl_exec($ch);

        // Handle curl error
        if (curl_errno($ch)) jflush(false, "Curl error: " . curl_error($ch));

        // Get http code
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);

        // If code is 200
        if ($code === 200) {

            // Unset from $this->uploaded array
            unset($this->uploaded[$name]);

            // Return true
            return true;

        // Else flush failure
        } else {
            jflush(false,"Failed to delete file '$name'.\nCode: $code\nBody: $resp");
        }
    }

    /**
     * @param $file
     * @return array|mixed|object
     */
    public function uploadIfNeed(string $file) {

        // If file was already uploaded
        if ($uploaded = $this->uploaded($file)) {

            // Get timestamps
            $uploadedAt = date('Y-m-d H:i:s', strtotime($uploaded->createTime));
            $modifiedAt = date('Y-m-d H:i:s', filemtime("data/prompt/$file"));

            // If uploaded file is outdated
            if ($modifiedAt > $uploadedAt) {
                $this->deleteUploaded($file);
            }
        }

        // If uploaded file is still there - return it, else re-upload
        return $this->uploaded[$file] ?? $this->upload($file);
    }

    public function cacheIfNeed(string $file) {
        return $this->cached($file) ?? $this->cache($file);
    }

    public function cache($file) {

        $uploaded = $this->uploadIfNeed($file);
        $ch = curl_init($url = "$this->baseUrl/v1beta/cachedContents?key=$this->key");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data = [
            "model" => $this->model,
            "display_name" => $uploaded->displayName,
            "contents" => [[
                "parts" => [
                    ["file_data" => ["mime_type" => $uploaded->mimeType, "file_uri" => $uploaded->uri ]],
                ],
                "role" => 'user',
            ]],
            'system_instruction' => [
                'parts' => [[
                    'text' => <<<DOC
                        You are an expert in Indi Engine, which is a zero-code tool that allows developer to configure data-structures and quite
                        complex real-time data-views on top, organized into the foreign-key based hierarchies and having lots of features designed
                        to handle cases when there are lots of data needs to be shown on the screen at the same time. You became an expert because
                        you read carefully through the Indi Engine app-agnostic docs) and you are aware of rich 
                        formatting, e.g. bolds, italics, nested bullet points, font foreground and background colors, ordinary and nested tables 
                        with merged cells, so you have a comprehensive knowledge of concepts explained there, and knowledge of all zero-code 
                        features that are supported by Indi Engine, each with examples of use cases where those features are relevant and make sense.
                    DOC
                ]],
                "role" => 'system'
            ],
            "ttl" => '3600s'
        ], JSON_UNESCAPED_SLASHES));
        $resp = curl_exec($ch);

        if (curl_errno($ch)) {
            jflush(false, "cURL Error: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($resp, true);

        //
        if ($httpCode !== 200 && $httpCode !== 201) { // Expect 200 or 201 for creation

            //
            $text = json_last_error() !== JSON_ERROR_NONE
                ? $resp
                : json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            // Parse file info JSON
            jflush(false, "Request failed with HTTP code: $httpCode\nResponse:\n$text\n");
        }

        curl_close($ch);

        return $this->cached[$json->displayName] = $json;
    }

    public static function getModels() {
        return [
            'gemini-2.5-pro-exp-03-25',
            'gemini-2.5-pro-preview-03-25',
            'gemini-2.5-flash-preview-04-17',
            'gemini-2.0-flash-001',
            'gemini-2.0-flash-lite',
            'gemini-2.0-flash',
            'gemini-1.5-flash-latest',
            'gemini-1.5-pro',
        ];
    }
}