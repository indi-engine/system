<?php
class Indi_View {
    /**
     * Path stack for script , helper, and filter directories.
     *
     * @var array
     */
    private $_path = [
        'script' => [],
        'helper' => []
    ];

    /**
     * Script file name to execute
     *
     * @var string
     */
    private $_file = null;

    /**
     * Instances of helper objects.
     *
     * @var array
     */
    private $_helper = [];

    /**
     * Map of helper => class pairs to help in determining helper class from
     * name
     * @var array
     */
    private $_helperLoaded = [];

    /**
     * Map of helper => classfile pairs to aid in determining helper classfile
     * @var array
     */
    private $_helperLoadedDir = [];

    /**
     * Callback for escaping.
     *
     * @var string
     */
    private $_escape = 'htmlspecialchars';

    /**
     * Encoding to use in escaping mechanisms; defaults to utf-8
     * @var string
     */
    private $_encoding = 'UTF-8';

    /**
     * Plugin loaders
     * @var array
     */
    private $_loaders = [];

    /**
     * Plugin types
     * @var array
     */
    private $_loaderTypes = ['helper'];

    /**
     * Constructor.
     *
     * @param array $config Configuration key-value pairs.
     */
    public function __construct($config = [])
    {
        // set inital paths and properties
        $this->setScriptPath(null);

        // base path
        if (array_key_exists('basePath', $config)) {
            $prefix = 'Indi_View';
            if (array_key_exists('basePathPrefix', $config)) {
                $prefix = $config['basePathPrefix'];
            }
            $this->setBasePath($config['basePath'], $prefix);
        }

        // user-defined view script path
        if (array_key_exists('scriptPath', $config)) {
            $this->addScriptPath($config['scriptPath']);
        }

        // user-defined helper path
        if (array_key_exists('helperPath', $config)) {
            if (is_array($config['helperPath'])) {
                foreach ($config['helperPath'] as $prefix => $path) {
                    $this->addHelperPath($path, $prefix);
                }
            } else {
                $prefix = 'Indi_View_Helper';
                if (array_key_exists('helperPathPrefix', $config)) {
                    $prefix = $config['helperPathPrefix'];
                }
                $this->addHelperPath($config['helperPath'], $prefix);
            }
        }
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (preg_match('/^row(set|)$/i', $property) && t(true)) return t()->$property;
        else if (preg_match('/^user$/', $property)) return Indi::user();
    }

    /**
     * Allows testing with empty() and isset() to work inside
     * templates.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        if (preg_match('/^row(set|)$/i', $key)) return isset(t()->$key);

        if ('_' != substr($key, 0, 1)) {
            return isset($this->$key);
        }

        return false;
    }

    /**
     * Directly assigns a variable to the view script.
     *
     * Checks first to ensure that the caller is not attempting to set a
     * protected or private member (by checking for a prefixed underscore); if
     * not, the public member is set; otherwise, an exception is raised.
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     * @throws Exception if an attempt to set a private or protected
     * member is detected
     */
    public function __set($key, $val)
    {
        if (preg_match('/^row(set|)$/i', $key) && t(true)) {
            t()->$key = $val;
            return;
        }

        if ('_' != substr($key, 0, 1)) {
            $this->$key = $val;
            return;
        }

        $e = new Exception('Setting private or protected class members is not allowed');
        throw $e;
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if ('_' != substr($key, 0, 1) && isset($this->$key)) {
            unset($this->$key);
        }
    }

    /**
     * Accesses a helper object from within a script.
     *
     * If the helper class has a 'view' property, sets it with the current view
     * object.
     *
     * @param string $name The helper name.
     * @param array $args The parameters for the helper.
     * @return string The result of the helper output.
     */
    public function __call($name, $args)
    {
        // is the helper already loaded?
        $helper = $this->getHelper($name);

        // call the helper method
        return call_user_func_array(
            [$helper, $name],
            $args
        );
    }

