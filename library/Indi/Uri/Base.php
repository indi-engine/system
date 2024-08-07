<?php
#[\AllowDynamicProperties]
class Indi_Uri_Base {

    /**
     * Environment things are stored here under href-keys
     *
     * @see response()
     * @var array
     */
    public static $stack = [];

    /**
     * Constructor
     */
    public function __construct() {

        // Parse the existing $_SERVER['REQUEST_URI']
        $this->parse();
    }

    /**
     * Parse the $uri argument, or $_SERVER['REQUEST_URI'], if $uri argument is empty
     *
     * @param string $uri
     */
    public function parse($uri = '') {

        // Clear all current uri params
        $this->clear();

        // If $uri argument is given, setup $_SERVER['REQUEST_URI'] as $uri argument
        if ($uri) $_SERVER['REQUEST_URI']
            = (COM ? '' : '/admin')
            . '/' . ltrim($uri, '/')
            . rif(!preg_match('~\?~', $uri), '/');

        // If request url starts with double slash - flush failure
        if (preg_match('~^//~', $_SERVER['REQUEST_URI'])) jflush(false, I_URI_ERROR_SECTION_FORMAT);

        // If project located in some subfolder of $_SERVER['DOCUMENT_ROOT'] instead of directly in it
        // we strip mention of that subfolder from $_SERVER['REQUEST_URI']
        if (STD) $_SERVER['REQUEST_URI'] = preg_replace('!^' . STD . '!', '', $_SERVER['REQUEST_URI']);

        // If 'cms-only' mode is turned on, we prepend $_SERVER['REQUEST_URI'] with '/admin'
        if (COM) $_SERVER['REQUEST_URI'] = '/admin' . ($_SERVER['REQUEST_URI'] ?? null);

        // Extract path component from request uri
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Trim '/' from 'path' item, got by parse_url() usage, and explode it by '/'
        $uri = explode('/', trim($uri ?? '', '/'));

        // Set default params
        $this->module = 'front';
        $this->section = 'index';
        $this->action = 'index';
        $this->command = false;

        // If URI is '/' - return
        if (!$uri[0]) return;

        // If first chunk of $uri is 'admin', we set 'module' param as 'admin' and drop that chunk from $uri
        if ($uri[0] == 'admin') {
            $this->module = $uri[0];
            array_shift($uri);
        }

        // Check all uri parts for format validity
        for ($i = 0; $i < count($uri); $i++) if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]*$/', urldecode($uri[$i]))) {

