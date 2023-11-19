<?php
class Admin_EntitiesController extends Indi_Controller_Admin_Exportable {

    /**
     * Output of last $this->exec($command) call
     *
     * @var string
     */
    public string $msg = '';

    /**
     * Git things
     *
     * @var array
     */
    private $git = [

        // Path to git config file
        'config' => '.git/config',

        // Git
        'auth' => [

            // Auth string in format 'username:token' for use to access current project's private repository, if private
            'value' => '',

            // Regular expressions to work with git config file contents and/or auth string given via prompt
            'rex' => [

                // Regex to match some text which comes right before auth string inside git config file
                'conf' => '\[remote "origin"\]\s+.*?url\s*=\s*.*//',

                // Regex to match auth string itself
                'self' => '[a-zA-Z0-9_]+:[a-zA-Z0-9_]+',

                // Regex to match repo url (e.g. github.com/repo-account/repo-name.git) that comes after auth string plus '@'
                'repo' => '[^\n]+'
            ]
        ]
    ];

    /**
     * Prompt for auth-string to be able to update current repository using `git pull` command
     */
    public function promptGitUserToken() {

        // Prompt for valid Google Cloud Translate API key
        $prompt = $this->prompt(im([
            'Please specify git username and token to update current repo via `git pull` command',
            'Leave empty if your project use public repository'
        ], '<br>'), [[
            'xtype' => 'textfield',
            'emptyText' => 'yourUsername:yourToken',
            //'regex' => '/' . $this->git['auth']['rex']['self'] . '/',
            'width' => 250,
            'name' => $name = 'user:token'
        ]]);

        // Check prompt data
        jcheck([
            $name => [
                'rex' => '~^' . $this->git['auth']['rex']['self'] . '$~'
            ]
        ], $prompt);

        // Assign as prop to be further accessible
        $this->git['auth']['value'] = $prompt[$name];
    }

    /**
     * Execute shell command
     *
     * @param $command
     * @param string $folder Directory context
     * @param string $noExitOnFailureIfMsgContains Don't die on non-0 exit code if output contains given string
     * @param bool $silent Prevent this command from being shown in user's notification area
     * @return false|string
     */
    public function exec($command, string $folder = '', string $noExitOnFailureIfMsgContains = '', $silent = false) {

        // Prepare msg
        $msg = rif($folder, '$1$ ');
        $msg .= str_replace([
            $this->git['auth']['value'],
            '-p' . ini()->db->pass
        ], [
            '*user*:*token*',
            '-p*pass*'
        ], $command);

        // Print where we are
        if (!$silent) ini()->rabbitmq->enabled ? msg($msg) : i($msg, 'a', 'log/update.log');

        // If command should be executed within a certain directory
        if ($folder) {

            // Remember current directory
            $wasdir = getcwd();

            // Change current directory
            chdir($folder);
        }

        // Exec command
        exec("$command 2>&1", $output, $exit_code);

        // Change current directory back
        if ($folder) chdir($wasdir);

        // Prepare msg
        $this->msg = join("<br>", $output);

        // If success (e.g. exit code is 0) - just return msg
        if (!$exit_code) return $this->msg;

        // Else if failure, but msg contains given value - return false
        if ($match = $noExitOnFailureIfMsgContains)
            if (preg_match("~$match~", $this->msg))
                return false;

        // Setup debug info
        $user = posix_getpwuid(posix_geteuid());
        $debug [-7] = 'php posix_getpwuid(..)[\'name\']: ' . $user['name'];
        $debug [-6] = 'env APACHE_RUN_USER: ' . getenv('APACHE_RUN_USER');
        $debug [-5] = 'php get_current_user(): ' . get_current_user();
        $debug [-4] = 'php posix_getpwuid(..)[\'dir\']: ' . $user['dir'];
        $debug [-3] = 'env HOME: ' . getenv('HOME');
        $debug [-2] = 'env COMPOSER_HOME: ' . getenv('COMPOSER_HOME');
        $debug [-1] = '---------';

        // Return output
        $msg = join("<br>", $debug + $output);

        // Else flush failure right here
        jflush(false, $msg);
    }

