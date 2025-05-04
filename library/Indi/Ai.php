<?php
class Indi_Ai {

    /**
     * Singleton instance
     *
     * @var Indi_Ai
     */
    protected static $_instance = null;

    /**
     * Pairs of [vendor => class] for AI models
     *
     * @var string[]
     */
    public $vendors = [
        //'gpt'    => 'OpenAI',
        'gemini' => 'Gemini'
    ];

    /**
     * @return Indi_Ai
     */
    public static function factory()
    {
        return self::$_instance ??= new self();
    }

    /**
     * @var Gemini
     */
    public mixed $model;

    /**
     * Show dialog to allow user to specify prompt text and purpose, and select AI model
     *
     * @param Indi_Controller $ctrl
     * @return mixed
     * @throws Exception
     */
    public function dialog(Indi_Controller $ctrl) {

        // Setup a flag indicating whether current Indi Engine app is a bare app
        // which would be the case if current app has no custom entities so far
        $isBare = ! m()->all('`fraction` = "custom"')->count();

        // Define 'Purpose'-field to be added further to 'Build with AI'-dialog
        m()->fields()->add([
            'alias' => 'purpose',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'mode' => 'hidden',
        ]);

        // Define choices for that field
        m()->fields('purpose')->nested('enumset', [
            ['alias' => 'improve', 'title' => 'Evolve existing app'],
            ['alias' => 'scratch', 'title' => 'Build new app from ' . wrap('scratch', '<span data-title="Any customizations you\'ve done so far - will be completely purged">', !$isBare)],
        ]);

        // Make sure the long title of a 1st choice won't be cut
        m()->fields('purpose')->param('substr', 200);

        // Define 'AI model'-field to be also added further to same dialog
        m()->fields()->add([
            'alias' => 'model',
            'columnTypeId' => 'ENUM',
            'elementId' => 'combo',
            'storeRelationAbility' => 'one',
            'relation' => 'enumset',
            'mode' => 'hidden',
            'title' => 'AI Model'
        ]);

        // Setup default values
        t()->row = m()->new([
            'purpose' => $isBare ? 'scratch' : 'improve',
            'model' => 'gemini-2.5-flash-preview-04-17',
        ]);

        // Placeholders per choice
        $placeholder = [
            'scratch' => 'Write here something about the app you want to build from scratch...',
            'improve' => 'Write here something you want to adjust, append or remove...',
        ];

        // Ask user for description, build purpose and AI model
        return $ctrl->prompt([
            'title' => t()->action->title,
            'icon' => false,
            'onshow' => 'onAIDialogShow',
            'items' => [
                [
                    'xtype' => 'textarea',
                    'name' => 'prompt',
                    'width' => 600,
                    'emptyText' => $placeholder[t()->row->purpose],
                    'value' => 'i need an app for the zoo',
                    'grow' => true,
                    'growMax' => 300,
                    'growMin' => 30,
                    'margin' => '0 0 6 0',
                    'emptyTextByPurpose' => $placeholder,
                    'isCustomField' => true,
                ],
                [
                    'xtype' => 'container',
                    'layout' => 'hbox',
                    'margin' => '0 0 0 0',
                    'padding' => 0,
                    'defaults' => [
                        'margin' => 0
                    ],
                    'items' => [
                        [
                            'layout' => 'hbox',
                            'columns' => 2,
                            'flex' => 1,
                            'fieldLabel' => false,
                            'isCustomField' => true,
                            'disabledOptions' => rif($isBare, 'improve'),
                            ... t()->row->radio('purpose'),
                        ],
                        [
                            'flex' => 0.9,
                            'labelWidth' => 50,
                            'isCustomField' => true,
                            ... t()->row->combo('model', [
                                'Gemini' => Gemini::getModels(),
                                'OpenAI' => OpenAI::getModels()
                            ]),
                        ]
                    ],
                ],
            ]
        ]);
    }

