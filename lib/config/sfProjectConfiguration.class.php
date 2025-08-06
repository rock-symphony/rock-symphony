<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfProjectConfiguration represents a configuration for a symfony project.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectConfiguration
{
  /** @var string */
  protected string $rootDir;

  /** @var string */
  protected string $symfonyLibDir;

  /** @var sfEventDispatcher */
  protected sfEventDispatcher $dispatcher;

  /** @var array */
  protected array $plugins = [];

  /** @var array */
  protected array $pluginPaths = [];

  /** @var array */
  protected array $overriddenPluginPaths = [];

  /** @var sfPluginConfiguration[] */
  protected array $pluginConfigurations = [];

  /** @var bool */
  protected bool $pluginsLoaded = false;

  static protected sfProjectConfiguration | sfApplicationConfiguration | null $active = null;

  /**
   * @param string|null            $rootDir     The project root directory
   * @param sfEventDispatcher|null $dispatcher  The event dispatcher
   */
  public function __construct(string $rootDir = null, sfEventDispatcher $dispatcher = null)
  {
    if (null === self::$active || $this instanceof sfApplicationConfiguration) {
      self::$active = $this;
    }

    $this->rootDir       = null === $rootDir ? static::guessRootDir() : realpath($rootDir);
    $this->symfonyLibDir = realpath(__DIR__ . '/..');
    $this->dispatcher    = $dispatcher ?: new sfEventDispatcher();

    ini_set('magic_quotes_runtime', 'off');

    sfConfig::set('sf_symfony_lib_dir', $this->symfonyLibDir);

    $this->setRootDir($this->rootDir);

    $this->setup();

    $this->loadPlugins();
    $this->setupPlugins();
  }

  /**
   * Setups the current configuration.
   *
   * Override this method if you want to customize your project configuration.
   */
  public function setup(): void
  {
  }

  /**
   * Loads the project's plugin configurations.
   */
  public function loadPlugins(): void
  {
    foreach ($this->getPluginPaths() as $path) {
      if (false === $plugin = array_search($path, $this->overriddenPluginPaths)) {
        $plugin = basename($path);
      }
      $class = $plugin . 'Configuration';

      if (is_readable($file = sprintf('%s/config/%s.class.php', $path, $class))) {
        require_once $file;
        $configuration = new $class($this, $path, $plugin);
      } else {
        $configuration = new sfPluginConfigurationGeneric($this, $path, $plugin);
      }

      $this->pluginConfigurations[$plugin] = $configuration;
    }

    $this->pluginsLoaded = true;
  }

  /**
   * Sets up plugin configurations.
   *
   * Override this method if you want to customize plugin configurations.
   */
  public function setupPlugins(): void
  {
  }

  /**
   * Sets the project root directory.
   *
   * @param string $rootDir  The project root directory
   */
  public function setRootDir(string $rootDir): void
  {
    $this->rootDir = $rootDir;

    sfConfig::add([
      'sf_root_dir'    => $rootDir,

      // global directory structure
      'sf_apps_dir'    => $rootDir . DIRECTORY_SEPARATOR . 'apps',
      'sf_lib_dir'     => $rootDir . DIRECTORY_SEPARATOR . 'lib',
      'sf_log_dir'     => $rootDir . DIRECTORY_SEPARATOR . 'log',
      'sf_data_dir'    => $rootDir . DIRECTORY_SEPARATOR . 'data',
      'sf_config_dir'  => $rootDir . DIRECTORY_SEPARATOR . 'config',
      'sf_test_dir'    => $rootDir . DIRECTORY_SEPARATOR . 'test',
      'sf_plugins_dir' => $rootDir . DIRECTORY_SEPARATOR . 'plugins',
    ]);

    $this->setWebDir($rootDir . DIRECTORY_SEPARATOR . 'web');
    $this->setCacheDir($rootDir . DIRECTORY_SEPARATOR . 'cache');
  }

  /**
   * Returns the project root directory.
   *
   * @return string The project root directory
   */
  public function getRootDir(): string
  {
    return $this->rootDir;
  }

  /**
   * Sets the cache root directory.
   *
   * @param string $cacheDir  The absolute path to the cache dir.
   */
  public function setCacheDir(string $cacheDir): void
  {
    sfConfig::set('sf_cache_dir', $cacheDir);
  }

  /**
   * Sets the log directory.
   *
   * @param string $logDir  The absolute path to the log dir.
   */
  public function setLogDir(string $logDir): void
  {
    sfConfig::set('sf_log_dir', $logDir);
  }

  /**
   * Sets the web root directory.
   *
   * @param string $webDir  The absolute path to the web dir.
   */
  public function setWebDir(string $webDir): void
  {
    sfConfig::add([
      'sf_web_dir'         => $webDir,
      'sf_upload_dir_name' => $uploadDirName = 'uploads',
      'sf_upload_dir'      => $webDir . DIRECTORY_SEPARATOR . $uploadDirName,
    ]);
  }

  /**
   * Gets directories where model classes are stored. The order of returned paths is lowest precedence
   * to highest precedence.
   *
   * @return string[] An array of directories
   */
  public function getModelDirs(): array
  {
    return array_merge(
      $this->getPluginSubPaths('/lib/model'),     // plugins
      [sfConfig::get('sf_lib_dir') . '/model'] // project
    );
  }

  /**
   * Gets directories where template files are stored for a generator class and a specific theme.
   *
   * @param string $class  The generator class name
   * @param string $theme  The theme name
   *
   * @return string[] An array of directories
   */
  public function getGeneratorTemplateDirs(string $class, string $theme): array
  {
    return array_merge(
      [sfConfig::get('sf_data_dir') . '/generator/' . $class . '/' . $theme . '/template'], // project
      $this->getPluginSubPaths('/data/generator/' . $class . '/' . $theme . '/template'),      // plugins
      [sfConfig::get('sf_data_dir') . '/generator/' . $class . '/default/template'],    // project (default theme)
      $this->getPluginSubPaths('/data/generator/' . $class . '/default/template')          // plugins (default theme)
    );
  }

  /**
   * Gets directories where the skeleton is stored for a generator class and a specific theme.
   *
   * @param string $class  The generator class name
   * @param string $theme  The theme name
   *
   * @return string[] An array of directories
   */
  public function getGeneratorSkeletonDirs(string $class, string $theme): array
  {
    return array_merge(
      [sfConfig::get('sf_data_dir') . '/generator/' . $class . '/' . $theme . '/skeleton'], // project
      $this->getPluginSubPaths('/data/generator/' . $class . '/' . $theme . '/skeleton'),      // plugins
      [sfConfig::get('sf_data_dir') . '/generator/' . $class . '/default/skeleton'],    // project (default theme)
      $this->getPluginSubPaths('/data/generator/' . $class . '/default/skeleton')          // plugins (default theme)
    );
  }

  /**
   * Gets the template to use for a generator class.
   *
   * @param string $class  The generator class name
   * @param string $theme  The theme name
   * @param string $path   The template path
   *
   * @return string A template path
   *
   * @throws sfException
   */
  public function getGeneratorTemplate(string $class, string $theme, string $path): string
  {
    $dirs = $this->getGeneratorTemplateDirs($class, $theme);
    foreach ($dirs as $dir) {
      if (is_readable($dir . '/' . $path)) {
        return $dir . '/' . $path;
      }
    }

    throw new sfException(sprintf('Unable to load "%s" generator template in: %s.', $path, implode(', ', $dirs)));
  }

  /**
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string $configPath  The configuration path
   *
   * @return string[] An array of paths
   */
  public function getConfigPaths(string $configPath): array
  {
    $globalConfigPath = basename(dirname($configPath)) . '/' . basename($configPath);

    $files = [
      $this->getSymfonyLibDir() . '/config/' . $globalConfigPath, // symfony
    ];

    foreach ($this->getPluginPaths() as $path) {
      if (is_file($file = $path . '/' . $globalConfigPath)) {
        $files[] = $file;                                     // plugins
      }
    }

    $files = array_merge($files, [
      $this->getRootDir() . '/' . $globalConfigPath,              // project
      $this->getRootDir() . '/' . $configPath,                    // project
    ]);

    foreach ($this->getPluginPaths() as $path) {
      if (is_file($file = $path . '/' . $configPath)) {
        $files[] = $file;                                     // plugins
      }
    }

    $configs = [];
    foreach (array_unique($files) as $file) {
      if (is_readable($file)) {
        $configs[] = $file;
      }
    }

    return $configs;
  }

  /**
   * Sets the enabled plugins.
   *
   * @param string[] $plugins  An array of plugin names
   *
   * @throws LogicException If plugins have already been loaded
   */
  public function setPlugins(array $plugins): void
  {
    if ($this->pluginsLoaded) {
      throw new LogicException('Plugins have already been loaded.');
    }

    $this->plugins = $plugins;

    $this->pluginPaths = [];
  }

  /**
   * Enables a plugin or a list of plugins.
   *
   * @param string[] $plugins  A plugin name or a plugin list
   */
  public function enablePlugins(array $plugins): void
  {
    $this->setPlugins(array_merge($this->plugins, $plugins));
  }

  /**
   * Disables a plugin.
   *
   * @param string[] $plugins  A plugin name or a plugin list
   *
   * @throws LogicException If plugins have already been loaded
   */
  public function disablePlugins(array $plugins): void
  {
    if ($this->pluginsLoaded) {
      throw new LogicException('Plugins have already been loaded.');
    }

    foreach ($plugins as $plugin) {
      if (false !== $pos = array_search($plugin, $this->plugins)) {
        unset($this->plugins[$pos]);
      } else {
        throw new InvalidArgumentException(sprintf('The plugin "%s" does not exist.', $plugin));
      }
    }

    $this->pluginPaths = [];
  }

  /**
   * Enabled all installed plugins except th = array()e one given as argument.
   *
   * @param string[] $plugins  A plugin name or a plugin list
   *
   * @throws LogicException If plugins have already been loaded
   */
  public function enableAllPluginsExcept(array $plugins): void
  {
    if ($this->pluginsLoaded) {
      throw new LogicException('Plugins have already been loaded.');
    }

    $this->plugins = array_keys($this->getAllPluginPaths());

    sort($this->plugins);

    $this->disablePlugins($plugins);
  }

  /**
   * Gets the list of enabled plugins.
   *
   * @return string[] An array of enabled plugins
   */
  public function getPlugins(): array
  {
    return $this->plugins;
  }

  /**
   * Gets the paths plugin sub-directories, minding overloaded plugins.
   *
   * @param string $subPath  The subdirectory to look for
   *
   * @return string[] The plugin paths.
   */
  public function getPluginSubPaths(string $subPath): array
  {
    if (array_key_exists($subPath, $this->pluginPaths)) {
      return $this->pluginPaths[$subPath];
    }

    $this->pluginPaths[$subPath] = [];
    $pluginPaths                 = $this->getPluginPaths();
    foreach ($pluginPaths as $pluginPath) {
      if (is_dir($pluginPath . $subPath)) {
        $this->pluginPaths[$subPath][] = $pluginPath . $subPath;
      }
    }

    return $this->pluginPaths[$subPath];
  }

  /**
   * Gets the paths to plugins root directories, minding overloaded plugins.
   *
   * @return string[] The plugin root paths.
   *
   * @throws InvalidArgumentException If an enabled plugin does not exist
   */
  public function getPluginPaths(): array
  {
    if ( ! isset($this->pluginPaths[''])) {
      $pluginPaths = $this->getAllPluginPaths();

      $this->pluginPaths[''] = [];
      foreach ($this->getPlugins() as $plugin) {
        if (isset($pluginPaths[$plugin])) {
          $this->pluginPaths[''][] = $pluginPaths[$plugin];
        } else {
          throw new InvalidArgumentException(sprintf('The plugin "%s" does not exist.', $plugin));
        }
      }
    }

    return $this->pluginPaths[''];
  }

  /**
   * Returns an array of paths for all available plugins.
   *
   * @return string[]
   */
  public function getAllPluginPaths(): array
  {
    $pluginPaths = [];

    // search for *Plugin directories representing plugins
    // follow links and do not recurse. No need to exclude VC because they do not end with *Plugin
    $finder = sfFinder::type('dir')->maxdepth(0)->ignore_version_control(false)->follow_link()->name('*Plugin');
    $dirs   = [
      $this->getSymfonyLibDir() . '/plugins',
      sfConfig::get('sf_plugins_dir'),
    ];

    foreach ($finder->in($dirs) as $path) {
      $pluginPaths[basename($path)] = $path;
    }

    foreach ($this->overriddenPluginPaths as $plugin => $path) {
      $pluginPaths[$plugin] = $path;
    }

    return $pluginPaths;
  }

  /**
   * Manually sets the location of a particular plugin.
   *
   * This method can be used to ease functional testing of plugins. It is not
   * intended to support sharing plugins between projects, as many plugins
   * save project specific code (to /lib/form/base, for example).
   *
   * @param string $plugin
   * @param string $path
   */
  public function setPluginPath(string $plugin, string $path): void
  {
    $this->overriddenPluginPaths[$plugin] = realpath($path);
  }

  /**
   * Returns the configuration for the requested plugin.
   *
   * @param string $name
   *
   * @return  sfPluginConfiguration
   */
  public function getPluginConfiguration(string $name): sfPluginConfiguration
  {
    if ( ! isset($this->pluginConfigurations[$name])) {
      throw new InvalidArgumentException(sprintf('There is no configuration object for the "%s" object.', $name));
    }

    return $this->pluginConfigurations[$name];
  }

  /**
   * Returns the event dispatcher.
   *
   * @return sfEventDispatcher A sfEventDispatcher instance
   */
  public function getEventDispatcher(): sfEventDispatcher
  {
    return $this->dispatcher;
  }

  /**
   * Returns the symfony lib directory.
   *
   * @return string The symfony lib directory
   */
  public function getSymfonyLibDir(): string
  {
    return $this->symfonyLibDir;
  }

  /**
   * Returns the active configuration.
   *
   * @return sfProjectConfiguration|sfApplicationConfiguration The current sfProjectConfiguration instance
   */
  public static function getActive(): sfProjectConfiguration
  {
    if ( ! static::hasActive()) {
      throw new RuntimeException('There is no active configuration.');
    }

    return self::$active;
  }

  /**
   * Returns true if these is an active configuration.
   *
   * @return bool
   */
  public static function hasActive(): bool
  {
    return null !== self::$active;
  }

  /**
   * Guesses the project root directory.
   *
   * @return string The project root directory
   */
  public static function guessRootDir(): string
  {
    $r = new ReflectionClass(ProjectConfiguration::class);

    return realpath(dirname($r->getFileName()) . '/..');
  }

  /**
   * Returns a sfApplicationConfiguration configuration for a given application.
   *
   * @param string                 $application  An application name
   * @param string                 $environment  The environment name
   * @param Boolean                $debug        true to enable debug mode
   * @param string|null            $rootDir      The project root directory
   * @param sfEventDispatcher|null $dispatcher   An event dispatcher
   *
   * @return sfApplicationConfiguration A sfApplicationConfiguration instance
   */
  public static function getApplicationConfiguration(
    string            $application,
    string            $environment,
    bool              $debug,
    string            $rootDir = null,
    sfEventDispatcher $dispatcher = null
  ): sfApplicationConfiguration {
    $class = $application . 'Configuration';

    if (null === $rootDir) {
      $rootDir = static::guessRootDir();
    }

    if ( ! is_file($file = "{$rootDir}/apps/{$application}/config/{$class}.class.php")) {
      throw new InvalidArgumentException(sprintf('The application "%s" does not exist.', $application));
    }

    require_once $file;

    return new $class($environment, $debug, $rootDir, $dispatcher);
  }
}