    /**
     * Strip git username and token (if any) from repository url inside git config file
     */
    public function stripGitUserToken() {

        // Get config file contents
        $config['text'] = file_get_contents($this->git['config']);

        // Shortcuts
        list ($conf, $self, $repo) = array_values($this->git['auth']['rex']);

        // Strip username and token (if any) from repository url
        $config['text'] = preg_replace("~($conf)$self@($repo)~s",'$1$2', $config['text']);

        // Write back
        file_put_contents($this->git['config'], $config['text']);
    }

    /**
     * Parse repo url within git config file
     */
    public function parseGitRepoUrl() : string {

        // Get config file contents
        $config['text'] = file_get_contents($this->git['config']);

        // Shortcuts
        list ($conf, $self, $repo) = array_values($this->git['auth']['rex']);

        // Strip username and token (if any) from repository url
        return 'https://' . Indi::rexm("~($conf)($self@|)($repo)~s", $config['text'], 3);
    }

    /**
     * Apply git username and token (if any) from repository url inside git config file
     *
     * @param string $url
     * @return string|null
     */
    public function applyGitUserToken($url = ''): ?string
    {
        // If git auth string is empty - return
        if (!$this->git['auth']['value']) return $url;

        // If repo url is given as argument
        if ($url) {

            // Apply auth string into that url here and return it
            return preg_replace('~://~', '${0}' . $this->git['auth']['value'] . '@', $url);
        }

        // Strip git username and token from repo url within git config file
        $this->stripGitUserToken();

        // Get config file contents
        $config['text'] = file_get_contents($this->git['config']);

        // Shortcuts
        list ($conf, $self, $repo) = array_values($this->git['auth']['rex']);

        // Apply auth string value
        $config['text'] = preg_replace("~($conf)($repo)~s",'$1' . $this->git['auth']['value'] . '@$2', $config['text']);

        // Write back
        file_put_contents($this->git['config'], $config['text']);

        // Return null
        return null;
    }

    /**
     * Check whether local $repo is outdated
     *
     * @param string $repo
     * @return true|string
     */
    public function isRepoOutdated(string $repo) {

        // If $repo is 'custom'
        if ($repo === 'custom') {

            // Get project remote repository URL
            $remote = $this->parseGitRepoUrl();

            // Current folder assumed
            $folder = false;

            // Apply git username and token
            $remote = '-h -t ' . $this->applyGitUserToken($remote);

        // Else assume vendor package
        } else {

            // Get package remote repository URL
            $remote = "https://github.com/indi-engine/$repo.git";

            // Get the folder where package's local repository reside
            $folder = ltrim(VDR, '/') . "/$repo";
        }

        // Get the latest commit in package's remote repository
        //$commit = $this->exec("git ls-remote $remote master | awk '{print $1}'");
        $commit = preg_split('~\s+~', $this->exec("git ls-remote $remote master"))[0];

        // Call 'git branch' command in that folder to check whether latest commit already exists there
        $exists = $this->exec("git branch --contains $commit", $folder, 'no such commit');

        // Return
        return $exists === false ? true : $commit;
    }

    /**
     * Check whether contents of composer.lock file is outdated due to that commit hash
     * mentioned as a package source reference is not equal to the $commit, assuming that
     * $commit arg is the hash of the most recently pushed commit
     *
     * @param $repo
     * @param $commit
     * @return bool
     */
    public function isLockOutdated($repo, $commit) {

        // Get and decode lock file contents
        $lock = json_decode(file_get_contents('composer.lock'));

        // Find $repo among packages and check last commit
        foreach ($lock->packages as $package) {
            if ($package->name === "indi-engine/$repo") {
                return $package->source->reference !== $commit;
            }
        }

        // If no such package was found at all - return true
        return true;
    }