    /**
     * Improve existing app with a prompt to AI model
     *
     * @param string $prompt
     * @param string $model
     */
    public function improve(string $prompt, string $model) {
        jflush(false, 'Evolving existing app via AI is not yet supported');
    }

    /**
     * Build app from scratch with a prompt to AI model
     *
     * @param string $prompt
     * @param string $model
     */
    public function scratch(string $prompt, string $model) {

        // Purge all customizations to get back to bare Indi Engine app
        Indi::purge();

        // File to write response to
        $text = 'data/prompt/scratch.txt';

        // Files to attach to prompt
        $files = [
            "Indi Engine - docs.pdf",
            "Indi Engine - data-structures.md",
            "Indi Engine - data-views.md"
        ];

        // Generate response and write it to file
        //$resp = $this->prompt($prompt, $model, $files); file_put_contents($text, $resp);

        // Prepare php code from response
        $total = $this->prepare($code = 'data/prompt/scratch.php', $text); $idx = 0;

        // Show progress bar with estimation by $total
        progress(($step = "Creating the app") . "...", $total);

        // Execute prepared code
        include $code;
    }

    public function prompt(string $prompt, string $model, array $files) {

        // Prepare full prompt
        $prompt = file_get_contents('data/prompt/prompt.md') . "\n\n$prompt";

        // Indicate step
        msg('Waiting for response from GPT...'); mt();

        // Get response
        $resp = $this->request($prompt, $model, $files);

        // Show response time
        msg('Model response time: ' . round(mt(), 2) . 's');

        // Return response
        return $resp;
    }

    public function request(string $prompt, string $model, array $files) {

        // Get model vendor
        $vendor = explode('-', $model)[0];

        // Check model vendor support
        if (!isset($this->vendors[$vendor])) {
            jflush(false, "Right now $vendor-models are not supported");
        }

        // Get model class instance
        $this->model ??= new $this->vendors[$vendor]($model);

        // Do request on underlying model
        return $this->model->request($prompt, $files);
    }

    public function prepare($code, $text) {

        // Get php code generated by AI and do initial cleanup
        $php = file_get_contents($text);
        $php = str_replace("\r\n", "\n", $php);
        $php = preg_replace('~^.*?```php\s*(<\?php\n|)~s', '', $php);
        $php = preg_replace('~(\?>\n|)```~', '', $php);
        file_put_contents($code, "<?php\n");

        // Validate php code generated by AI
        $php = $this->validate($code, $php); file_put_contents($code, $php, FILE_APPEND);


        // Amend php-code to be able to count calls and track progress
        $php = preg_replace(
            '~](\)->save\(|)\);$~m', '$0 progress(++$idx, "$step: {percent}%");',
            file_get_contents($code), count:
            $total
        );

        // Write php code to a target file
        file_put_contents($code, $php);

        // Return total number of calls
        return $total;
    }

