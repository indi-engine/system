<?php
class Admin_EntitiesController extends Indi_Controller_Admin_Exportable {

    /**
     * Flag indicating whether db backup steps should be skipped during update
     *
     * @var bool
     */
    protected $nobackup = false;

    /**
     * Prompt for auth-string to be able to update current repository using `git pull` command
     */
    public function promptGitUserToken() {

        // Prompt for valid Google Cloud Translate API key
        $prompt = $this->prompt(im([
            'Please specify your GitHub token having read-write access to the current repo.',
            'Write access might be needed to push composer.lock file if changed by `composer update`',
            'command, and to upload database backups before and after migrations, unless skipped.'
        ], '<br>'), [[
            'xtype'      => 'textfield',
            'emptyText'  => 'Please specify GitHub token here..',
            'inputType'  => 'password',
            'allowBlank' => false,
            //'regex' => '/' . $this->git['auth']['rex']['self'] . '/',
            'width' => 250,
            'name' => $name = 'token'
        ], [
            'xtype' => 'checkbox',
            'name'  => 'nobackup',
            'boxLabel' => 'Skip db backup steps before and after migrations, if any'
        ]]);

        // Check prompt data
        jcheck([
            $name => [
                'rex' => '~^' . $this->git['auth']['rex']['self'] . '$~'
            ]
        ], $prompt);

        // Assign as prop to be further accessible
        $this->git['auth']['value'] = $prompt[$name];

        // Assing nobackup-flag to be further accessible
        $this->nobackup = $prompt['nobackup'];
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

        // Get instance type
        $type = param('instance-type')->cfgValue;

        // Flag indicating that we had already backed up our database both locally and on github.
        // Initial value for now is `false`, because there might be nothing to migrate, so no backup needed.
        // In case if further script detects there is something to be migrated - backup is made and flag is set to `true`,
        // so that backup is made once per $this->__migrate() call. As a result of a backup the file
        // named as 'custom-$type.backup-before-migrate.sql.gz' appears locally (in sql/) and on github
        // and is kept there until overwritten
        $backed = false;

        // Flag indicating we have successfully migrated so that we need to upload fresh db dump on github
        $github = false;

        // Repos to be checked for pending migrations
        $repoA = [
            'system' => [
                'folder' => ltrim(VDR, '/') . '/system',
                'commit' => param('migration-commit-system')->cfgValue ?? 'ee31c122a5417ebdc05c5fadaad8ef96e2831b2d',
                'detect' => 'library/Indi/Controller/Migrate.php'
            ],
            'custom' => [
                'folder' => '',
                'commit' => param('migration-commit-custom')->cfgValue ?? $this->exec('git rev-parse HEAD'),
                'detect' => 'application/controllers/admin/MigrateController.php',
            ]
        ];

        // Foreach repo
        foreach ($repoA as $fraction => $repo) {

            // Shortcuts
            $folder = $repo['folder'];
            $commit = $repo['commit'];
            $detect = $repo['detect'];

            // If it's not really a commit but is a timestamp in 'Wed Nov 22 17:56:41 2023 +0000' format
            if (preg_match('~[:+]~', $timestamp = $commit)) {

                // Get commit info by timestamp, as timestamps are kept the same even if
                // commit history rewritten due to removal of previous versions of database dumps
                $commit = $this->exec("git log --since=\"$timestamp\" --until=\"$timestamp\"");

                // If we're unable to find commit that was made at such timestamp
                if (!$commit || !($commit = Indi::rexm("~commit ([a-f0-9]+)~", $commit, 1))) {
                    jflush(false, "No commit found by timestamp $timestamp");
                }
            }

            // If we're going to check custom repo migrations - prevent exit on failure for case
            // when commit from 'migration-commit-custom' param does not exist in repo, as for
            // custom repo this might happen due to that repo was created based on template repo,
            // so commit's history of template repo where commit from 'migration-commit-custom'
            // do really exists - was not included in custom repo, and in that case we'll just
            // rely on the very first commit of the custom repo
            $noExitOnFailureIfMsgContains = $fraction === 'custom' ? 'fatal: bad object' : '';

            // Get files changed since last commit which we did migrate at
            $files = $this->exec("git diff --name-only $commit", $folder, $noExitOnFailureIfMsgContains);

            // If commit does not exist
            if ($files === false) {

                // Get the very first commit
                $commit = $this->exec("git rev-list --max-parents=0 HEAD");

                // Get files changed since the very first commit, if any
                $files = $this->exec("git diff --name-only $commit", $folder);
            }

            // If some files were changed
            if ($files) {

                // Convert files list into an array
                $files = explode("<br>", $files);

                // If migrations-file was changed
                if (in($detect, $files)) {

                    // Get what's changed
                    $diff = $this->exec("git diff $commit -- $detect", $folder);

                    // If new migration-actions detected
                    if ($actions = Indi::rexma('~<br>\+\s+public function ([a-zA-Z_0-9]+)Action\(~', $diff)[1] ?? 0) {

                        // Reverse list of migrations, as new ones are added at the top of migrations controller class
                        // just to prevent developer to scrolling to the bottom of file having thousands of lines
                        $actions = array_reverse($actions);

                        // If there is really something to migrate
                        if ($actions && !$backed && !$this->nobackup) {

                            // Do backup
                            $this->backupAction([
                                'dump' => "custom-$type.backup-before-migrate.sql.gz",
                                'token' => $this->git['auth']['value']
                            ]);

                            // Setup $backup flag to true
                            $backed = true;
                        }

                        // Foreach migration
                        foreach ($actions as $action) {

                            // Run
                            $this->exec("php indi migrate/$action");

                            // Reload db meta
                            db(true);
                        }

                        // Setup $github flag to true
                        if ($actions) $github = true;

                    // Else flush the status
                    } else msg("$fraction: no new migrations yet");

                // Else flush the status
                } else msg("$fraction: migrations file not changed");

                // If php-file responsible for turning On l10n for certain field - was changed
                if (in($l10n_meta = 'application/lang/ui.php', $files)) {

                    // If there is really something to migrate
                    if (!$backed && !$this->nobackup) {

                        // Do backup
                        $this->backupAction([
                            'dump' => "custom-$type.backup-before-migrate.sql.gz",
                            'token' => $this->git['auth']['value']
                        ]);

                        // Setup $backup flag to true
                        $backed = true;
                    }

                    // Print where we are
                    msg("$fraction: importing $l10n_meta");

                    // Get language to assume as current language for fields that are going to be localized
                    // This variable is used by application/lang/ui.php file required by further require_once call
                    $lang = m('lang')->row('`adminSystemUi` = "y"', '`move`')->alias;

                    // Run php file to turn on l10n
                    require_once rif($folder, '$1/') . $l10n_meta;

                    // Todo: make fieldToggleL10n calls syncronous
                    sleep(10);

                    // Setup $github flag to true
                    $github = true;

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

                    // If there is really something to migrate
                    if (!$backed && !$this->nobackup) {

                        // Do backup
                        $this->backupAction([
                            'dump' => "custom-$type.backup-before-migrate.sql.gz",
                            'token' => $this->git['auth']['value']
                        ]);

                        // Setup $backup flag to true
                        $backed = true;
                    }

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

                    // Setup $github flag to true
                    $github = true;
                }

                // Get current commit
                $commit = $this->exec('git rev-parse HEAD', $folder);

                // Update global-level config-field's value
                param("migration-commit-$fraction", $commit);

            // Else flush the status
            } else msg("$fraction: no files were changed");
        }

        // If $github flag is true - export db dump and save as repo's latest-release asset
        if ($github && !$this->nobackup) {

            // Get instance type
            $type = param('instance-type')->cfgValue;

            // If it's not in the whitelist - skip
            if (!in($type, 'demo,prod,bare')) return;

            // Do backup
            $this->backupAction($prompt = [
                'dump' => "custom-$type.sql.gz",
                'token' => $this->git['auth']['value']
            ]);
        }
    }