    /**
     *
     */
    private function _migrate() {

        // Repos to be checked for pending migrations
        $repoA = [
            'system' => [
                'folder' => ltrim(VDR, '/') . '/system',
                'commit' => '13363c622921edd7e709b42f088accbeb711ae16',
                'detect' => 'library/Indi/Controller/Migrate.php'
            ],
            'custom' => [
                'folder' => '',
                'commit' => '42f49fa04ea45d54fca6834b7b6db543e2161a1c',
                'detect' => 'application/controllers/admin/MigrateController.php',
            ]
        ];

        // Foreach repo
        foreach ($repoA as $fraction => $repo) {

            // Shortcuts
            $folder = $repo['folder'];
            $commit = $repo['commit'];
            $detect = $repo['detect'];

            // Get files changed since last commit which we did migrate at
            if ($files = $this->exec("git diff --name-only $commit", $folder)) {

                // Convert files list into an array
                $files = explode("<br>", $files);

                // If migrations-file was changed
                if (in($detect, $files)) {

                    // Get what's changed
                    $diff = $this->exec("git diff $commit -- $detect", $folder);

                    // If new migration-actions detected
                    if ($actions = Indi::rexma('~<br>\+\s+public function ([a-zA-Z_]+)Action\(~', $diff)[1]) {

                        // Reverse list of migrations, as new ones are added at the top of migrations controller class
                        // just to prevent developer to scrolling to the bottom of file having thousands of lines
                        $actions = array_reverse($actions);

                        // Run migrations
                        foreach ($actions as $action)
                            $this->exec("php indi migrate/$action");

                    // Else flush the status
                    } else msg("$fraction: no new migrations yet");

                // Else flush the status
                } else msg("$fraction: migrations file not changed");

                // If php-file responsible for turning On l10n for certain field - was changed
                if (in($l10n_meta = 'application/lang/ui.php', $files)) {

                    // Print where we are
                    msg("$fraction: importing $l10n_meta");

                    // Get language to assume as current language for fields that are going to be localized
                    // This variable is used by application/lang/ui.php file required by further require_once call
                    $lang = m('lang')->row('`adminSystemUi` = "y"', '`move`')->alias;

                    // Run php file to turn on l10n
                    require_once rif($folder, '$1/') . $l10n_meta;

                // Else flush the status
                } else msg("$fraction: no l10n meta was changed");

                // Collect [lang => file] pairs
                $l10n_data = [];
                foreach ($files as $file)
                    if ($lang = Indi::rexm('~^application/lang/ui/(.*?).php$~', $file, 1))
                        $l10n_data[$lang] = $file;

                // If nothing collected - flush the status
                if (!$l10n_data) msg("$fraction: no l10n data was changed");

                // Else Import titles
                else foreach ($l10n_data as $lang => $file) {

                    // Print where we are
                    msg("$fraction: importing $file");

                    // Backup current language
                    $_lang = ini('lang')->admin;

                    // Spoof current language with given language
                    ini('lang')->admin = $lang;

                    // Execute file, containing php-code for setting up titles for given language
                    require_once rif($folder, '$1/') . $file;

                    // Restore current language
                    ini('lang')->admin = $_lang;
                }

            // Else flush the status
            } else msg("$fraction: no files were changed");
        }
    }