            // Flush failure
            jflush(false, I_URI_ERROR_CHUNK_FORMAT);
        }

        // Setup all other params
        for ($i = 0; $i < count($uri); $i++)

            // Setup section
            if ($i == 0) $this->section = $uri[$i];

            // Setup action
            else if ($i == 1) $this->action = $uri[$i];

            // Setup all other params
            else if (count($uri) > $i && strlen($uri[$i])) {

                // Shortcuts
                $param = $uri[$i]; $value = $uri[$i + 1] ?? null;

                // Do setup
                $this->$param = $value;

                // Setup command
                if ($param && !$value) $this->command = $param;

                // Increment $i
                $i++;
            }

        // Return instance itself
        return $this;
    }

    /**
     * Dispatch the current uri. If $uri argument is given,
     * the current uri will be replaced with given, and then dispatched
     *
     * @param string $uri
     * @param array $args
     */
    public function dispatch($uri = '', $args = []){

        // If $uri argument is given - parse it
        if ($uri) $this->parse($uri);

        // Do pre-dispatch operations
        $this->preDispatch();

        // Redirect to uri, that ends with trailing slash, if current uri end with no slash
        $this->trailingSlash();

        // If section name is not valid - throw an error message
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', uri('section'))) die(I_URI_ERROR_SECTION_FORMAT);

        // Build the controller class name
        $controllerClass = 'Admin_' . ucfirst(uri()->section) . 'Controller';

        // If such controller class does not exist
        if (!class_exists($controllerClass)) {

            // Try to find
            $sectionR = m('Section')->row('`alias` = "' . uri()->section . '"');

            // If section was found, and it has non-empty `extends` property - set $controllerParentClass as the value
            // of that property, or set it as 'Project_Controller_Admin'
            $controllerParentClass = $sectionR->extendsPhp ?? 'Project_Controller_Admin';

            // If such controller parent class does not exist - set it as 'Indi_Controller_Admin' by default
            if (!class_exists($controllerParentClass)) $controllerParentClass = 'Indi_Controller_Admin';

            // Auto-declare controller class
            eval('class ' . ucfirst($controllerClass) . ' extends ' . $controllerParentClass . '{}');
        }

        // Get the controller instance
        $controller = new $controllerClass();

        // Dispatch
        $controller->dispatch($args);
    }

    /**
     * Make sub-request and get sub-response
     *
     * @param $uri
     */
    public function response($uri) {

        // Backup current uri's environment things
        self::$stack[$_SERVER['REQUEST_URI']] = [
            'uri' => Indi::registry('uri'),
            'trail' => [
                'instance' => Indi::registry('trail'),
                'items' => Indi_Trail_Admin::$items,
                'controller' => Indi_Trail_Admin::$controller,
            ],
            'errors' => Indi::$errors
        ];

        // Reset errors
        Indi::$errors = [];

        // Dispatch sub-request and get sub-response
        ob_start(); $this->dispatch($uri); $out = ob_get_clean();

        // Strip errors from $out
        foreach (Indi::$errors as $error)
            $out = preg_replace('~^' . preg_quote($error, '~') . '~', '', $out);

        // Get prev environment key
        $prev = array_key_last(self::$stack);

        // Restore prev enviromnent
        $_SERVER['REQUEST_URI'] = $prev;
        Indi::registry('uri',           self::$stack[$prev]['uri']);
        Indi::registry('trail',         self::$stack[$prev]['trail']['instance']);
        Indi_Trail_Admin::$items =      self::$stack[$prev]['trail']['items'];
        Indi_Trail_Admin::$controller = self::$stack[$prev]['trail']['controller'];
        Indi::$errors =                 self::$stack[$prev]['errors'];

        // Unset from stack as now it is the current environment again
        unset(self::$stack[$prev]);

        // Return sub-response
        return $out;
    }

    /**
     * Do pre-dispatch operations
     */
    public function preDispatch() {

        // Perform a 301 redirection if current domain name starts with 'www.'
        $this->no3w();

        // Set cookie domain and path
        $this->setCookieDomain();

        // If 'Indi-Auth' header given - use it's value as session id
        if (!session_id()) if ($id = $_COOKIE['PHPSESSID'] ?? null) session_id($id);

        // Start session, if need
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Load system language constants
        if (file_exists($system = DOC . STD . VDR . '/system/application/lang/admin/' . ini('lang')->admin . '.php'))
            include_once $system;

        // Load custom language constants
        if (file_exists($custom = DOC . STD . '/application/lang/admin/' . ini('lang')->admin . '.php'))
            include_once $custom;
    }

    /**
     * Set cookie domain and path
     */
    public function setCookieDomain(){
        
        // Get current host name without port number
        $hostname = $_SERVER['SERVER_NAME'] ?? '';

        // Detect domain
        $domain = '';
        $domainA = explode(' ', ini()->general->domain);
        foreach ($domainA as $domainI) 
            if (preg_match('/' . preg_quote($domainI) . '$/', $hostname))
                $domain = ini('general')->domain = $domainI;

        // If session is already active - prevent 'PHP Warning: ini_set(): A session is active. You cannot change the session module's ini settings at this time' error msg
        if (session_id()) return;

        // Set cookie domain and path
        ini_set('session.cookie_domain', $hostname == 'localhost' || Indi::rexm('ipv4', $hostname)
            ? $hostname
            : (preg_match('/^[0-9\.]+$/', $domain) ? '' : '.') . $domain);

        // If project runs not from document root, but from some
        // subfolder of document root - setup an appropriate cookie path
        if (STD) ini_set('session.cookie_path', STD);
    }

    /**
     * Perform a 301 redirection if current domain name starts with 'www.',
     * so there will be a redirect to same domain, but without 'www.'
     */
    public function no3w() {

        // If current domain name starts with 'www.'
        if (preg_match('/^www\./', $_SERVER['SERVER_NAME'] ?? '')) {

            // Setup 301 header
            header('HTTP/1.1 301 Moved Permanently');

            // Redirect and die
            header('Location: http://' . preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI']); die();
        }
    }

    /**
     * Redirect to uri, that ends with trailing slash, if current uri end with no slash
     */
    public function trailingSlash() {

        // If current uri end with no slash
        if (($_SERVER['REQEST_URI'] ?? null) != '/'
            && !preg_match('~/$~', $_SERVER['REQUEST_URI'])
            && !preg_match('/\?/', $_SERVER['REQUEST_URI'])
            && $_SERVER['REQUEST_METHOD'] == 'GET') {

            // Setup 301 header
            header('HTTP/1.1 301 Moved Permanently');

            // Redirect and die
            header('Location: ' . $_SERVER['REQUEST_URI'] . '/'); die();
        }
    }

    /**
     * Setup new value for $_SERVER['REQUEST_URI'] variable, based on current object's internal properties, such as
     * module, section, action and other param => value pairs
     */
    public function build() {

        // Setup request uri as value of STD constant, initially
        $_SERVER['REQUEST_URI'] = STD;

        // Append all other parts
        foreach ($this as $key => $value) {

            // If $key is 'staticpageAdditionalWHERE' - ignore it, as it does not relate to request uri
            if ($key == 'staticpageAdditionalWHERE') continue;

            // Else if $key is 'module'
            else if ($key == 'module')

                // If module is 'front' or is 'admin', but cms only mode is enabled - ignore it
                if ($value == 'front' || COM) continue; else $_SERVER['REQUEST_URI'] .= '/' . $value;

            // Else if $key is 'section' - append section
            else if ($key == 'section') $_SERVER['REQUEST_URI'] .= '/' . $value;

            // Else if $key is 'action' - append section
            else if ($key == 'action') $_SERVER['REQUEST_URI'] .= '/'. $value;

            // Else if $key is some another key, append both key name and value
            else $_SERVER['REQUEST_URI'] .= '/' . $key . '/' . $value;
        }

        // Append trailing slash
        $_SERVER['REQUEST_URI'] .= '/';
    }

    /**
     * Clear all current uri params
     */
    public function clear() {
        foreach ($this as $prop => $value) unset($this->$prop);
    }

    /**
     * Get an associative array, containing all uri params
     *
     * @return array
     */
    public function toArray() {
        return (array) $this;
    }

    /**
     * Prevent 'Undefined property' errors
     *
     * @param $prop
     * @return null
     */
    public function __get($prop) {
        return '';
    }
}