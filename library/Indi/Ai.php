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
     * File where AI model response should be written to be further parsed and checked
     *
     * @var string
     */
    public $resp = '';

    /**
     * File where final php-code should be written to be further executed
     *
     * @var string
     */
    public $code = 'data/prompt/tmp/scratch.php';

    /**
     * @var string
     */
    public $lastUsedModel = 'data/prompt/tmp/model.txt';

    /**
     * Prompt purpose, can be:
     *
     *  ds    => Data structures
     *  ds-dv => Data structures and data-views
     *
     * @var string
     */
    public $mode = 'ds';

    /**
     * System fraction contents
     *
     * @var array
     */
    public $system = [];

    /**
     * Custom fraction contents
     *
     * @var array
     */
    public $custom = [];

    /**
     * @var array
     */
    public $elements = null;

    /**
     * @var array
     */
    public $columnTypes = null;

    /**
     * @var string[]
     */
    public $func = [
        'entity', 'field', 'enumset', 'param', 'resize', 'consider', 'section', 'inQtySum', 'sqlIndex',
        'section', 'section2action', 'grid', 'filter', 'alteredField'
    ];

    /**
     * @return Indi_Ai
     */
    public static function factory()
    {
        return self::$_instance ??= new self();
    }

    /**
     * Indi_Ai constructor.
     * @throws Exception
     */
    public function __construct() {

        // Load table names of system entities to be able to prevent conflicts
        foreach(m('entity')->all('`fraction` = "system"') as $entity) {
            $this->system['entity'][$entity->table] = true;
        }

        // Collect enumerated values per field per entity, if any
        foreach(db()->query("
            SELECT `t`.`table`, `f`.`alias` AS `field`, `e`.`alias`
            FROM `entity` `t`, `field` `f`, `enumset` `e`
            WHERE `e`.`fieldId` = `f`.`id` AND `f`.`entityId` = `t`.`id` AND `f`.`entry` = 0
        ")->all() as $e)
            $this->system['enumset'][ $e['table'] ][ $e['field'] ][ $e['alias']] = true;

        // Load aliases system sections to be able to prevent conflicts
        foreach(m('section')->all('`fraction` = "system"') as $section) {
            $this->system['section'][$section->alias] = true;
        }

        // Load elements
        foreach(m('element')->all() as $element) {
            $this->elements[$element->alias] = [
                'storeRelationAbility' => $element->storeRelationAbility,
                'defaultType' => $element->foreign('defaultType')?->type
            ];
        }

        // Load columnTypes
        foreach(m('columnType')->all() as $columnType) {
            $this->columnTypes[$columnType->type] = $columnType->foreign('elementId')?->fis('alias');
        }
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
            'purpose' => $isBare || ini()->gemini->debug ? 'scratch' : 'improve',
            'model' => $_SESSION['ai']['model'] ?? 'gemini-2.5-flash-preview-04-17',
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
    public function scratch(string $prompt, string $model, bool $cache = false) {

        // Purge all customizations to get back to bare Indi Engine app
        Indi::purge();

        // Setup resp file
        $this->resp($model);

        // Files to attach to prompt
        $files = [
            "Indi Engine - docs.pdf",
            "Indi Engine - data-structures.md",
            //"Indi Engine - data-views.md"
        ];

        // If $debug is empty - generate response and write it to file
        if (!ini()->gemini->debug) {
            $resp = $this->prompt($prompt, $model, $files, $cache); file_put_contents($this->resp, $resp);
        }

        // Prepare php code from response
        $total = $this->prepare(); $idx = 0;

        // Show progress bar with estimation by $total
        progress(($step = "Creating the app") . "...", $total);

        // Execute prepared code
        include $this->code;
    }

    public function prompt(string $prompt, string $model, array $files, bool $cache = false) {

        // Prepare full prompt
        $prompt = file_get_contents("data/prompt/$this->mode/prompt.md") . "\n\n$prompt";

        // Indicate step
        msg('Waiting for response from GPT...'); mt();

        // Get response
        $resp = $this->request($prompt, $model, $files, $cache);

        // Show response time
        msg('Model response time: ' . round(mt(), 2) . 's');

        // Return response
        return $resp;
    }

    public function request(string $prompt, string $model, array $files, bool $cache = true) {

        // Get model vendor
        $vendor = explode('-', $model)[0];

        // Check model vendor support
        if (!isset($this->vendors[$vendor])) {
            jflush(false, "Right now $vendor-models are not supported");
        }

        // Get model class instance
        $this->model ??= new $this->vendors[$vendor]($model);

        // Remember last used model
        file_put_contents($this->lastUsedModel, $model);

        // Do request on underlying model
        return $this->model->request($prompt, $files, $cache);
    }

    public function prepare() {

        // Setup response file
        if (!$this->resp) $this->resp();

        // Get text response generated by AI
        $resp = file_get_contents($this->resp);

        // Parse php function calls and put unparsed into trash.txt
        file_put_contents("data/prompt/tmp/trash.txt", $this->parseCalls($resp));

        // Check php function calls code generated by AI
        $this->checkParsedCalls();

        // Write php function calls code into a file to be further executed
        $this->writeParsedCalls();

        // Amend php function calls code to be able to count calls and track progress
        $php = preg_replace(
            '~](\)->save\(|)\);$~m', '$0 progress(++$idx, "$step: {percent}%");',
            file_get_contents($this->code), count:
            $total
        );

        // Write php code to a target file
        file_put_contents($this->code, $php);

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

    /**
     * @param $raw
     * @return mixed
     */
    public function toArray($raw) {

        if (is_array($raw)) {
            $call = $raw['call'];
            $coord = [];
            foreach (array_keys($raw) as $key) {
                if (!is_numeric($key) && $key != "call" && $key != "ctor") {
                    $coord []= $raw[$key];
                }
            }
            $coord = im($coord, "', '");
            $raw = $raw['ctor'];

        } else {
            $coord = null;
            $call = null;
        }
        if (!strlen($raw)) return false;

        // Remove comments
        $raw = preg_replace('~//.*$~m', '', $raw);

        // Convert PHP-like array syntax to JSON
        $json = $raw;
        $json = trim($json);
        $json = preg_replace("/' => /", "':", $json);                   // Replace => with :
        $json = preg_replace("/\"/", '\"', $json);                    // Replace single quotes with double
        $json = preg_replace("/'/", '"', $json);                    // Replace single quotes with double
        $json = preg_replace("/,\s*]/", "]", $json);                // Remove trailing comma before ]
        $json = preg_replace("/,\s*}/", "}", $json);                // Remove trailing comma before }
        $json = preg_replace("~^\[~", "{", $json);                // Remove trailing comma before }
        $json = preg_replace("~\]$~", "}", $json);                // Remove trailing comma before }

        $array = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            jflush(false,"JSON parse error: $call($alias): " .  print_r($raw, true));
        }
        return $array;
    }

    /**
     * @param $txt
     * @return array|string|string[]|null
     */
    public function parseCalls($txt) {

        // Replace CRLF with LF
        $txt = str_replace("\r\n", "\n", $txt);

        // Replace comments trailing '#'-comments
        $txt = preg_replace('~,\s*# .+$~m', ',', $txt);

        // Cut off and parse entity() calls
        $txt = preg_replace_callback("~\b(?<call>entity)\('(?<table>.+?)',\s*(?<ctor>\[.*?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['entity'][ $m['table'] ] ??= [];
                $this->custom['entity'][ $m['table'] ] += $ctor;
            }
        }, $txt);

        // Cut off and parse field() calls
        $txt = preg_replace_callback("~\b(?<call>field)\('(?<table>.+?)',\s*'(?<field>.+?)',\s*(?<ctor>\[.+?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['field'][$m['table']][$m['field']] ??= [];
                $this->custom['field'][$m['table']][$m['field']] += $ctor;
            }
        }, $txt);

        // Cut off and parse enumset() calls
        $txt = preg_replace_callback("~\b(?<call>enumset)\('(?<table>.+?)',\s*'(?<field>.+?)',\s*'(?<alias>.+?)',\s*(?<ctor>\[.+?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['enumset'][$m['table']][$m['field']][$m['alias']] ??= [];
                $this->custom['enumset'][$m['table']][$m['field']][$m['alias']] += $ctor;
            }
        }, $txt);

        // Cut off and parse resize() calls
        $txt = preg_replace_callback("~\b(?<call>resize\('(?<table>.+?)',\s*'(?<field>.+?)',\s*'(?<alias>.+?)',\s*(?<ctor>\[.+?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['resize'][$m['table']][$m['field']][$m['alias']] ??= [];
                $this->custom['resize'][$m['table']][$m['field']][$m['alias']] += $ctor;
            }
        }, $txt);

        // Cut off and parse param() calls
        $txt = preg_replace_callback("~\bparam\('(?<table>.+?)',\s*'(?<field>.+?)',\s*'(?<param>.+?)',\s*(?<value>.+?)\);~s", function($m) {
            $this->custom['param'][ $m['table'] ][ $m['field'] ][ $m['param'] ] = is_numeric($m['value']) ? $m['value'] : trim($m['value'], "'\"");
        }, $txt);

        // Cut off and parse role() calls
        $txt = preg_replace_callback("~\b(?<call>role)\('(?<alias>.+?)',\s*(?<ctor>\[.+?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['role'][$m['alias']] ??= [];
                $this->custom['role'][$m['alias']] += $ctor;
            }
        }, $txt);

        // Cut off and parse section() calls
        $txt = preg_replace_callback("~\bsection\('(?<alias>.+?)',\s*(?<ctor>\[.+?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['section'][$m['alias']] ??= [];
                $this->custom['section'][$m['alias']] += $ctor;
            }
        }, $txt);

        // Cut off an parse section2action() calls
        $txt = preg_replace_callback("~\bsection2action\('(?<section>.+?)',\s*'(?<action>.+?)',\s*(?<ctor>\[.+?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['section2action'][$m['section']][$m['action']] ??= [];
                $this->custom['section2action'][$m['section']][$m['action']] += $ctor;
            }
        }, $txt);

        // Cut off an parse grid() calls
        $txt = preg_replace_callback("~\bgrid\('(?<section>.+?)',\s*'(?<field>.+?)',\s*(?<ctor>.+?)\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['grid'][$m['section']][$m['field']] ??= [];
                $this->custom['grid'][$m['section']][$m['field']] += $ctor;
            }
        }, $txt);

        // Cut off an parse filter() calls
        $txt = preg_replace_callback("~\bfilter\('(?<section>.+?)',\s*'(?<field>.+?)'\s*(?:,\s*(?<ctor>.+?))?\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['filter'][$m['section']][$m['field']] ??= [];
                $this->custom['filter'][$m['section']][$m['field']] += $ctor;
            }
        }, $txt);

        // Cut off an parse alteredField() calls
        $txt = preg_replace_callback("~\b(?<call>alteredField)\('(?<section>.+?)',\s*'(?<field>.+?)',\s*(?<ctor>\[.+?])\);~s", function($m) {
            if (($ctor = $this->toArray($m)) !== false) {
                $this->custom['alteredField'][$m['section']][$m['field']] ??= [];
                $this->custom['alteredField'][$m['section']][$m['field']] += $ctor;
            }
        }, $txt);

        // Strip excessive newlines
        $txt = preg_replace('~\n{2,}~', "\n\n", $txt);

        // Return remaining text
        return $txt;
    }

    public function checkParsedCalls() {

        // Check entities
        foreach ($this->custom['entity'] as $table => &$ctor) {
            $this->checkEntity($table, $ctor);
        }

        // Check fields
        foreach ($this->custom['field'] as $table => &$fields) {
            foreach ($fields as $alias => &$ctor) {
                $this->checkField($table, $alias, $ctor);
            }
        }

        // Check enumset
        foreach ($this->custom['enumset'] as $table => &$fields) {
            foreach ($fields as $field => &$enumset) {
                foreach ($enumset as $alias => &$ctor) {
                    $this->checkEnumset($table, $field, $alias, $ctor);
                }
            }
        }

        // Check roles
        foreach ($this->custom['role'] as $alias => &$ctor) {
            $this->checkRole($alias, $ctor);
        }
    }

    public function writeParsedCalls() {

        // Write php opening tag
        $this->write("<?php\n", 0);

        // Write entity() calls
        foreach ($this->custom['entity'] as $table => $ctor) {
            $this->write("entity('$table', ['title' => '{$ctor['title']}']);");
        }

        // Write field() calls
        foreach ($this->custom['field'] as $table => $fields) {
            foreach ($fields as $alias => $ctor) {
                $this->write("field('$table', '$alias', " . $this->ctor($ctor) . ");");
            }
        }

        // Write enumset() calls
        foreach ($this->custom['enumset'] as $table => $fields) {
            foreach ($fields as $field => $enumset) {
                foreach ($enumset as $alias => $ctor) {
                    $this->write("enumset('$table', '$field', '$alias', " . $this->ctor($ctor) . ");");
                }
            }
        }

        // Write resize() calls
        foreach ($this->custom['resize'] as $table => $fields) {
            foreach ($fields as $field => $resize) {
                foreach ($resize as $alias => $ctor) {
                    $this->write("resize('$table', '$field', '$alias', " . $this->ctor($ctor) . ");");
                }
            }
        }

        // Write entity() calls for all other each entity props than 'title'
        foreach ($this->custom['entity'] as $table => $ctor) {
            unset($ctor['title']); $ctor && $this->write("entity('$table', " . $this->ctor($ctor) . ");");
        }

        // Write role() calls
        foreach ($this->custom['role'] as $alias => $ctor) {
            $this->write("role('$alias', " . $this->ctor($ctor) . ");");
        }

        // Write section() calls
        foreach ($this->custom['section'] as $alias => $ctor) {
            //$this->write("section('$alias', ['title' => '{$ctor['title']}']);");
        }
    }

    /**
     * @param $text
     * @param int $flag
     */
    private function write($text, $flag = FILE_APPEND) {
        file_put_contents($this->code, "$text\n", $flag);
    }

    /**
     * @param array $ctor
     * @return mixed|string|string[]|null
     */
    private function ctor(array $ctor) {
        return _var_export($ctor, 20);
    }

    /**
     * @param $table
     * @param $ctor
     */
    public function checkEntity($table, &$ctor) {

        // If custom entity table name is already in use
        if ($this->system['entity'][$table] ?? 0) {

            // Shortcut
            $inuse = $table;

            // Convert capitalization from 2nd and further words within titles
            $ctor['title'] = ucfirst(strtolower($ctor['title']));

            // Rename usages in first args
            foreach (ar('entity,field,param,resize,enumset') as $type) {
                if (in($table, 'role,enumset')) {
                    unset($this->custom[$type][$table]);
                } else if ($this->custom[$type] ?? 0) {
                    $this->renameKey($inuse, "c$inuse", $this->custom[$type]);
                }
            }

            // Rename usages in fields: 'alias', 'relation', 'move',
            foreach ($this->custom['field'] as $table => &$fields) {
                foreach ($fields as $alias => &$fctor) {
                    if (in($alias, "{$inuse}Id,{$inuse}Ids")) {
                        $this->renameKey($alias, "c$alias", $this->custom['field'][$table]);
                    }
                    if ($fctor['relation'] === $inuse) $fctor['relation'] = "c$inuse";
                    if ($fctor['move'] === "{$inuse}Id") $fctor['move'] = "c{$inuse}Id";
                }
            }
        }

        // If spaceScheme is going to be applied
        if ($ctor['spaceScheme'] ?? 0) {

            // Replace commas with dashes
            $ctor['spaceScheme'] = str_replace(',', '-', $ctor['spaceScheme']);

            // If given spaceScheme is not supported - unset both spaceScheme and spaceFields from $ctor
            if (!isset($this->system['enumset']['entity']['spaceScheme'][ $ctor['spaceScheme'] ])) {
                unset($ctor['spaceScheme'], $ctor['spaceFields']);
            }
        }

        // If spaceFields is going to be applied
        if ($ctor['spaceFields'] ?? 0) {

            // Check whether all of space-fields really exist
            $valid = true;
            foreach (ar($ctor['spaceFields']) as $spaceField)
                $valid = $valid && ($this->custom['field'][$table][$spaceField] ?? 0);

            // If at least one space-feld does not exist - unset both spaceScheme and spaceFields from $ctor
            if (!$valid) {
                unset($ctor['spaceScheme'], $ctor['spaceFields']);
            }
        }

        // If changeLogToggle is going to be applied
        if ($ctor['changeLogToggle'] ?? 0) {

            // If it's invalid value 'yes' - convert to valid value 'all'
            if ($ctor['changeLogToggle'] === 'yes') {
                $ctor['changeLogToggle'] = 'all';
            }

            // If value is not in the known list - unset both changeLogToggle and changeLogExcept from $ctor
            if (!isset($this->system['enumset']['entity']['changeLogToggle'][ $ctor['changeLogToggle'] ])) {
                unset($ctor['changeLogToggle'], $ctor['changeLogExcept']);
            }
        }

        //
        if (isset($ctor['changeLogExcept'])) {

            //
            if ("{$ctor['changeLogExcept']}" === '0') {
                $ctor['changeLogExcept'] = '';
            }
        }
    }

    /**
     * @param $oldKey
     * @param $newKey
     * @param $array
     */
    public function renameKey($oldKey, $newKey, &$array) {
        $keys = array_keys($array);
        $values = array_values($array);

        // Shortcuts
        $oldVal = $array[$oldKey] ?? null;
        $newVal = $array[$newKey] ?? null;
        $oldIdx = array_search($oldKey, $keys);
        $newIdx = array_search($newKey, $keys);

        // Rename old key to new key
        $keys[$oldIdx] = $newKey;

        // If new key already exists
        if ($newIdx !== false) {

            // Unset from keys to prevent duplicate keys
            unset($keys[$newIdx]);

            // If value under new key is array as the value under old key - merge thos arrays
            if (is_array($values[$oldIdx]) && is_array($values[$newIdx])) {
                $values[$oldIdx] += $values[$newIdx];
            }

            // Unset value previously existing under new key, as we have merged value now
            unset($values[$newIdx]);
        }

        // Overwrite $array
        $array = array_combine($keys, $values);
    }

    /**
     * @param $table
     * @param $alias
     * @param $ctor
     */
    public function checkField($table, $alias, &$ctor) {

        // If entity does not exist - unset field
        if (!isset($this->custom['entity'][$table])) {
            unset($this->custom['field'][$table][$alias]);
            return;
        }

        // If element is unknown - try to fix it
        if (!isset($this->elements[ $ctor['elementId'] ])) {

            // If $alias is 'photo'
            if ($alias === 'photo') {
                unset($ctor['columnTypeId']);
                $ctor['elementId'] = 'upload';
                $ctor['storeRelationAbility'] = 'none';
            }

            // If element is 'text' - change to 'textarea'
            if ($ctor['elementId'] === 'text') {
                $ctor['elementId'] = 'textarea';
            }
        }

        // Unset field
        if (!isset($this->elements[ $ctor['elementId'] ])) {
            unset($this->custom['field'][$table][$alias]);
            return;
        }

        // Get foreign key target, if it's a foreign key field
        $ref = $ctor['relation'] ?? null;

        // Convert capitalization from 2nd and further words within titles
        $ctor['title'] = ucfirst(strtolower($ctor['title']));

        // If foreign key target entity does not exist
        if ($ref && $ref !== 'enumset' && !isset($this->custom['entity'][$ref])) {

            // If target entity is 'country'
            if ($ref === 'country') {

                // Create it
                $this->custom['entity']['country'] = ['title' => 'Country'];
                $this->custom['field']['country']['title'] = [
                    'title' => 'Title',
                    'mode' => 'required',
                    'elementId' => 'string',
                    'columnTypeId' => 'VARCHAR(255)',
                    'move' => '',
                ];

            // Else unset the field
            } else {
                unset($this->custom['field'][$table][$alias]);
                return;
            }
        }

        //
        if ($unknown = $this->unknownEnumset('field', $ctor)) {
            if ($unknown['onDelete'] === 'SET_NULL') {
                $ctor['onDelete'] = 'SET NULL';
            }
        }
    }

    /**
     * @param string $entity
     * @param array $ctor
     * @return array
     */
    public function unknownEnumset(string $entity, array &$ctor) {
        $unknown = [];
        foreach ($this->system['enumset'][$entity] as $enumsetField => $knownValues) {
            if (isset($ctor[$enumsetField]) && !isset($knownValues[$ctor[$enumsetField]])) {
                $unknown[$enumsetField] = $ctor[$enumsetField];
                unset($ctor[$enumsetField]);
            }
        }
        return $unknown;
    }

    /**
     * @param $table
     * @param $field
     * @param $alias
     * @param $ctor
     */
    public function checkEnumset($table, $field, $alias, &$ctor) {

        // If entity does not exist - unset enumset
        if (!isset($this->custom['entity'][$table])) {
            unset($this->custom['enumset'][$table][$alias]);
            return;
        }

        // Convert capitalization from 2nd and further words within titles
        $ctor['title'] = ucfirst(strtolower($ctor['title']));

        // If field does not exists - create it
        if (!isset($this->custom['field'][$table][$field])) {
            $this->custom['field'][$table][$field] = [
                'title' => $this->fg($field),
                'storeRelationAbility' => 'one',
                'relation' => 'enumset',
                'onDelete' => 'RESTRICT',
                'elementId' => 'combo',
                'columnTypeId' => 'ENUM',
                'defaultValue' => $alias,
            ];
        }

        // If enumset() call is not for a foreign-key field
        if (!in($this->custom['field'][$table][$field]['storeRelationAbility'], 'one,many')) {

            // Check how many foreign-key fields are using this entity
            $using = [];
            foreach ($this->custom['field'] as $_table => $fields) {
                foreach ($fields as $_alias => $_ctor) {
                    if ($_ctor['relation'] === $table) {
                        $using [$_table] []= $_alias;
                    }
                }
            }
            if (count($using) === 1) {
                unset ($this->custom['entity'][$table]);
                unset ($this->custom['field'][$table]);
                foreach($using as $_table => $fields) {
                    foreach ($fields as $_field) {
                        $_ctor = $this->custom['field'][$_table][$_field];
                        $_ctor['relation'] = 'enumset';
                        $_ctor['columnTypeId'] = $_ctor['storeRelationAbility'] == 'one' ? 'ENUM' : 'SET';
                        $_ctor['defaultValue'] = $_ctor['storeRelationAbility'] == 'one' ? $alias : 'SET';
                        $this->custom['field'][$_table][$_field] = $_ctor;
                        $this->renameKey($field, $_field, $this->custom['enumset'][$table]);
                    }
                    $this->renameKey($table, $_table, $this->custom['enumset']);
                }
            }
        }
    }

    /**
     * @param $alias
     * @param $ctor
     */
    public function checkRole($alias, &$ctor) {

        // Unset order
        unset($ctor['move']);

        // Convert capitalization from 2nd and further words within titles
        $ctor['title'] = ucfirst(strtolower($ctor['title']));

        // If entity of users, specified for that role - does not exist - use 'admin' as a fallback
        if (!isset($this->custom['entity'][$ctor['entityId']])) {
            $ctor['entityId'] = 'admin';
        }
    }

    /**
     * Convert possible camel cased background name to foreground name
     *
     * @param $input
     * @return array|string|string[]|null
     */
    public function fg($bg) {

        // Capitalize the first camelCased word if needed
        $fg = preg_replace_callback(
            '/^([a-zäöüß])([a-zäöüß]*)([A-ZÄÖÜ])/',
            fn ($m) => ucfirst($m[1]) . $m[2] . $m[3],
            $bg
        );

        // Insert space before capital letters and lowercase them
        $fg = preg_replace_callback(
            '/([a-zäöüß])([A-ZÄÖÜ])/',
            fn ($m) => $m[1] . ' ' . mb_strtolower($m[2]),
            $fg
        );

        // Return label
        return $fg;
    }

    /**
     * @param $model
     */
    public function resp($model = '') {

        // If $model arg is not given
        if (!$model) {

            // Assume we're debugging
            ini()->gemini->debug = true;

            // Get last used model
            $model = file_get_contents($this->lastUsedModel);
        }

        // File to write response to
        mkdir($dir = "data/prompt/$this->mode/$model", 0777, true);

        // Count how many previous responses we have
        $old = glob("$dir/scratch*.txt");

        // If debug is enabled
        if ($debug = ini()->gemini->debug) {

            // If debug is true - use the last response, else use a response at specific index
            $idx = is_bool($debug) ? count($old) : $debug;

        // Else make sure it will be a new response file
        } else {
            $idx = count($old) + 1;
        }

        // Prepare response file path
        $this->resp = "$dir/scratch$idx.txt";

        // Check debug response file does really exist
        if ($debug && !is_file($this->resp)) {
            jflush(false, "No debug response file: $this->resp");
        }
    }
}