    /**
     * At first, check if current project repo is outdated, and if so do `git pull`
     * Afterwards, check if any of indi-engine package repos are outdated, and if so
     */
    public function updateAction() {

        // If rabbitmq is not enabled - clear log/update.log
        if (!ini()->rabbitmq->enabled) i('', 'w', 'log/update.log');

        // Temporarily set HOME env, if not set
        if (!getenv('HOME') || !is_writable(getenv('HOME'))) putenv('HOME=' . DOC);

        // Check status of system package
        $this->exec(
            'git status',
            ltrim(VDR, '/') . '/system',
            false,
            !!Indi::get()->answer
        );

        // If some changes are there - ask for confirmation to proceed further
        if (!preg_match('~nothing to commit~', $this->msg)) {
            $this->confirm($this->msg);
        }

        // Prompt git username and token to work with private repo
        $this->promptGitUserToken();

        // If custom repo is outdated
        if ($this->isRepoOutdated('custom') === true) {

            // Apply github username and token
            $this->applyGitUserToken();

            // Do git pull and hope composer.lock will be among updated files
            $this->exec('git pull');

            // Strip github username and token
            $this->stripGitUserToken();

            // Do composer install. todo: detect whether composer.lock was really updated
            $this->exec('composer install');
        }

        // Foreach local repo check whether at least one of them is outdated
        $isOutdated = false;
        foreach (ar('system,client') as $repo) {

            // Get last pushed commit if local repo is NOT outdated
            $lastPushedCommit = $this->isRepoOutdated($repo);

            // If local repo is outdated, or if not but composer.lock file is outdated
            if ($lastPushedCommit === true || $this->isLockOutdated($repo, $lastPushedCommit)) {

                // Setup $isOutdated flag and break
                $isOutdated = true; break;
            }
        }

        // If one/both of indi-engine packages is/are outdated,
        // or their mentions inside composer.lock are outdated
        // or composer.lock file is changed but not committed
        if ($isOutdated || $this->exec('git status --porcelain composer.lock')) {

            // Run composer update
            if ($isOutdated) $this->exec('composer update indi-engine/*');

            // Add to commit
            $this->exec('git add composer.lock');

            // Commit
            $this->exec('git commit -F .git/COMMIT_EDITMSG');

            // Insert git username and token in repo url
            $this->applyGitUserToken();

            // Push changes
            $this->exec('git push');

            // Strip git username and token from repo url
            $this->stripGitUserToken();
        }

        // Run migrations, if need
        //$this->_migrate();

        // Flush output printed by last command
        jflush(true, $this->msg);
    }

    /**
     * Backup the whole db as sql dump
     */
    public function backupAction() {
        
        // Check sql/ directory is writable
        if (!is_writable('sql')) jflush(false, 'sql/ directory is not writable');
        
        // Prompt for filename
        $prompt = $this->prompt('Please specify db dump filename to be created in sql/ directory', [[
            'xtype' => 'textfield',
            'name' => 'dump',
            'value' => 'custom-demo.sql.gz'
        ]]);
        
        // Prepare variables
        $user = ini()->db->user;
        $pass = ini()->db->pass;
        $name = ini()->db->name;
        $host = ini()->db->host;
        $dump = $prompt['dump'];

        // Make sure dump name ends with .sql or .sql.gz
        jcheck([
            'dump' => [
                'req' => true,
                'rex' => '~\.(sql|sql\.gz)$~'
            ]
        ], $prompt);

        // Make sure dump name contains alphanumeric and basic punctuation chars
        jcheck([
            'dump' => [
                'req' => true,
                'rex' => '~^[a-zA-Z0-9\.\-]+$~'
            ]
        ], $prompt);

        // Get sql-file name, e.g. with NO '.gz' at the end
        $sql = preg_replace('~\.gz$~', '', $dump);

        // Create dump
        $this->exec("mysqldump -h $host -u $user -p$pass -y $name -r sql/$sql");

        // Gzip dump
        if ($dump === "$sql.gz") $this->exec("gzip sql/$sql");

        // Flush result
        jflush(true, $this->msg ?: 'Done');
    }