    public function validate($code, $txt) {

        // Trim comments
        //$txt = preg_replace('~ // .+?$~m', '', $txt);
        $txt = preg_replace('~^# .+$~m', '', $txt);
        $txt = preg_replace('~\n{2,}~', "\n\n", $txt);

        // Copy bare entities declarations to the very top to prevent foreign key dependencies problems
        $php = preg_replace_callback("~(entity\(')(.+?)(', \[)(\s*?)('title' => ')(.+?)',~", function($m) use ($code) {
            $title = ucfirst(strtolower($m[6]));
            $value = "$m[1]$m[2]$m[3]$m[5]$title";
            $this->ds[$m[2]] = [
                'isDict' => true,
                'isRole' => false,
                'props' => [
                    'title' => $title
                ],
                'fields' => []
            ];
            file_put_contents($code, "$value']);\n", FILE_APPEND);
            return $m[0];
        }, $txt);
        file_put_contents($code, "\n\n", FILE_APPEND);

        // Small hacks
        $php = str_replace("'elementId' => 'decimal',", "'elementId' => 'price',", $php);
        $php = str_replace("  'toggleTile' => 'textUnderImage',", "  'toggleTile' => 'y',", $php);

        // Foreach field
        $php = preg_replace_callback("~^field\('(?<table>.+?)', '(?<field>.+?)',\s+(?<props>.+?)\);\s*~ms", function($f) use ($code) {

            $props = $this->toArray($f['props']); $table = $f['table']; $field = $f['field'];

            // Append field definition
            $this->ds[$table]['fields'][$field] = $props;

            // Decide on wheter current entity is NOT a dictionary
            if (
                in($props['columnTypeId'], 'DATE,DATETIME')
                || (
                    count($this->ds[$table]['fields']) > 3
                    && !str_ends_with($table, 'Type')
                    && !in($f['table'], 'country,city')
                )
            ) {
                $this->ds[$table]['isDict'] = false;
            }

            // If it's a Password-field - mark entity as used by a role
            if ($field === 'password') {
                $this->ds[$table]['isRole'] = true;
            }

            // Move field definitions at the top right after entities
            file_put_contents($code, $f[0], FILE_APPEND);

            // Return empty string to cut off from $php
            return '';
        }, $php);

        // Foreach enumset
        $php = preg_replace_callback("~enumset\('(?<table>.+?)', '(?<field>.+?)', '(?<alias>.+?)',\s+(?<props>.+?)\);\s*~ms", function($m) {

            // Extract match
            $props = $this->toArray($m['props']); $table = $m['table']; $field = $m['field']; $alias = $m['alias'];

            // If fields column type is ENUM or SET
            if (in($this->ds[$table]['fields'][$field]['columnTypeId'], 'ENUM,SET')) {

                // Assign values
                $this->ds[$table]['fields'][$field]['nested']['enumset'][$alias] = $props;

                // Return as is
                return $m[0];

            // Else return empty string
            } else {
                return '';
            }
        }, $php);

        // Foreach param
        $php = preg_replace_callback("~param\('(?<table>.+?)', '(?<field>.+?)', '(?<param>.+?)', (?<value>.+?)\);$~sm", function($m) {
            if (!is_numeric($m['value'])) {
                $m['value'] = trim($m['value'], "'");
            }
            if ($m['param'] === 'allowedTypes') {
                return str_replace('allowedTypes' , 'allowTypes', $m[0]);
            } else if ($m['param'] === 'mask') {
                return str_replace('mask' , 'inputMask', $m[0]);
            } else {
                return $m[0];
            }
        }, $php);

        // Move full data-structures declarations from AI response to code file, with cutting it out of $php variable
        $php = preg_replace_callback("~^(.+?\n)(section\(')~s", function($m) use ($code) {
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
        file_put_contents($code, "\n", FILE_APPEND);

        // Strip sections data-sourced by non-existing entities and collect valid ones
        $php = preg_replace_callback("~section\('(?<section>.+?)', (?<props>.+?)\);\s*(//.+?|)\n~s", function($m) use ($code, $php){
            $props = $this->toArray($m['props']); $section = $m['section'];
            if ($entity = $props['entityId']) {
                if (!$this->ds[$entity]) {
                    return '';
                }
            }
            $this->dv[$section]['isRoot'] = !$props['sectionId'];
            $this->dv[$section]['props'] = $props;
            return $m[0];
        }, $php);

        // Trim php opening tag
        //$php = preg_replace('~<\?php~', '', $php);
        //$php = preg_replace('~( => )‘(.+?)’~', '$1\'$2\'', $php);

        //$m[1] = $php;
        //$m[1] = preg_replace("~countInQtySum\('(?<table>.+?)', '(?<field>.+?)', '(?<param>.+?)', (?<value>.+?)\);$~sm", '', $m[1]);
        //$php = $m[1];
        //file_put_contents($code, $m[1], FILE_APPEND);

        //
        $php = preg_replace_callback("~section\('(?<section>.+?)', (?<props>.+?)\);\s*~s", function($m) use ($code, $php){

            // Shortcuts
            $props = $this->toArray($m['props']);
            $section = preg_replace('~Dict$~', '', $m['section']);
            $parent = $props['sectionId'];
            $entity = $props['entityId'];
            $props['title'] = ucfirst(mb_strtolower($props['title']));

            // Remove 'Dict' suffix
            foreach (ar('sectionId,move') as $prop) {
                if (isset($props[$prop])) {
                    $props[$prop] = preg_replace('~Dict$~', '', $props[$prop]);
                }
            }

            // Apply to the proper menu group
            if ($this->dv[$parent]['isRoot']) {
                if ($this->ds[$entity]['isDict']) {
                    $props['sectionId'] = 'dict';
                } else if ($this->ds[$entity]['isRole']) {
                    $props['sectionId'] = 'usr';
                } else {
                    $props['sectionId'] = 'db';
                }
            }

            //
            if ($connector = $props['parentSectionConnector']) {
                if (!$this->ds[$entity]['fields'][$connector]) {
                    unset($props['parentSectionConnector']);
                }
            }

            // Validate color fields and unset if invalid
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

            // Rebuild props
            $props = _var_export($props);

            // Write to file
            file_put_contents($code, "section('$section', $props);\n", FILE_APPEND);

            // Cut from $php
            return '';
        }, $php);

        // Remove things related to non-existing sections
        $php = preg_replace_callback("~(section2action|grid|alteredField|filter)\('(?<section>.+?)',.+?\]\);\n~s", function($sa) {
            $section = $sa['section'];
            if (!$this->dv[$section]) {
                return '';
            }
            return $sa[0];
        }, $php);

        // Move section2action declarations from AI response to code file
        $php = preg_replace_callback("~section2action\((?<section>'.+?)', \[.+?\]\);\s*~s", function($m) use ($code, $php){
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
                    unset($props['jumpSectionId'], $props['jumpSectionActionId']);
                    /*if ($ds = $this->ds[$target]) {
                        $sectionProps = _var_export([
                            'title' => $this->ds[$target]['props']['title'] . "s",
                            'entityId' => $target,
                            'sectionId' => $ds['isDict'] ? 'dict' : 'db'
                        ]);
                        file_put_contents($code, "section('{$target}', $sectionProps);\n", FILE_APPEND);
                        foreach(ar('index,form,save,delete') as $action) {
                            file_put_contents($code, "section2action('{$target}', '$action', ['roleIds' => 'dev,admin']);\n", FILE_APPEND);
                        }
                    }*/
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
        $php = preg_replace_callback("~alteredField\('(?<section>.+?)', '(?<field>.+?)', (?<props>\[.+?\])\);\s*~s", function($g) use ($code, $php){
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
                    unset($props['jumpSectionId'], $props['jumpSectionActionId']);
                }
            }
            $data = _var_export($props, 8);
            return "grid('$section', '$field', $data);\n";
        }, $php);

        return $php;

        // Remove trailing 'Dict'
        /*$php = preg_replace_callback("~(section2action|grid|alteredField|filter)(\('.+?)Dict',~s", function($sa) {
            return "$sa[1]$sa[2]', ";
        }, $php);*/

        /*$php = preg_replace_callback("~(grid|alteredField|filter)\('(?<section>.+?)',\s?'(?<field>.+?)',.+?\]\);\n~s", function($sa) {
            $section = $sa['section'];
            $field = $sa['field'];
            $entity = $this->dv[$section]['props']['entityId'];
            if (!$field = $this->ds[$entity]['fields'][$field]) {
                return '';
            }
            return $sa[0];
        }, $php);*/


        //
        /*$php = preg_replace_callback("~m\('(?<entity>.+?)'\)->new\((?<data>\[.+?\])\)->save\(\);\s*(//.+?|)\n~s", function($r) use ($code, $php){
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
        }, $php);*/

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