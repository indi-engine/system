<?php
class Gemini {

    public $baseUrl = "https://generativelanguage.googleapis.com";
    public $key = '';
    public $model = '';
    public $uploaded = null;
    public $cached = null;

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
    public function request(string $prompt, array $files = [], bool $cache = false) {

        // Prompt content parts
        $parts = [];

        // Upload (if needed) and append files
        if (!$cache) {
            foreach ($files as $file) {
                $uploaded = $this->uploadIfNeed($file);
                $parts []= ['file_data' => ['mime_type' => $uploaded->mimeType, 'file_uri' => $uploaded->uri]];
            }
        }

        // Add prompt
        $parts []= ["text" => $prompt];

        // Prepare data
        $data = [
            "contents" => [[
                "parts" => $parts,
                "role" => "user"
            ]],
        ];

        // If $cache flag is given as true - create a single cached content entry if not created
        // per the given sequence of $files, and append 'cachedContent' param to the request data
        if ($cache) {
            $data['cachedContent'] = $this->cacheIfNeed($files)->name;
        }

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/models/$this->model:generateContent?key=$this->key",
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data)
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
            jflush(false, json_encode($json, JSON_PRETTY_PRINT));
        }

        // Return response
        return $resp;
    }

    /**
     * Upload $file if not yet uploaded or outdated
     *
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
                $this->dropUploaded($file);
            }
        }

        // If uploaded file is still there - return it, else re-upload
        return $this->uploaded[$file] ?? $this->upload($file);
    }

    /**
     * Check whether $file is in the list of uploaded files
     *
     * @param string|null $file
     * @return array|mixed|null
     */
    public function uploaded(?string $file = null) {

        // Fetch the list of uploaded, if not yet fetched
        $this->uploaded ??= $this->listUploaded();

        // Return the info for a given $file, or the whole list of files
        return $file
            ? $this->uploaded[$file] ?? null
            : $this->uploaded;
    }

    /**
     * Get array of [file displayName => stdClass info object] pairs
     *
     * @return array
     */
    public function listUploaded() {

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/files?key=$this->key",
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);

        // If response code is not 200, or response body is not json-decodable - flush failure
        if ($code !== 200) {
            jflush(false, "Files list retrieval failed with HTTP code: $code.\nResponse:\n$resp\n");
        } else if ((!$json = json_decode($resp)) && json_last_error() !== JSON_ERROR_NONE) {
            jflush(false, "Files list not json-decodable: " . json_last_error_msg() . "\nResponse:\n$resp\n");
        }

        // Return array of [file displayName => stdClass info object] pairs
        return property_exists($json, 'files')
            ? array_combine(array_column($json->files, 'displayName'), $json->files)
            : [];
    }

    /**
     * Drop uploaded $file
     *
     * @param string $file
     * @param false $skip
     * @return bool|void
     */
    public function dropUploaded(string $file) {

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/{$this->uploaded[$file]->name}?key=$this->key",
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
            unset($this->uploaded[$file]);

            // Return true
            return true;

        // Else flush failure
        } else {
            jflush(false,"Failed to delete file '$file'.\nCode: $code\nBody: $resp");
        }
    }

    /**
     * Upload $file
     *
     * @param $file
     * @return object
     */
    public function upload($file) {

        // Get file path
        $path = "data/prompt/$file";

        // Check file exists
        if (file_get_contents($path) === false) {
            jflush(false, "Failed to read file: $path\n");
        }

        // Get mime type and size
        $mime = Indi::mime($file);
        $size = filesize($path);

        // Make request to get URL for the further uploaded file
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/upload/v1beta/files?key=$this->key",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POSTFIELDS => json_encode(['file' => ['display_name' => $file]]),
            CURLOPT_HTTPHEADER => [
                "X-Goog-Upload-Protocol: resumable",
                "X-Goog-Upload-Command: start",
                "X-Goog-Upload-Header-Content-Length: $size",
                "X-Goog-Upload-Header-Content-Type: $mime",
                "Content-Type: application/json"
            ]
        ]);
        $resp = curl_exec($ch);
        $head = substr($resp, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        curl_close($ch);

        // Extract upload URL from headers
        $uploadUrl = null;
        $headers = explode("\r\n", $head);
        foreach ($headers as $header) {
            if (stripos($header, "x-goog-upload-url:") === 0) {
                $uploadUrl = trim(substr($header, strlen("x-goog-upload-url:")));
                break;
            }
        }

        // If nothing exctracted - flush failure
        if ($uploadUrl === null) {
            jflush(false, "Failed to get upload URL from response headers.\nResponse Headers:\n$head\n");
        }

        // Upload $file
        curl_setopt_array($ch = curl_init($uploadUrl), [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => file_get_contents($path),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Length: $size",
                "X-Goog-Upload-Offset: 0",
                "X-Goog-Upload-Command: upload, finalize",
                "Content-Type: $mime"
            ]
        ]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);

        // If response code is not 200, or response body is not json-decodable - flush failure
        if ($code !== 200) {
            jflush(false, "File upload failed with code: $code\nResp:\n$resp\n");
        } else if ((!$json = json_decode($resp)) && json_last_error() !== JSON_ERROR_NONE) {
            jflush(false, "Failed to parse file info JSON: " . json_last_error_msg() . "\nResp:\n$resp\n");
        }

        // If file uri exists in json - add file info to $this->uploaded and return that info
        if ($json->file->uri ?? 0) {
            return $this->uploaded[$file] = $json->file;
        } else {
            jflush(false, "Failed to get file URI from file info response.\nResponse:\n$resp\n");
        }
    }

    /**
     * Cache a sequence of $files if not yet cached or outdated
     *
     * @param $file
     * @return array|mixed|object
     */
    public function cacheIfNeed(array $files) {

        // If $files sequence was already cached
        if ($cached = $this->cached($files)) {

            // Get timestamps
            $cachedAt = date('Y-m-d H:i:s', strtotime($cached->createTime));
            foreach ($files as $file) {
                $modifiedAt []= date('Y-m-d H:i:s', filemtime("data/prompt/$file"));
            }
            $modifiedAt = max($modifiedAt);

            // If any of files was updated after cached
            if ($modifiedAt > $cachedAt) {
                $this->dropCached($files);
            }
        }

        // If cache for sequence of $files is still there - return it, else cache that sequence
        return $this->cached[im($files)] ?? $this->cache($files);
    }

    /**
     * Check whether a sequence of $files is in the list of cached sequences
     *
     * @param string|null $file
     * @return array|mixed|null
     */
    public function cached(?array $files = null) {

        // Fetch list of cached, if not yet fetched
        $this->cached ??= $this->listCached();

        // Return the info for a given cache, or the whole list of cached sequences of files
        return $files
            ? $this->cached[im($files)] ?? null
            : $this->cached;
    }

    /**
     * Get array of [cache displayName => stdClass info object] pairs
     *
     * @return array
     */
    public function listCached() {

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/cachedContents?key=$this->key",
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);

        // If response code is not 200, or response body is not json-decodable - flush failure
        if ($code !== 200) {
            jflush(false, "Caches list retrieval failed with HTTP code: $code.\nResponse:\n$resp\n");
        } else if ((!$json = json_decode($resp)) && json_last_error() !== JSON_ERROR_NONE) {
            jflush(false, "Caches list not json-decodable: " . json_last_error_msg() . "\nResponse:\n$resp\n");
        }

        // Prepare array of [cache displayName => stdClass info object] pairs
        if (property_exists($json, 'cachedContents')) {

            // Unset caches from other models
            foreach ($json->cachedContents as $idx => $cache) {
                if ($cache->model !== "models/$this->model") {
                    unset ($json->cachedContents[$idx]);
                }
            }

            // Prepare and return pairs
            return array_combine(array_column($json->cachedContents, 'displayName'), $json->cachedContents);
        }

        // Return empty array
        return [];
    }

    /**
     * Drop cached sequence of $files
     *
     * @param array $files
     * @return bool|void
     */
    public function dropCached(array $files) {

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/{$this->cached[im($files)]->name}?key=$this->key",
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

            // Unset from $this->cached array
            unset($this->cached[im($files)]);

            // Return true
            return true;

        // Else flush failure
        } else {
            jflush(false,"Failed to delete cache '" . im($files) . "'.\nCode: $code\nBody: $resp");
        }
    }

    /**
     * Get answer from AI model on a given prompt
     *
     * @param string $prompt
     * @param array $files
     * @return int|mixed
     */
    public function cache(array $files) {

        // Prompt content parts
        $parts = [];

        // Upload (if needed) and append files as content parts
        foreach ($files as $file) {
            $uploaded = $this->uploadIfNeed($file);
            $parts []= ['file_data' => ['mime_type' => $uploaded->mimeType, 'file_uri' => $uploaded->uri]];
        }

        // Init curl
        curl_setopt_array($ch = curl_init(), [
            CURLOPT_URL => "$this->baseUrl/v1beta/cachedContents?key=$this->key",
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data = [
                'model' => "models/$this->model",
                'display_name' => im($files),
                'contents' => [[
                    'parts' => $parts,
                    'role' => 'user'
                ]],
                'system_instruction' => [
                    'parts' => [
                        [
                            'text' => '',//file_get_contents('data/prompt/cache-system.md')
                        ]
                    ],
                    'role' => 'system'
                ],
                //'ttl' => 300 // Time to live = 1 hour by default
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
        if (!$resp = $json['name'] ?? 0) {
            jflush(false, json_encode($json, JSON_PRETTY_PRINT));
        }

        // Return response
        return $resp;
    }

    public function cache1($file) {

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