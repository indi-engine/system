<?php
class Gemini1 {

    public $baseUrl = "https://generativelanguage.googleapis.com";
    public $key = '';
    //public $model = 'models/gemini-2.0-flash-001';
    //public $model = 'models/gemini-2.5-pro-exp-03-25';
    //public $model = 'models/gemini-2.5-flash-preview-04-17';
    //public $model = 'models/gemini-2.5-pro-preview-03-25';
    //public $model = 'models/gemini-1.5-pro';
    //public $model = 'models/gemini-1.5-flash-latest';
    public $model =
        //'models/gemini-2.0-pro'
        //'models/gemini-2.0-flash',
        //'models/gemini-2.0-flash-lite'
        'models/gemini-2.5-flash-preview-04-17'
    ;
    public $ds = [];
    public $dv = [];


    public $files = [];

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
        //$gen = $this->uploadIfNeed("$pref - generation rules.md")->uri;
        $eds = $this->uploadIfNeed("$pref - data-structures.md")->uri;
        $edv = $this->uploadIfNeed("$pref - data-views.md")->uri;

        // Prepare full prompt
        $prompt = file_get_contents('data/gemini/prompt.md') . "\n$prompt";

        // Get response
        msg('Waiting for response from GPT...');
        mt();
        $resp = $this->scratch([$doc, $eds, $edv], $prompt);
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

