<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConfiguration represents a configuration for a symfony application.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfApplicationConfiguration extends ProjectConfiguration
{
  /** @var bool */
  static protected bool $coreLoaded = false;

  /** @var array<string,bool> [ string $helper_name => bool, ... ] */
  static protected array $loadedHelpers = [];

  protected sfConfigCache $configCache;

  protected string $application;

  protected string $environment;

  protected bool $debug = false;

  /** @var array Multi-level configuration array structure */
  protected array $config = [];

  /** @var mixed[] Multi-level configuration cache */
  protected array $cache = [];

  /**
   * Constructor.
   *
   * @param string                 $environment  The environment name
   * @param bool                   $debug        true to enable debug mode
   * @param string|null            $rootDir      The project root directory
   * @param sfEventDispatcher|null $dispatcher   An event dispatcher
   */
  public function __construct(string $environment, bool $debug, string $rootDir = null, sfEventDispatcher $dispatcher = null)
  {
    $this->environment = $environment;
    $this->debug       = $debug;
    $this->application = str_replace('Configuration', '', get_class($this));
    $this->configCache = new sfConfigCache($this);

    parent::__construct($rootDir, $dispatcher);

    $this->configure();

    $this->initConfiguration();

    if (sfConfig::get('sf_check_lock')) {
      $this->checkLock();
    }

    if (is_file($file = sfConfig::get('sf_app_cache_dir') . '/config/configuration.php')) {
      $this->cache = require $file;
    }

    $this->initialize();

    // store current sfConfig values
    $this->config = sfConfig::getAll();
  }

  /**
   * Configures the current configuration.
   *
   * Override this method if you want to customize your application configuration.
   */
  public function configure(): void
  {
  }

  /**
   * Initialized the current configuration.
   *
   * Override this method if you want to customize your application initialization.
   */
  public function initialize(): void
  {
  }

  public function activate(): void
  {
    sfConfig::clear();
    sfConfig::add($this->config);
  }

  /**
   * Various initializations.
   */
  public function initConfiguration(): void
  {
    $configCache = $this->getConfigCache();

    // in debug mode, start global timer
    if ($this->isDebug() && ! sfConfig::get('sf_cli') && ! sfWebDebugPanelTimer::isStarted()) {
      sfWebDebugPanelTimer::startTime();
    }

    // required core classes for the framework
    if ( ! $this->isDebug() && ! sfConfig::get('sf_test') && ! sfConfig::get('sf_cli') && ! self::$coreLoaded) {
      $configCache->import('config/core_compile.yml', false);
    }

    // load base settings
    include($configCache->checkConfig('config/settings.yml'));
    if ($file = $configCache->checkConfig('config/app.yml', true)) {
      include($file);
    }

    if ( ! sfConfig::get('sf_cli') && false !== sfConfig::get('sf_csrf_secret')) {
      sfForm::enableCSRFProtection(sfConfig::get('sf_csrf_secret'));
    }

    sfWidget::setCharset(sfConfig::get('sf_charset'));
    sfValidatorBase::setCharset(sfConfig::get('sf_charset'));

    // force setting default timezone if not set
    if ($default_timezone = sfConfig::get('sf_default_timezone')) {
      date_default_timezone_set($default_timezone);
    } elseif (sfConfig::get('sf_force_default_timezone', true)) {
      date_default_timezone_set(@date_default_timezone_get());
    }

    // error settings
    ini_set('display_errors', $this->isDebug() ? 'on' : 'off');

    // parse and set error reporting level
    $error_reporting = new sfErrorReporting();
    $error_reporting->set(sfConfig::get('sf_error_reporting'));

    // initialize plugin configuration objects
    $this->initializePlugins();

    self::$coreLoaded = true;
  }

  /**
   * Initializes plugin configuration objects.
   */
  protected function initializePlugins(): void
  {
    foreach ($this->pluginConfigurations as $configuration) {
      $configuration->initialize();
    }
  }

  /**
   * Returns a configuration cache object for the current configuration.
   *
   * @return sfConfigCache A sfConfigCache instance
   */
  public function getConfigCache(): sfConfigCache
  {
    return $this->configCache;
  }

  /**
   * Check lock files to see if we're not in a cache cleaning process.
   *
   * @return void
   */
  public function checkLock(): void
  {
    if (
      $this->hasLockFile(sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $this->getApplication() . '_' . $this->getEnvironment() . '-cli.lck', 5)
      ||
      $this->hasLockFile(sfConfig::get('sf_data_dir') . DIRECTORY_SEPARATOR . $this->getApplication() . '_' . $this->getEnvironment() . '.lck')
    ) {
      // application is not available - we'll find the most specific unavailable page...
      $files = [
        sfConfig::get('sf_app_config_dir') . '/unavailable.php',
        sfConfig::get('sf_config_dir') . '/unavailable.php',
        sfConfig::get('sf_web_dir') . '/errors/unavailable.php',
        $this->getSymfonyLibDir() . '/exception/data/unavailable.php',
      ];

      foreach ($files as $file) {
        if (is_readable($file)) {
          header("HTTP/1.1 503 Service Temporarily Unavailable");
          header("Status: 503 Service Temporarily Unavailable");

          include $file;
          break;
        }
      }

      die(1);
    }
  }

  /**
   * Determines if a lock file is present.
   *
   * @param string  $lockFile             Name of the lock file.
   * @param integer $maxLockFileLifeTime  A max amount of life time for the lock file.
   *
   * @return bool true, if the lock file is present, otherwise false.
   */
  protected function hasLockFile(string $lockFile, int $maxLockFileLifeTime = 0): bool
  {
    $isLocked = false;
    if (is_readable($lockFile) && ($last_access = fileatime($lockFile))) {
      $now      = time();
      $timeDiff = $now - $last_access;

      if ( ! $maxLockFileLifeTime || $timeDiff < $maxLockFileLifeTime) {
        $isLocked = true;
      } else {
        $isLocked = @unlink($lockFile) ? false : true;
      }
    }

    return $isLocked;
  }

  /**
   * Sets the project root directory.
   *
   * @param string $rootDir  The project root directory
   */
  public function setRootDir(string $rootDir): void
  {
    parent::setRootDir($rootDir);

    sfConfig::add([
      'sf_app'         => $this->getApplication(),
      'sf_environment' => $this->getEnvironment(),
      'sf_debug'       => $this->isDebug(),
      'sf_cli'         => PHP_SAPI === 'cli',
    ]);

    $this->setAppDir(sfConfig::get('sf_apps_dir') . DIRECTORY_SEPARATOR . $this->getApplication());
  }

  /**
   * Sets the app directory.
   *
   * @param string $appDir  The absolute path to the app dir.
   */
  public function setAppDir(string $appDir): void
  {
    sfConfig::add([
      'sf_app_dir'          => $appDir,

      // SF_APP_DIR directory structure
      'sf_app_config_dir'   => $appDir . DIRECTORY_SEPARATOR . 'config',
      'sf_app_lib_dir'      => $appDir . DIRECTORY_SEPARATOR . 'lib',
      'sf_app_module_dir'   => $appDir . DIRECTORY_SEPARATOR . 'modules',
      'sf_app_template_dir' => $appDir . DIRECTORY_SEPARATOR . 'templates',
      'sf_app_i18n_dir'     => $appDir . DIRECTORY_SEPARATOR . 'i18n',
    ]);
  }

  /**
   * @param string $cacheDir
   * @see sfProjectConfiguration
   */
  public function setCacheDir(string $cacheDir): void
  {
    parent::setCacheDir($cacheDir);

    sfConfig::add([
      'sf_app_base_cache_dir' => $cacheDir . DIRECTORY_SEPARATOR . $this->getApplication(),
      'sf_app_cache_dir'      => $appCacheDir = $cacheDir . DIRECTORY_SEPARATOR . $this->getApplication() . DIRECTORY_SEPARATOR . $this->getEnvironment(),

      // SF_CACHE_DIR directory structure
      'sf_template_cache_dir' => $appCacheDir . DIRECTORY_SEPARATOR . 'template',
      'sf_i18n_cache_dir'     => $appCacheDir . DIRECTORY_SEPARATOR . 'i18n',
      'sf_config_cache_dir'   => $appCacheDir . DIRECTORY_SEPARATOR . 'config',
      'sf_test_cache_dir'     => $appCacheDir . DIRECTORY_SEPARATOR . 'test',
      'sf_module_cache_dir'   => $appCacheDir . DIRECTORY_SEPARATOR . 'modules',
    ]);
  }

  /**
   * Gets directories where controller classes are stored for a given module.
   *
   * @param string $moduleName  The module name
   *
   * @return array<string,bool> An array of directories
   */
  public function getControllerDirs(string $moduleName): array
  {
    if ( ! isset($this->cache['getControllerDirs'][$moduleName])) {
      $dirs = [];

      $dirs[sprintf("%s/%s/actions", sfConfig::get('sf_app_module_dir'), $moduleName)] = false; // application

      foreach ($this->getPluginPaths() as $path) {
        if (is_dir($dir = "{$path}/modules/{$moduleName}/actions")) {
          $dirs[$dir] = true; // plugins
        }
      }

      if (is_dir($dir = "{$this->getSymfonyLibDir()}/controller/{$moduleName}/actions")) {
        $dirs[$dir] = true; // core modules
      }

      $this->cache['getControllerDirs'][$moduleName] = $dirs;
    }

    return $this->cache['getControllerDirs'][$moduleName];
  }

  /**
   * Gets directories where lib files are stored for a given module.
   *
   * @param string $moduleName  The module name
   *
   * @return string[] An array of directories
   */
  public function getLibDirs(string $moduleName): array
  {
    $dirs = [];

    $dirs[] = sfConfig::get('sf_app_module_dir') . '/' . $moduleName . '/lib';                  // application
    $dirs   = array_merge($dirs, $this->getPluginSubPaths('/modules/' . $moduleName . '/lib')); // plugins
    $dirs[] = $this->getSymfonyLibDir() . '/controller/' . $moduleName . '/lib';                // core modules
    $dirs[] = sfConfig::get('sf_module_cache_dir') . '/auto' . ucfirst($moduleName . '/lib');   // generated templates in cache

    return $dirs;
  }

  /**
   * Gets directories where template files are stored for a given module.
   *
   * @param string $moduleName  The module name
   *
   * @return string[] An array of directories
   */
  public function getTemplateDirs(string $moduleName): array
  {
    $dirs = [];

    $dirs[] = sfConfig::get('sf_app_module_dir') . '/' . $moduleName . '/templates';                  // application
    $dirs   = array_merge($dirs, $this->getPluginSubPaths('/modules/' . $moduleName . '/templates')); // plugins
    $dirs[] = $this->getSymfonyLibDir() . '/controller/' . $moduleName . '/templates';                // core modules
    $dirs[] = sfConfig::get('sf_module_cache_dir') . '/auto' . ucfirst($moduleName . '/templates');   // generated templates in cache

    return $dirs;
  }

  /**
   * Gets the helper directories for a given module name.
   *
   * @param string $moduleName  The module name
   *
   * @return string[]  An array of directories
   */
  public function getHelperDirs(string $moduleName = null): array
  {
    $dirs = [];

    if ($moduleName) {
      $dirs[] = sfConfig::get('sf_app_module_dir') . '/' . $moduleName . '/lib/helper'; // module

      $dirs = array_merge($dirs, $this->getPluginSubPaths('/modules/' . $moduleName . '/lib/helper'));
    }

    return array_merge(
      $dirs,
      [
        sfConfig::get('sf_app_lib_dir') . '/helper',         // application
        sfConfig::get('sf_lib_dir') . '/helper',             // project
      ],
      $this->getPluginSubPaths('/lib/helper'),             // plugins
      [$this->getSymfonyLibDir() . '/helper']           // symfony
    );
  }

  /**
   * Gets the template directory to use for a given module and template file.
   *
   * @param string $moduleName    The module name
   * @param string $templateFile  The template file
   *
   * @return string|null A template directory
   */
  public function getTemplateDir(string $moduleName, string $templateFile): ?string
  {
    if ( ! isset($this->cache['getTemplateDir'][$moduleName][$templateFile])) {
      $this->cache['getTemplateDir'][$moduleName][$templateFile] = null;
      foreach ($this->getTemplateDirs($moduleName) as $dir) {
        if (is_readable($dir . '/' . $templateFile)) {
          $this->cache['getTemplateDir'][$moduleName][$templateFile] = $dir;
          break;
        }
      }
    }

    return $this->cache['getTemplateDir'][$moduleName][$templateFile];
  }

  /**
   * Gets the template to use for a given module and template file.
   *
   * @param string $moduleName    The module name
   * @param string $templateFile  The template file
   *
   * @return string|null A template path
   */
  public function getTemplatePath(string $moduleName, string $templateFile): ?string
  {
    $dir = $this->getTemplateDir($moduleName, $templateFile);

    return $dir ? $dir . '/' . $templateFile : null;
  }

  /**
   * @see sfProjectConfiguration
   * @return string[]
   */
  public function getPluginPaths(): array
  {
    if ( ! isset($this->cache['getPluginPaths'])) {
      $this->cache['getPluginPaths'] = parent::getPluginPaths();
    }

    return $this->cache['getPluginPaths'];
  }

  /**
   * Gets the decorator directories.
   *
   * @return string[]  An array of the decorator directories
   */
  public function getDecoratorDirs(): array
  {
    return [sfConfig::get('sf_app_template_dir')];
  }

  /**
   * Gets the decorator directory for a given template.
   *
   * @param string $template  The template file
   *
   * @return string A template directory
   */
  public function getDecoratorDir(string $template): ?string
  {
    foreach ($this->getDecoratorDirs() as $dir) {
      if (is_readable($dir . '/' . $template)) {
        return $dir;
      }
    }
    return null;
  }

  /**
   * Gets the i18n directories to use globally.
   *
   * @return string[] An array of i18n directories
   */
  public function getI18NGlobalDirs(): array
  {
    $dirs = [];

    // application
    if (is_dir($dir = sfConfig::get('sf_app_i18n_dir'))) {
      $dirs[] = $dir;
    }

    // plugins
    return array_merge($dirs, $this->getPluginSubPaths('/i18n'));
  }

  /**
   * Gets the i18n directories to use for a given module.
   *
   * @param string $moduleName  The module name
   *
   * @return string[] An array of i18n directories
   */
  public function getI18NDirs(string $moduleName): array
  {
    $dirs = [];

    // module
    if (is_dir($dir = sfConfig::get('sf_app_module_dir') . '/' . $moduleName . '/i18n')) {
      $dirs[] = $dir;
    }

    // application
    if (is_dir($dir = sfConfig::get('sf_app_i18n_dir'))) {
      $dirs[] = $dir;
    }

    return array_merge(
      $dirs,
      $this->getPluginSubPaths('/modules/' . $moduleName . '/i18n'), // module in plugins
      $this->getPluginSubPaths('/i18n')                          // plugins
    );
  }

  /**
   * Gets the configuration file paths for a given relative configuration path.
   *
   * @param string $configPath  The configuration path
   *
   * @return array An array of paths
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
      sfConfig::get('sf_app_dir') . '/' . $globalConfigPath,      // application
      sfConfig::get('sf_app_cache_dir') . '/' . $configPath,      // generated modules
    ]);

    foreach ($this->getPluginPaths() as $path) {
      if (is_file($file = $path . '/' . $configPath)) {
        $files[] = $file;                                     // plugins
      }
    }

    $files[] = sfConfig::get('sf_app_dir') . '/' . $configPath;   // module

    $configs = [];
    foreach (array_unique($files) as $file) {
      if (is_readable($file)) {
        $configs[] = $file;
      }
    }

    return $configs;
  }

  /**
   * Loads helpers.
   *
   * @param array|string $helpers     An array of helpers to load
   * @param string       $moduleName  A module name (optional)
   */
  public function loadHelpers(array $helpers, string $moduleName = ''): void
  {
    foreach ((array)$helpers as $helperName) {
      if (isset(self::$loadedHelpers[$helperName])) {
        continue;
      }

      if (isset($this->cache['loadedHelpers'][$moduleName][$helperName])) {
        include_once $this->cache['loadedHelpers'][$moduleName][$helperName];
      } elseif (isset($this->cache['loadedHelpers'][''][$helperName])) {
        include_once $this->cache['loadedHelpers'][''][$helperName];
      } else {
        $fileName = $helperName . 'Helper.php';

        if ( ! isset($dirs)) {
          $dirs = $this->getHelperDirs($moduleName);
        }

        foreach ($dirs as $dir) {
          $included = false;
          if (is_readable($dir . '/' . $fileName)) {
            include_once $dir . '/' . $fileName;
            $included = true;
            break;
          }
        }

        if ( ! $included) {
          throw new InvalidArgumentException(sprintf('Unable to load "%sHelper.php" helper in: %s.', $helperName, implode(', ', array_map(['sfDebug', 'shortenFilePath'], $dirs))));
        }
      }

      self::$loadedHelpers[$helperName] = true;
    }
  }

  /**
   * Returns the application name.
   *
   * @return string The application name
   */
  public function getApplication(): string
  {
    return $this->application;
  }

  /**
   * Returns the environment name.
   *
   * @return string The environment name
   */
  public function getEnvironment(): string
  {
    return $this->environment;
  }

  /**
   * Returns true if this configuration has debug enabled.
   *
   * @return Boolean true if the configuration has debug enabled, false otherwise
   */
  public function isDebug(): bool
  {
    return $this->debug;
  }
}