    /**
     * Given a base path, sets the script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/
     *     scripts/
     *     helpers/
     *     filters/
     * </code>
     *
     * @param  string $path
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Indi_View_Abstract
     */
    public function setBasePath($path, $classPrefix = 'Indi_View')
    {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->setScriptPath($path . 'scripts');
        $this->setHelperPath($path . 'helpers', $classPrefix . 'Helper');
        return $this;
    }

    /**
     * Given a base path, add script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/
     *     scripts/
     *     helpers/
     *     filters/
     * </code>
     *
     * @param  string $path
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Indi_View_Abstract
     */
    public function addBasePath($path, $classPrefix = 'Indi_View')
    {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->addScriptPath($path . 'scripts');
        $this->addHelperPath($path . 'helpers', $classPrefix . 'Helper');
        return $this;
    }

    /**
     * Adds to the stack of view script paths in LIFO order.
     *
     * @param string|array The directory (-ies) to add.
     * @return Indi_View_Abstract
     */
    public function addScriptPath($path)
    {
        $this->_addPath('script', $path);
        return $this;
    }

    /**
     * Resets the stack of view script paths.
     *
     * To clear all paths, use Indi_View::setScriptPath(null).
     *
     * @param string|array The directory (-ies) to set as the path.
     * @return Indi_View_Abstract
     */
    public function setScriptPath($path)
    {
        $this->_path['script'] = [];
        $this->_addPath('script', $path);
        return $this;
    }

