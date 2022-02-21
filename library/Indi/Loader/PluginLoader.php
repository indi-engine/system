<?php
class Indi_Loader_PluginLoader
{
    /**
     * Instance loaded plugin paths
     *
     * @var array
     */
    protected $_loadedPluginPaths = [];

    /**
     * Instance loaded plugins
     *
     * @var array
     */
    protected $_loadedPlugins = [];

    /**
     * Instance registry property
     *
     * @var array
     */
    protected $_prefixToPaths = [];

    /**
     * Statically loaded plugin path mappings
     *
     * @var array
     */
    protected static $_staticLoadedPluginPaths = [];

    /**
     * Statically loaded plugins
     *
     * @var array
     */
    protected static $_staticLoadedPlugins = [];

    /**
     * Static registry property
     *
     * @var array
     */
    protected static $_staticPrefixToPaths = [];

    /**
     * Whether to use a statically named registry for loading plugins
     *
     * @var string|null
     */
    protected $_useStaticRegistry = null;

    /**
     * Constructor
     *
     * @param array $prefixToPaths
     * @param string $staticRegistryName OPTIONAL
     */
    public function __construct(Array $prefixToPaths = [], $staticRegistryName = null)
    {
        if (is_string($staticRegistryName) && !empty($staticRegistryName)) {
            $this->_useStaticRegistry = $staticRegistryName;
            if(!isset(self::$_staticPrefixToPaths[$staticRegistryName])) {
                self::$_staticPrefixToPaths[$staticRegistryName] = [];
            }
            if(!isset(self::$_staticLoadedPlugins[$staticRegistryName])) {
                self::$_staticLoadedPlugins[$staticRegistryName] = [];
            }
        }

        foreach ($prefixToPaths as $prefix => $path) {
            $this->addPrefixPath($prefix, $path);
        }
    }

    /**
     * Format prefix for internal use
     *
     * @param  string $prefix
     * @return string
     */
    protected function _formatPrefix($prefix)
    {
        if($prefix == "") {
            return $prefix;
        }

        $last = strlen($prefix) - 1;
        if ($prefix{$last} == '\\') {
            return $prefix;
        }

        return rtrim($prefix, '_') . '_';
    }

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return Indi_Loader_PluginLoader
     */
    public function addPrefixPath($prefix, $path)
    {
        if (!is_string($prefix) || !is_string($path)) {
            require_once 'Exception.php';
            throw new Exception('Indi_Loader_PluginLoader::addPrefixPath() method only takes strings for prefix and path.');
        }

        $prefix = $this->_formatPrefix($prefix);
        $path   = rtrim($path, '/\\') . '/';

        if ($this->_useStaticRegistry) {
            self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix][] = $path;
        } else {
            if (!isset($this->_prefixToPaths[$prefix])) {
                $this->_prefixToPaths[$prefix] = [];
            }
            if (!in_array($path, $this->_prefixToPaths[$prefix])) {
                $this->_prefixToPaths[$prefix][] = $path;
            }
        }
        return $this;
    }

    /**
     * Get path stack
     *
     * @param  string $prefix
     * @return false|array False if prefix does not exist, array otherwise
     */
    public function getPaths($prefix = null)
    {
        if ((null !== $prefix) && is_string($prefix)) {
            $prefix = $this->_formatPrefix($prefix);
            if ($this->_useStaticRegistry) {
                if (isset(self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix])) {
                    return self::$_staticPrefixToPaths[$this->_useStaticRegistry][$prefix];
                }

                return false;
            }

            if (isset($this->_prefixToPaths[$prefix])) {
                return $this->_prefixToPaths[$prefix];
            }

            return false;
        }

        if ($this->_useStaticRegistry) {
            return self::$_staticPrefixToPaths[$this->_useStaticRegistry];
        }

        return $this->_prefixToPaths;
    }

    /**
     * Normalize plugin name
     *
     * @param  string $name
     * @return string
     */
    protected function _formatName($name)
    {
        return ucfirst((string) $name);
    }

    /**
     * Whether or not a Plugin by a specific name is loaded
     *
     * @param string $name
     * @return Indi_Loader_PluginLoader
     */
    public function isLoaded($name)
    {
        $name = $this->_formatName($name);
        if ($this->_useStaticRegistry) {
            return isset(self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name]);
        }

        return isset($this->_loadedPlugins[$name]);
    }

    /**
     * Return full class name for a named plugin
     *
     * @param string $name
     * @return string|false False if class not found, class name otherwise
     */
    public function getClassName($name)
    {
        $name = $this->_formatName($name);
        if ($this->_useStaticRegistry
            && isset(self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name])
        ) {
            return self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name];
        } elseif (isset($this->_loadedPlugins[$name])) {
            return $this->_loadedPlugins[$name];
        }

        return false;
    }

    /**
     * Get path to plugin class
     *
     * @param  mixed $name
     * @return string|false False if not found
     */
    public function getClassPath($name)
    {
        $name = $this->_formatName($name);
        if ($this->_useStaticRegistry
            && !empty(self::$_staticLoadedPluginPaths[$this->_useStaticRegistry][$name])
        ) {
            return self::$_staticLoadedPluginPaths[$this->_useStaticRegistry][$name];
        } elseif (!empty($this->_loadedPluginPaths[$name])) {
            return $this->_loadedPluginPaths[$name];
        }

        if ($this->isLoaded($name)) {
            $class = $this->getClassName($name);
            $r     = new ReflectionClass($class);
            $path  = $r->getFileName();
            if ($this->_useStaticRegistry) {
                self::$_staticLoadedPluginPaths[$this->_useStaticRegistry][$name] = $path;
            } else {
                $this->_loadedPluginPaths[$name] = $path;
            }
            return $path;
        }

        return false;
    }

    /**
     * Load a plugin via the name provided
     *
     * @param  string $name
     * @param  bool $throwExceptions Whether or not to throw exceptions if the
     * class is not resolved
     * @return string|false Class name of loaded class; false if $throwExceptions
     * if false and no class found
     * @throws Exception if class not found
     */
    public function load($name, $throwExceptions = true)
    {
        $name = $this->_formatName($name);
        if ($this->isLoaded($name)) {
            return $this->getClassName($name);
        }

        if ($this->_useStaticRegistry) {
            $registry = self::$_staticPrefixToPaths[$this->_useStaticRegistry];
        } else {
            $registry = $this->_prefixToPaths;
        }

        $registry  = array_reverse($registry, true);
        $found     = false;
        $classFile = str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
        foreach ($registry as $prefix => $paths) {
            $className = $prefix . $name;

            if (class_exists($className, false)) {
                $found = true;
                break;
            }

            $paths     = array_reverse($paths, true);

            foreach ($paths as $path) {
                $loadFile = $path . $classFile;
                if (Indi_Loader::isReadable($loadFile)) {
                    include_once $loadFile;
                    if (class_exists($className, false)) {
                        $found = true;
                        break 2;
                    }
                }
            }
        }
        if (!$found) {
            if (!$throwExceptions) {
                return false;
            }

            $message = "Plugin by name '$name' was not found in the registry; used paths:";
            foreach ($registry as $prefix => $paths) {
                $message .= "\n$prefix: " . implode(PATH_SEPARATOR, $paths);
            }
            throw new Exception($message);
       }

        if ($this->_useStaticRegistry) {
            self::$_staticLoadedPlugins[$this->_useStaticRegistry][$name]     = $className;
        } else {
            $this->_loadedPlugins[$name]     = $className;
        }
        return $className;
    }
}