    /**
     * Restore the whole db from sql dump
     */
    public function restoreAction() {

        // Prompt for filename
        $prompt = $this->prompt('Please specify db dump filename to be imported from sql/ directory', [[
            'xtype' => 'textfield',
            'name' => 'dump',
            'value' => 'custom-demo.sql.gz'
        ]]);

        // Prepare variables
        $user = ini()->db->user;
        $pass = ini()->db->pass;
        $name = ini()->db->name;
        $host = ini()->db->host;
        $dump = $prompt['dump'];

        // Make sure dump name ends with .sql or .sql.gz
        jcheck([
            'dump' => [
                'req' => true,
                'rex' => '~\.(sql|sql\.gz)$~'
            ]
        ], $prompt);

        // If given dump name is actually an URL
        if (Indi::rexm('url', $dump)) {

            // Flush step
            msg('Downloading dump...');

            // Download dump
            $raw = file_get_contents($dump);

            // Spoof $dump for it to contain just filename with extension
            $dump = $prompt['dump'] = pathinfo($dump, PATHINFO_BASENAME);

            // Save dump in sql/ directory
            file_put_contents("sql/$dump", $raw);
        }

        // Make sure dump name contains alphanumeric and basic punctuation chars
        jcheck([
            'dump' => [
                'rex' => '~^[a-zA-Z0-9\.\-]+$~'
            ]
        ], $prompt);

        // Whether maxwell is enabled
        $maxwell = ini()->rabbitmq->maxwell;

        // If yes
        if ($maxwell) {

            // Flush step
            msg('Disabling maxwell...');

            // Disable maxwell
            `php indi realtime/maxwell/disable`;
        }

        // Flush step
        msg('Importing dump...');

        // Exec mysqldump-command
        $exec = preg_match('~\.gz$~', $dump)
            ? `zcat sql/$dump | mysql -h $host -u $user -p$pass $name`
            : `mysql -h $host -u $user -p$pass $name < sql/$dump`;

        // If maxwell was enabled
        if ($maxwell) {

            // Flush step
            msg('Enabling maxwell back...');

            // Enable maxwell back
            `php indi realtime/maxwell/enable`;
        }

        // Flush result
        jflush(!$exec, $exec ?: 'Done');
    }
    
    /**
     * Create php classes files
     */
    public function phpAction() {

        // PHP class files for entities of fraction:
        // - 'system' - will be created in VDR . '/system',
        // - 'public' -                 in VDR . '/public',
        // - 'custom' -                 in ''
        $repoDirA = [
            'system' => VDR . '/system',
            'public' => VDR . '/public',
            'custom' => ''
        ];

        // Build the dir name, that model's php-file will be created in
        $dir = Indi::dir(DOC . STD . $repoDirA[$this->row->fraction] . '/application/models/');

        // If that dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $dir)) jflush(false, $dir);

        // Get the model name with first letter upper-cased
        $model = ucfirst(t()->row->table);

        // Messages
        $msg = [];

        // Get absolute and relative paths
        $modelAbs = $dir . $model . '.php';
        $modelRel = preg_replace('~^' . preg_quote(DOC) . '/~', '', $modelAbs);

        // If model file already exists
        if (is_file($modelAbs)) {

            // Add msg
            $msg [] = __(I_FILE_EXISTS, $modelRel);

        // Else
        } else {

            // Build template model file name
            $tplModelFn = DOC. STD . VDR . '/system/application/models/{Model}.php';

            // If it is not exists - flush an error, as we have no template for creating a model file
            if (!is_file($tplModelFn)) jflush(false, I_ENTITIES_TPLMDL_404);

            // Get the template contents (source code)
            $emptyModelSc = file_get_contents($tplModelFn);

            // Replace {Model} keyword with an actual model name
            $modelSc = preg_replace(':\{Model\}:', $model, $emptyModelSc);

            // Replace {extends} keyword with an actual parent class name
            $modelSc = preg_replace(':\{extends\}:', $this->row->extends, $modelSc);

            // Put the contents to a model file
            file_put_contents($modelAbs, $modelSc);

            // Chmod
            chmod($modelAbs, 0765);

            // Reload this row in all grids it's currently shown in
            t()->row->realtime('reload');

            // Flush success
            $msg []= __(I_FILE_CREATED, $modelRel);
        }

        // Build the model's own dir name, and try to create it, if it not yet exist
        $modelDir = Indi::dir($dir . '/' . $model . '/');

        // If model's own dir doesn't exist and can't be created - flush an error
        if (!preg_match(Indi::rex('dir'), $modelDir)) jflush(false, $modelDir);

        // Get absolute and relative paths
        $modelRowAbs = $dir . $model . '/Row.php';
        $modelRowRel = preg_replace('~^' . preg_quote(DOC) . '/~', '', $modelRowAbs);

        // If model's row-class file already exists
        if (is_file($modelRowAbs)) {

            // Add msg
            $msg [] = __(I_FILE_EXISTS, $modelRowRel);

        // Else
        } else {

            // Build template model's rowClass file name
            $tplModelRowFn = DOC. STD . VDR . '/system/application/models/{Model}/Row.php';

            // If it is not exists - flush an error, as we have no template for creating a model's rowClass file
            if (!is_file($tplModelRowFn)) jflush(false, I_ENTITIES_TPLMDLROW_404);

            // Get the template contents (source code)
            $tplModelRowSc = file_get_contents($tplModelRowFn);

            // Replace {Model} keyword with an actual model name
            $modelRowSc = preg_replace(':\{Model\}:', $model, $tplModelRowSc);

            // Put the contents to a model's rowClass file
            file_put_contents($modelRowAbs, $modelRowSc);

            // Chmod
            chmod($modelRowAbs, 0765);

            // Flush success
            $msg []= __(I_FILE_CREATED, $modelRowRel);
        }

        // Reload this row in all grids it's currently shown in
        t()->row->realtime('reload');

        // Flush success
        jflush(true, join('<br>', $msg));
    }