    /**
     * Retrieve plugin loader for a specific plugin type
     *
     * @param  string $type
     * @return Indi_Loader_PluginLoader
     */
    public function getPluginLoader($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes)) {
            $e = new Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
            throw $e;
        }

        if (!array_key_exists($type, $this->_loaders)) {
            $prefix     = 'Indi_View_';
            $pathPrefix = 'Indi/View/';
            $pType = ucfirst($type);
            switch ($type) {
                case 'filter':
                case 'helper':
                default:
                    $prefix     .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Indi_Loader_PluginLoader([
                        $prefix => $pathPrefix
                    ]);
                    $this->_loaders[$type] = $loader;
                    break;
            }
        }
        return $this->_loaders[$type];
    }

    /**
     * Adds to the stack of helper paths in LIFO order.
     *
     * @param string|array The directory (-ies) to add.
     * @param string $classPrefix Class prefix to use with classes in this
     * directory; defaults to Indi_View_Helper
     * @return Indi_View_Abstract
     */
    public function addHelperPath($path, $classPrefix = 'Indi_View_Helper_')
    {
        return $this->_addPluginPath('helper', $classPrefix, (array) $path);
    }

    /**
     * Resets the stack of helper paths.
     *
     * To clear all paths, use Indi_View::setHelperPath(null).
     *
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix The class prefix to apply to all elements in
     * $path; defaults to Indi_View_Helper
     * @return Indi_View_Abstract
     */
    public function setHelperPath($path, $classPrefix = 'Indi_View_Helper_')
    {
        unset($this->_loaders['helper']);
        return $this->addHelperPath($path, $classPrefix);
    }

    /**
     * Get a helper by name
     *
     * @param  string $name
     * @return object
     */
    public function getHelper($name, $throwExceptions = true)
    {
        return $this->_getPlugin('helper', $name, $throwExceptions);
    }

    /**
     * Assigns variables to the view script via differing strategies.
     *
     * Indi_View::assign('name', $value) assigns a variable called 'name'
     * with the corresponding $value.
     *
     * Indi_View::assign($array) assigns the array keys as variable
     * names (with the corresponding array values).
     *
     * @see    __set()
     * @param  string|array The assignment strategy to use.
     * @param  mixed (Optional) If assigning a named variable, use this
     * as the value.
     * @return Indi_View_Abstract Fluent interface
     * @throws Exception if $spec is neither a string nor an array,
     * or if an attempt to set a private or protected member is detected
     */
    public function assign($spec, $value = null)
    {
        // which strategy to use?
        if (is_string($spec)) {
            // assign by name and value
            if ('_' == substr($spec, 0, 1)) {
                $e = new Exception('Setting private or protected class members is not allowed');
                throw $e;
            }
            $this->$spec = $value;
        } elseif (is_array($spec)) {
            // assign from associative array
            $error = false;
            foreach ($spec as $key => $val) {
                if ('_' == substr($key, 0, 1)) {
                    $error = true;
                    break;
                }
                $this->$key = $val;
            }
            if ($error) {
                $e = new Exception('Setting private or protected class members is not allowed');
                throw $e;
            }
        } else {
            $e = new Exception('assign() expects a string or array, received ' . gettype($spec));
            throw $e;
        }

        return $this;
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        // find the script file name using the parent private method
        $this->_file = $this->_script($name);
        unset($name); // remove $name from local scope

        ob_start();
        include $this->_file;
        return ob_get_clean();
    }

    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
     * {@link $_encoding} setting.
     *
     * @param mixed $var The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var)
    {
        if (in_array($this->_escape, ['htmlspecialchars', 'htmlentities'])) {
            return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_encoding);
        }

        if (1 == func_num_args()) {
            return call_user_func($this->_escape, $var);
        }
        $args = func_get_args();
        return call_user_func_array($this->_escape, $args);
    }

    /**
     * Finds a view script from the available directories.
     *
     * @param $name string The base name of the script.
     * @return void
     */
    protected function _script($name)
    {
        if (preg_match('#\.\.[\\\/]#', $name)) {
            $e = new Exception('Requested scripts may not include parent directory traversal ("../", "..\\" notation)');
            throw $e;
        }

        if (0 == count($this->_path['script'])) {
            $e = new Exception('no view script directory set; unable to determine location for view script');
            throw $e;
        }

        foreach ($this->_path['script'] as $dir) {
            if (is_readable($dir . $name)) {
                return $dir . $name;
            }
        }

        $message = "script '$name' not found in path ("
                 . implode(PATH_SEPARATOR, $this->_path['script'])
                 . ")";
        $e = new Exception($message);
        throw $e;
    }

    public function exists($name) {
        foreach ($this->_path['script'] as $dir) {
            if (is_readable($dir . $name)) {
                return $dir . $name;
            }
        }
        return false;
    }

    /**
     * Adds paths to the path stack in LIFO order.
     *
     * Indi_View::_addPath($type, 'dirname') adds one directory
     * to the path stack.
     *
     * Indi_View::_addPath($type, $array) adds one directory for
     * each array element value.
     *
     * In the case of filter and helper paths, $prefix should be used to
     * specify what class prefix to use with the given path.
     *
     * @param string $type The path type ('script', 'helper', or 'filter').
     * @param string|array $path The path specification.
     * @param string $prefix Class prefix to use with path (helpers and filters
     * only)
     * @return void
     */
    private function _addPath($type, $path, $prefix = null)
    {
        foreach ((array) $path as $dir) {
            // attempt to strip any possible separator and
            // append the system directory separator
            $dir  = rtrim($dir, '/');
            $dir  = rtrim($dir, '\\');
            $dir .= '/';

            switch ($type) {
                case 'script':
                    // add to the top of the stack.
                    array_push($this->_path[$type], $dir);
                    break;
                case 'helper':
                default:
                    // add as array with prefix and dir keys
                    array_push($this->_path[$type], ['prefix' => $prefix, 'dir' => $dir]);
                    break;
            }
        }
    }

    /**
     * Resets the path stack for helpers and filters.
     *
     * @param string $type The path type ('helper' or 'filter').
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix Class prefix to apply to elements of $path
     */
    private function _setPath($type, $path, $classPrefix = null)
    {
        $dir = DIRECTORY_SEPARATOR . ucfirst($type) . DIRECTORY_SEPARATOR;

        switch ($type) {
            case 'script':
                $this->_path[$type] = [dirname(__FILE__) . $dir];
                $this->_addPath($type, $path);
                break;
            case 'filter':
            case 'helper':
            default:
                $this->_path[$type] = [[
                    'prefix' => 'Indi_View_' . ucfirst($type) . '_',
                    'dir'    => dirname(__FILE__) . $dir
                ]];
                $this->_addPath($type, $path, $classPrefix);
                break;
        }
    }

    /**
     * Return all paths for a given path type
     *
     * @param string $type The path type  ('helper', 'filter', 'script')
     * @return array
     */
    public function getPaths($type) {
        return $this->_path[$type];
    }

    /**
     * Remove last path
     *
     * @param $type
     * @return mixed
     */
    public function popPaths($type) {
        array_pop($this->_path[$type]);
        return $this->_path[$type];
    }

    /**
     * Register helper class as loaded
     *
     * @param  string $name
     * @param  string $class
     * @param  string $file path to class file
     * @return void
     */
    private function _setHelperClass($name, $class, $file)
    {
        $this->_helperLoadedDir[$name] = $file;
        $this->_helperLoaded[$name]    = $class;
    }

    /**
     * Add a prefixPath for a plugin type
     *
     * @param  string $type
     * @param  string $classPrefix
     * @param  array $paths
     * @return Indi_View_Abstract
     */
    private function _addPluginPath($type, $classPrefix, array $paths)
    {
        $loader = $this->getPluginLoader($type);
        foreach ($paths as $path) {
            $loader->addPrefixPath($classPrefix, $path);
        }
        return $this;
    }

    /**
     * Get a path to a given plugin class of a given type
     *
     * @param  string $type
     * @param  string $name
     * @return string|false
     */
    private function _getPluginPath($type, $name)
    {
        $loader = $this->getPluginLoader($type);
        if ($loader->isLoaded($name)) {
            return $loader->getClassPath($name);
        }

        try {
            $loader->load($name);
            return $loader->getClassPath($name);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retrieve a plugin object
     *
     * @param  string $type
     * @param  string $name
     * @return object
     */
    private function _getPlugin($type, $name, $throwExceptions = true)
    {
        $name = ucfirst($name);
        switch ($type) {
            case 'helper':
                $storeVar = '_helper';
                $store    = $this->_helper;
                break;
        }
        if (!isset($store[$name])) {
            $class = $this->getPluginLoader($type)->load($name, $throwExceptions);
            if (!$class) return $class;
            $store[$name] = new $class();
            if (method_exists($store[$name], 'setView')) {
                $store[$name]->setView($this);
            }
        }

        $this->$storeVar = $store;
        return $store[$name];
    }

    /**
     * Involve some other view into the rendering process.
     * This is useful when we are in some view, and want to additionally use some other view, by specifying
     * that view using it's filename (without file extension). Note that this function will use caller's view file
     * location as the directory where other view, specified by $name argument, would be tried to be found
     *
     * @param $name
     * @param array $vars
     */
    public function other($name, array $vars = []) {
        extract($vars);
        $file = str_replace('\\', '/', array_shift(array_shift(debug_backtrace(false))));
        include preg_replace('/\/[a-z0-9A-Z]+\.php/', '/' . $name . '.php', $file);
    }

    /**
     * Involve same-action view script located in some other sibling section-directory
     * This is useful when we are in some view, and want to additionally/instead use some other view, by specifying
     * that view's section name/folder/alias. Note that this function will use caller's view file
     * location as the start point within the process of detecting sibling section directory where same view,
     * would be tried to be found. Example: we have 'print' action for 'documents' section. But we also have
     * another section also dealing with documents, but has different name/alias. However, we don't want to
     * create an additional copy of a template file, what would be used to render the printable contents, as we
     * already have that template file, because it was already created for previous section dealing with 'documents'.
     * So, this function will just "redirect" (if we can say that way) the renderer
     *
     * @param $name
     */
    public function same($name) {
        $file = str_replace('\\', '/', array_shift(array_shift(debug_backtrace(false))));
        $rex = '~\/[a-z0-9A-Z]+\/([a-z0-9A-Z]+)\.php~';
        $lang = '-' . Indi::ini('lang')->admin;
        if (!file_exists($tpl = preg_replace($rex, '/' . $name . '/$1-' . Indi::ini('lang')->admin . '.php', $file)))
            $tpl = preg_replace($rex, '/' . $name . '/$1.php', $file);
        include $tpl;
    }
}