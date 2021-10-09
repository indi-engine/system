<?php
class Indi_Uri_Base {

    /**
     * Constructor
     */
    public function __construct() {

        // Include l10n constants
        foreach (ar('www,coref,core') as $fraction)
            foreach (array('', '/admin') as $_)
                if (file_exists($file = DOC . STD . '/'. $fraction . '/application/lang' . $_ . '/' . Indi::ini('lang')->{trim($_, '/') ?: 'front'} . '.php'))
                    include_once($file);

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
        if ($uri) $_SERVER['REQUEST_URI'] = $uri;

        // If project located in some subfolder of $_SERVER['DOCUMENT_ROOT'] instead of directly in it
        // we strip mention of that subfolder from $_SERVER['REQUEST_URI']
        if (STD) $_SERVER['REQUEST_URI'] = preg_replace('!^' . STD . '!', '', $_SERVER['REQUEST_URI']);

        // If 'cms-only' mode is turned on, we prepend $_SERVER['REQUEST_URI'] with '/admin'
        if (COM) $_SERVER['REQUEST_URI'] = '/admin' . $_SERVER['REQUEST_URI'];

        // Build the full url by prepending protocol and hostname, and parse it by parse_url() function
        $uri = parse_url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        // Trim '/' from 'path' item, got by parse_url() usage, and explode it by '/'
        $uri = explode('/', trim($uri['path'], '/'));

        // Set default params
        $this->module = 'front';
        $this->section = 'index';
        $this->action = 'index';

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
                $this->{$uri[$i]} = $uri[$i + 1];
                $i++;
            }
    }

    /**
     * Dispatch the current uri. If $uri argument is given,
     * the current uri will be replaced with given, and then dispatched
     *
     * @param string $uri
     * @param array $args
     */
    public function dispatch($uri = '', $args = array()){

        // If $uri argument is given - parse it
        if ($uri) $this->parse($uri);

        // Do pre-dispatch operations
        $this->preDispatch();

        // Redirect to uri, that ends with trailing slash, if current uri end with no slash
        $this->trailingSlash();

        // If section name is not valid - throw an error message
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', Indi::uri('section'))) die(I_URI_ERROR_SECTION_FORMAT);

        // Build the controller class name
        $controllerClass = 'Admin_' . ucfirst(Indi::uri()->section) . 'Controller';

        // If such controller class does not exist
        if (!class_exists($controllerClass)) {

            // Try to find
            $sectionR = Indi::model('Section')->fetchRow('`alias` = "' . Indi::uri()->section . '"');

            // If section was found, and it has non-empty `extends` property - set $controllerParentClass as the value
            // of that property, or set it as 'Project_Controller_Admin'
            $controllerParentClass = $sectionR->extendsPhp ?: $sectionR->extends ?: 'Project_Controller_Admin';

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
     * Do pre-dispatch operations
     */
    public function preDispatch() {

        // Perform a 301 redirection if current domain name starts with 'www.'
        $this->no3w();

        // Set cookie domain and path
        $this->setCookieDomain();

        // If 'Indi-Auth' header given - use it's value as session id
        if (!session_id()) if ($id = $_COOKIE['PHPSESSID']) session_id($id);

        // Start session
        session_start();

        // Set current language
        @include_once(DOC . STD . '/core/application/lang/admin/' . Indi::ini('lang')->admin . '.php');
        @include_once(DOC . STD . '/www/application/lang/admin/' . Indi::ini('lang')->admin . '.php');
    }

    /**
     * Set cookie domain and path
     */
    public function setCookieDomain(){
        
        // Detect domain
        $domainA = explode(' ', Indi::ini()->general->domain);
        foreach ($domainA as $domainI) 
            if (preg_match('/' . preg_quote($domainI) . '$/', $_SERVER['HTTP_HOST']))
                $domain = Indi::ini('general')->domain = $domainI;

        // If session is already active - prevent 'PHP Warning: ini_set(): A session is active. You cannot change the session module's ini settings at this time' error msg
        if (session_id()) return;

        // Set cookie domain and path
        ini_set('session.cookie_domain', (preg_match('/^[0-9\.]+$/', $domain) ? '' : '.') . $domain);

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
        if (preg_match('/^www\./', $_SERVER['HTTP_HOST'])) {

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
        if ($_SERVER['REQEST_URI'] != '/' && !preg_match('~/$~', $_SERVER['REQUEST_URI']) && !preg_match('/\?/', $_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

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
}