    public function prepare($text, $code) {

        file_put_contents($code, "<?php\n");

        cfgField('element', 'decimal143', 'measure', [
            'title' => 'Unit of measurement',
            'elementId' => 'string',
            'columnTypeId' => 'TEXT',
        ]);

        section('users', ['title' => 'Users']);

        $txt = file_get_contents($text);
        // Trim comments
        $txt = preg_replace('~ // .+?$~m', '', $txt);

        // Copy bare entities declarations to the very top to prevent foreign key dependencies problems
        $php = preg_replace_callback("~(entity\(')(.+?)(', \['title' => ')(.+?)'~", function($m) use ($code) {
            $value = "$m[1]$m[2]$m[3]" . ucfirst(strtolower($m[4])) . "'";
            $this->ds[$m[2]] = [
                'isDict' => true,
                'isRole' => false,
                'props' => [
                    'title' => ucfirst(strtolower($m[4]))
                ],
                'fields' => []
            ];
            file_put_contents($code, "$value]);\n", FILE_APPEND);
            return $value;
        }, $txt);

        // Trim php opening tag
        $php = preg_replace('~<\?php~', '', $php);

        // Move full entities declaration from AI response to code file
        $php = preg_replace_callback("~^(.+?\n)(section\(')~s", function($m) use ($code) {

            $m[1] = preg_replace("~inQtySum\(.+?\);\n~", '', $m[1]);
            $m[1] = preg_replace("~consider\(.+?\);\s*//.+?\n~", '', $m[1]);

            $m[1] = preg_replace_callback("~field\('(?<table>.+?)', '(?<field>.+?)', (?<props>.+?)\);\s*(//.+?|)\n~s", function($f) use ($code) {
                $props = $this->toArray($f['props']);
                $this->ds[$f['table']]['fields'][$f['field']] = $props;
                if (in($props['columnTypeId'], 'DATE,DATETIME')) {
                    $this->ds[$f['table']]['isDict'] = false;
                } else if (count($this->ds[$f['table']]['fields']) > 3) {
                    if (!str_ends_with($f['table'], 'Type') && !in($f['table'], 'country,city')) {
                        $this->ds[$f['table']]['isDict'] = false;
                    }
                }
                if ($f['field'] === 'password') {
                    $this->ds[$f['table']]['isRole'] = true;
                }
                file_put_contents($code, $f[0], FILE_APPEND);
                return '';
            }, $m[1]);

            $m[1] = preg_replace_callback("~enumset\('(?<table>.+?)', '(?<field>.+?)', '(?<alias>.+?)', (?<props>.+?)\);\s*(//.+?|)\n~s", function($e) {
                if (in($this->ds[$e['table']]['fields'][$e['field']]['columnTypeId'], 'ENUM,SET')) {
                    $this->ds[$e['table']]['fields'][$e['field']]['nested']['enumset'][$e['alias']] = $this->toArray($e['props']);
                    return $e[0];
                }
                return '';
            }, $m[1]);

            $m[1] = preg_replace_callback("~param\('(?<table>.+?)', '(?<field>.+?)', '(?<param>.+?)', (?<value>.+?)\);$~sm", function($p) {
                if (!is_numeric($p['value'])) {
                    $p['value'] = trim($p['value'], "'");
                }
                if ($p['param'] === 'allowedTypes') {
                    return str_replace('allowedTypes' , 'allowTypes', $p[0]);
                } else if ($p['param'] === 'mask') {
                    return str_replace('mask' , 'inputMask', $p[0]);
                } else {
                    return $p[0];
                }
            }, $m[1]);

            file_put_contents($code, $m[1], FILE_APPEND);

            return $m[2];
        }, $php);

        // Add role() for each entity having Password-field
        foreach ($this->ds as $table => $entity) {
            if ($entity['isRole']) {
                if (!preg_match("~role\('$table',.+?\);\s*(//.+?|)\n~s", $php)) {
                    $title = ucfirst($table);
                    file_put_contents($code, "role('$table', ['title' => '$title', 'entityId' => '$table']);\n", FILE_APPEND);
                }
            }
        }

        // Move bare sections declarations from AI response to code file
        $php = preg_replace_callback("~section\('(?<section>.+?)', (?<props>.+?)\);\s*(//.+?|)\n~s", function($s) use ($code, $php){
            $props = $this->toArray($s['props']);
            if ($entity = $props['entityId']) {
                if (!$this->ds[$entity]) {
                    return '';
                }
            }
            $section = $s['section'];
            $this->dv[$s['section']]['isRoot'] = !$props['sectionId'];
            $this->dv[$s['section']]['props'] = $props;
            return $s[0];
        }, $php);

        // Move bare sections declarations from AI response to code file
        $php = preg_replace_callback("~section\('(?<section>.+?)', (?<props>.+?)\);\s*(//.+?|)\n~s", function($s) use ($code, $php){
            $props = $this->toArray($s['props']);
            $s['section'] = preg_replace('~Dict$~', '', $s['section']);
            $section = $s['section'];
            $entity = $props['entityId'];

            foreach (ar('sectionId,move') as $prop) {
                if (isset($props[$prop])) {
                    $props[$prop] = preg_replace('~Dict$~', '', $props[$prop]);
                }
            }
            $props['title'] = ucfirst(mb_strtolower($props['title']));

            if ($this->dv[$props['sectionId']]['isRoot']) {
                if ($this->ds[$props['entityId']]['isDict']) {
                    $props['sectionId'] = 'dict';
                } else if ($this->ds[$props['entityId']]['isRole']) {
                    $props['sectionId'] = 'usr';
                } else {
                    $props['sectionId'] = 'db';
                }
            }
            if ($colorField = $props['colorField']) {
                if ($colorField = $this->ds[$entity]['fields'][$colorField]) {
                    if ($colorField['storeRelationAbility'] === 'one') {
                        if ($colorFurther = $props['colorFurther']) {
                            if ($colorFurther = $this->ds[$colorField['relation']]['fields'][$colorFurther]) {
                                if ($colorFurther['elementId'] !== 'color') {
                                    unset($props['colorField'], $props['colorFurther']);
                                }
                            } else {
                                unset($props['colorField'], $props['colorFurther']);
                            }
                        } else {
                            unset($props['colorField'], $props['colorFurther']);
                        }
                    } else if ($colorField['elementId'] !== 'color') {
                        unset($props['colorField'], $props['colorFurther']);
                    }
                } else {
                    unset($props['colorField'], $props['colorFurther']);
                }
            }

            $props = _var_export($props);
            file_put_contents($code, "section('{$s['section']}', $props);\n", FILE_APPEND);
            return '';
        }, $php);

        // Remove trailing 'Dict'
        $php = preg_replace_callback("~(section2action|grid|alteredField|filter)(\('.+?)Dict',~s", function($sa) {
            return "$sa[1]$sa[2]', ";
        }, $php);

        //
        $php = preg_replace_callback("~(section2action|grid|alteredField|filter)\('(?<section>.+?)',.+?\]\);\n~s", function($sa) {
            $section = $sa['section'];
            if (!$this->dv[$section]) {
                return '';
            }
            return $sa[0];
        }, $php);

        $php = preg_replace_callback("~(grid|alteredField|filter)\('(?<section>.+?)',\s?'(?<field>.+?)',.+?\]\);\n~s", function($sa) {
            $section = $sa['section'];
            $field = $sa['field'];
            $entity = $this->dv[$section]['props']['entityId'];
            if (!$field = $this->ds[$entity]['fields'][$field]) {
                return '';
            }
            return $sa[0];
        }, $php);

        // Move section2action declarations from AI response to code file
        $php = preg_replace_callback("~section2action\((?<section>'.+?)', \[.+?\]\);\s*(//.+?|)\n~s", function($m) use ($code, $php){
            file_put_contents($code, $m[0], FILE_APPEND);
            return '';
        }, $php);

        //
        $php = preg_replace_callback("~grid\('(?<section>.+?)', '(?<field>.+?)', (?<props>\[.+?\])\);\s*(//.+?|)\n~s", function($g) use ($code, $php){

            $section = $g['section'];
            $field = $g['field'];
            if ($entity = $this->dv[$section]['props']['entityId']) {
                if (!$fieldInfo = $this->ds[$entity]['fields'][$field]) {
                    return '';
                }
            }

            $props = $this->toArray($g['props']);
            if ($target = $props['jumpSectionId']) {
                if (!$this->dv[$target]) {
                    if ($ds = $this->ds[$target]) {
                        $sectionProps = _var_export([
                            'title' => $this->ds[$target]['props']['title'] . "s",
                            'entityId' => $target,
                            'sectionId' => $ds['isDict'] ? 'dict' : 'db'
                        ]);
                        file_put_contents($code, "section('{$target}', $sectionProps);\n", FILE_APPEND);
                        foreach(ar('index,form,save,delete') as $action) {
                            file_put_contents($code, "section2action('{$target}', '$action', ['roleIds' => 'dev,admin']);\n", FILE_APPEND);
                        }
                    }
                }
            }
            if ($props['summaryType'] === 'avg') {
                $props['summaryType'] = 'average';
            }
            if ($props['colorBreak'] === 'y' && !in($fieldInfo['elementId'], 'number,price,decimal143')) {
                unset($props['colorBreak']);
            }
            $data = _var_export($props, 8);
            return "grid('$section', '$field', $data);\n";
        }, $php);


        //
        $php = preg_replace_callback("~m\('(?<entity>.+?)'\)->new\((?<data>\[.+?\])\)->save\(\);\s*(//.+?|)\n~s", function($r) use ($code, $php){
            if (preg_match('~\$[a-z]~', $r['data'])) {
                return $r[0];
            };
            $entity = $r['entity'];
            $data = $this->toArray($r['data']);
            foreach ($data as $field => &$value) {
                if ($meta = $this->ds[$entity]['fields'][$field]) {
                    if ($meta['columnTypeId'] == 'ENUM') {
                        if (!$meta['nested']['enumset'][$value]) {
                            $value = $meta['defaultValue'];
                        }
                    }
                }
            }
            $data = _var_export($data, 10);
            return "m('$entity')->new($data)->save();\n";
        }, $php);

        //d($this->ds);
        // Put remaining
        file_put_contents($code, $php, FILE_APPEND);
    }

    public function toArray($raw) {

        // Remove comments
        $raw = preg_replace('~//.*$~m', '', $raw);

        // Convert PHP-like array syntax to JSON
        $json = $raw;
        $json = trim($json);
        $json = preg_replace("/' => /", "':", $json);                   // Replace => with :
        $json = preg_replace("/'/", '"', $json);                    // Replace single quotes with double
        $json = preg_replace("/,\s*]/", "]", $json);                // Remove trailing comma before ]
        $json = preg_replace("/,\s*}/", "}", $json);                // Remove trailing comma before }
        $json = preg_replace("~^\[~", "{", $json);                // Remove trailing comma before }
        $json = preg_replace("~\]$~", "}", $json);                // Remove trailing comma before }

        $array = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            die("JSON parse error: " . json_last_error_msg() . ": " .  print_r($json, true));
        }
        return $array;
    }
}