    /**
     * Append created(|ByRole|ByUser|At) fields to store info about by whom and when each entry was created
     */
    public function authorAction() {

        // Get current entity's table name
        $table = t()->row->table;

        // Span field
        field($table, 'created', ['title' => I_ENT_AUTHOR_SPAN, 'elementId' => 'span']);

        // Shared config for two fields
        $shared = ['columnTypeId' => 'INT(11)',  'elementId' => 'combo', 'storeRelationAbility' => 'one'];

        // Author role field
        field($table, 'createdByRole', $shared + ['defaultValue' => '<?=admin()->roleId?>', 'title' => I_ENT_AUTHOR_ROLE, 'relation' => 'role']);

        // Author field
        field($table, 'createdByUser', $shared + ['defaultValue' => '<?=admin()->id?>', 'title' => I_ENT_AUTHOR_USER]);

        // Author field depends on author role field
        consider($table, 'createdByUser', 'createdByRole', ['foreign' => 'entityId', 'required' => 'y']);

        // Datetime field
        field($table, 'createdAt', [
            'title' => I_ENT_AUTHOR_TIME, 'columnTypeId' => 'DATETIME', 'elementId' => 'datetime',
            'defaultValue' => '<?=date(\'Y-m-d H:i:s\')?>'
        ]);

        // Flush success
        jflush(true, 'OK');
    }

    /**
     * Create a `toggle` field within given entity
     */
    public function toggleAction() {

        // Foreach selected entity
        foreach (t()->rows as $row) {

            // Create field
            field($row->table, 'toggle', [
                'title' => I_ENT_TOGGLE,
                'columnTypeId' => 'ENUM',
                'elementId' => 'combo',
                'defaultValue' => 'y',
                'relation' => 'enumset',
                'storeRelationAbility' => 'one',
            ]);

            // Create enum option
            enumset($row->table, 'toggle', 'y', ['title' => I_ENT_TOGGLE_Y, 'boxColor' => 'lime', 'move' => '']);
            enumset($row->table, 'toggle', 'n', ['title' => I_ENT_TOGGLE_N, 'boxColor' => 'red' , 'move' => 'y']);
        }

        // Flush success
        jflush(true, 'OK');
    }

    /**
     * 1.Hide default values for `extends` prop, to prevent it from creating a mess in eyes
     * 2.Check php-model file exist, and if yes, check whether it's actual parent class is
     *   as per specified in `extends` prop
     *
     * @param array $data
     */
    public function adjustGridData(&$data) {

        // Foreach data item
        foreach ($data as &$item) {

            // Get php-mode class name
            $php = ucfirst($item['table']);

            // If php-controller file exists for this section
            if (class_exists($php)) {

                // Setup flag
                $item['_system']['php-class'] = true;

                // Get parent class
                $parent = get_parent_class($php);

                // If actual parent class is not as per entity `extends` prop - setup error
                if ($parent != $item['extends']) $item['_system']['php-error'] = sprintf(I_ENT_EXTENDS_OTHER, $parent);
            }
        }
    }
}