    /**
     * At first, check if current project repo is outdated, and if so do `git pull`
     * Afterwards, check if any of indi-engine package repos are outdated, and if so
     */
    public function updateAction() {

        // Respect demo mode
        Indi::demo();

        // If rabbitmq is not enabled - clear log/update.log
        if (!ini()->rabbitmq->enabled) i('', 'w', 'log/update.log');

        // Temporarily set HOME env, if not set
        if (!getenv('HOME') || !is_writable(getenv('HOME'))) putenv('HOME=' . DOC);

        // Check status of system package
        $this->exec(
            'git status',
            ltrim(VDR, '/') . '/system',
            false,
            !!(Indi::get()->answer ?? null)
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
        $isOutdated = [];
        foreach (ar('system,client') as $repo) {

            // Get last pushed commit if local repo is NOT outdated
            $lastPushedCommit = $this->isRepoOutdated($repo);

            // If local repo is outdated, or if not but composer.lock file is outdated
            if ($lastPushedCommit === true || $this->isLockOutdated($repo, $lastPushedCommit)) {

                // Setup $isOutdated flag and break
                $isOutdated[$repo] = true; break;
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

            // Create commit message filename if not exists
            if (!file_exists($m = '.git/COMMIT_EDITMSG')) $this->exec("touch $m");

            // Commit
            $this->exec("git commit -F $m");

            // Push changes
            $this->applyGitUserToken();
            $this->exec('git push');
            $this->stripGitUserToken();
        }

        // If we've updated system-package - check whether some files from system-package's custom/ dir
        // mostly responsible for docker-compose setup - should be replicated to the project root, so that
        // same files already existing there to be updated
        // todo: remove ' || true' once all instances updated
        if (isset($isOutdated['system']) || true) $this->_custom();

        // Run migrations, if need
        $this->_migrate();

        // Flush 'Done' or command history in case of rabbitmq is not enabled
        ini()->rabbitmq->enabled
            ? (msg('Done') || jflush(true))
            : jtextarea(true, file_get_contents('log/update.log'));
    }

    /**
     * Check whether some files from system-package's custom/ dir (mostly responsible for docker-compose setup)
     * - should be replicated to the project root, so that same files already existing there to be updated
     */
    private function _custom() {

        // System-package's dir shortcut
        $system = ltrim(VDR, '/') . '/system';

        // Files to be commited
        $commit = [];

        // Foreach file to be copied from system to custom
        foreach (rglob(($prefix = "$system/custom/") . "*") as $source) {

            // Get target path
            $target = str_replace($prefix, '', $source);

            // Prevent some files from being copied. todo: make this configurable
            if (in($target, ['.env.dist'])) continue;

            // If source path is a file
            if (is_file($source)) {

                // If there is no corresponding target file yet, or there is but contents differs
                if (!is_file($target) || file_get_contents($source) !== file_get_contents($target) ) {

                    // Copy file
                    copy($source, $target);

                    // Add this file to the list of to be committed
                    $this->exec("git add $target");

                    // Add executable right if it's *.sh file
                    if (preg_match('~\.sh$~', $target)) $this->exec("chmod +x $target");

                    // Append to the list of files to be commited
                    $commit []= $target;
                }

            // Else if it's a dir
            } else if (is_dir($source)) {

                // If there is no corresponding target dir
                if (!is_dir($target)) {

                    // Create it
                    mkdir($target);
                }
            }
        }

        // If there is something to commit
        if ($commit) {

            // Do commit
            $this->exec('git commit -m "docker-compose setup updated"');

            // Push changes
            $this->applyGitUserToken();
            $this->exec('git push');
            $this->stripGitUserToken();
        }
    }

    /**
     * Backup the whole db as sql dump
     */
    public function backupAction($prompt = null) {

        // Respect demo mode
        Indi::demo();

        // Check sql/ directory is writable
        if (!is_writable('sql')) jflush(false, 'sql/ directory is not writable');

        // Temporarily set HOME env, if not set or not writable
        if (!getenv('HOME') || !is_writable(getenv('HOME'))) putenv('HOME=' . DOC);

        // Get instance type
        $type = param('instance-type')->cfgValue;

        // Prompt for filename
        $prompt = $prompt ?? $this->prompt('Please specify db dump filename to be <strong>exported</strong> in sql/ directory', [[
            'xtype' => 'textfield',
            'name' => 'dump',
            'value' => "custom-$type.sql.gz"
        ], [
            'xtype' => 'textfield',
            'inputType' => 'password',
            'emptyText' => 'Specify token if you need to upload this asset on github',
            'name' => 'token'
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
        $this->exec("mysqldump --single-transaction -h $host -u $user -p$pass -y $name -r sql/$sql");

        // Gzip dump
        if ($dump === "$sql.gz") $this->exec("gzip -f sql/$sql");

        // If github token is given
        if ($prompt['token']) {

            // Setup GH_TOKEN variable required for gh-command
            putenv('GH_TOKEN=' . $prompt['token']);

            // Release tag
            $release = 'latest';

            // If we have a release
            $this->exec("gh release list | awk '$3 == \"$release\"'")
                ? $this->exec("gh release upload $release 'sql/$dump' --clobber")
                : $this->exec("gh release create $release 'sql/$dump'");
        }

        // Flush result
        if (!func_num_args()) jflush(true, $this->msg ?: 'Done');
    }

    /**
     * Restore the whole db from sql dump
     */
    public function restoreAction() {

        // Respect demo mode
        Indi::demo();

        // Get instance type
        $type = param('instance-type')->cfgValue;

        // Prompt for filename
        $prompt = $this->prompt('Please specify db dump filename to be <strong>imported</strong> from sql/ directory', [[
            'xtype' => 'textfield',
            'name' => 'dump',
            'value' => "custom-$type.sql.gz"
        ], [
            'xtype' => 'textfield',
            'inputType' => 'password',
            'emptyText' => 'Specify token if you want to download that dump from github',
            'name' => 'token'
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

        // Else if token is given
        } else if ($prompt['token']) {

            // Setup GH_TOKEN variable required for gh-command
            putenv('GH_TOKEN=' . $prompt['token']);

            // Release tag
            $release = 'latest';

            // Download from latest release assets into sql/ directory
            $this->exec("gh release download $release -D sql -p '$dump' --clobber");
        }

        // Make sure dump name contains alphanumeric and basic punctuation chars only
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

        // Respect demo mode
        Indi::demo();

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

        // Respect demo mode
        Indi::demo();

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

        // Respect demo mode
        Indi::demo();

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

        // Dirs dict by entity fraction
        $dir = [
            'system' => VDR . '/system',
            'public' => VDR . '/public',
            'custom' => ''
        ];

        // Foreach data item
        foreach ($data as &$item) {

            // Get php-model class name
            $cls =  ucfirst($item['table']);

            // Get php-model class file
            $phpA = [DIR . $dir[$item['fraction']] . "/application/models/$cls.php"];

            // If php-model class file exists for this entity
            if ($item['_system']['php-class'] = file_exists($phpA[0])) {

                // Add row-class file, if exists
                if (file_exists($row = DIR . $dir[$item['fraction']] . "/application/models/$cls/Row.php"))
                    $phpA []= $row;

                // Add rowset-class file, if exists
                if (file_exists($rowset = DIR . $dir[$item['fraction']] . "/application/models/$cls/Rowset.php"))
                    $phpA []= $rowset;

                // Convert paths to be relative rather than absolute
                foreach ($phpA as $idx => $php) {
                    $phpA[$idx] = preg_replace('~^' . preg_quote(DIR). '/~', '', $php);
                }

                // Setup class name
                $item['_system']['php-class'] = im($phpA, "&lt;br&gt;");

                // Get parent class
                $parent = get_parent_class($cls);

                // If actual parent class is not as per entity `extends` prop - setup error
                if ($parent != $item['extends']) $item['_system']['php-error'] = sprintf(I_ENT_EXTENDS_OTHER, $parent);
            }
        }
    }

    /**
     * Append extends-prop to the list of columns to prepare grid data for,
     * because $this->adjustGridData() rely on that
     *
     * @return mixed|void
     */
    public function affected4grid() {

        // Call parent
        $cols = $this->callParent();

        // Append extends-prop
        $cols []= 'extends';

        // Return
        return $cols;
    }

    /**
     * Build an new app from scratch or keep evolving the current app - with AI
     */
    public function buildAction() {

        // Prompt for app description, build type and AI model version
        $a = $_SERVER['ai'] ??= ai()->dialog($this);

        // Run in background
        $this->detach();

        // Call scratch() or improve() method depending of purpose
        if ($a['purpose'] === 'scratch') {
            ai()->scratch($a['prompt'], $a['model']);
        } else {
            ai()->improve($a['prompt'], $a['model']);
        }

        // Flush success
        jflush(true);
    }
}