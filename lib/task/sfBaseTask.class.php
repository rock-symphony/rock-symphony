<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RockSymphony\Util\Finder;

/**
 * Base class for all symfony tasks.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfBaseTask extends sfCommandApplicationTask
{
  protected sfProjectConfiguration | sfApplicationConfiguration | null $configuration = null;

  protected int | null $statusStartTime = null;

  protected sfFilesystem | null $filesystem = null;

  /**
   * @see sfTask
   * @inheritdoc
   */
  protected function doRun(sfCommandManager $commandManager, array | string | null $options): int
  {
    $event   = $this->dispatcher->filter(
      new sfEvent($this, 'command.filter_options', ['command_manager' => $commandManager]),
      $options,
    );
    $options = $event->getReturnValue();

    $this->process($commandManager, $options);

    $event = new sfEvent($this, 'command.pre_command', [
      'arguments' => $commandManager->getArgumentValues(),
      'options'   => $commandManager->getOptionValues(),
    ]);

    $this->dispatcher->notifyUntil($event);

    if ($event->isProcessed()) {
      return $event->getReturnValue();
    }

    $this->checkProjectExists();

    $requiresApplication = $commandManager->getArgumentSet()->hasArgument('application')
                           || $commandManager->getOptionSet()->hasOption('application');

    if (null === $this->configuration || ($requiresApplication && ! $this->configuration instanceof sfApplicationConfiguration)) {
      $application = $commandManager->getArgumentSet()->hasArgument('application')
        ? $commandManager->getArgumentValue('application')
        : ($commandManager->getOptionSet()->hasOption('application')
          ? $commandManager->getOptionValue('application')
          : null);

      $env = ($commandManager->getOptionSet()->hasOption('env') ? $commandManager->getOptionValue('env') : 'test') ?: 'test';

      if (true === $application) {
        $application = $this->getFirstApplication();

        if ($commandManager->getOptionSet()->hasOption('application')) {
          $commandManager->setOption($commandManager->getOptionSet()->getOption('application'), $application);
        }
      }

      $this->configuration = $this->createConfiguration($application, $env);
    }

    if ( ! $this->withTrace()) {
      sfConfig::set('sf_logging_enabled', false);
    }

    $ret = $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());

    $this->dispatcher->notify(new sfEvent($this, 'command.post_command'));

    return $ret;
  }

  /**
   * Sets the current task's configuration.
   *
   * @param sfProjectConfiguration | null $configuration
   */
  public function setConfiguration(sfProjectConfiguration | null $configuration = null): void
  {
    $this->configuration = $configuration;
  }

  /**
   * Returns the filesystem instance.
   *
   * @return sfFilesystem A sfFilesystem instance
   */
  public function getFilesystem(): sfFilesystem
  {
    if ( ! isset($this->filesystem)) {
      if ($this->isVerbose()) {
        $this->filesystem = new sfFilesystem($this->dispatcher, $this->formatter);
      } else {
        $this->filesystem = new sfFilesystem();
      }
    }

    return $this->filesystem;
  }

  /**
   * Checks if the current directory is a symfony project directory.
   *
   * @return void If the current directory is a symfony project directory, throws otherwise
   *
   * @throws sfException
   */
  public function checkProjectExists(): void
  {
    if ( ! file_exists('symfony')) {
      throw new sfException('You must be in a symfony project directory.');
    }
  }

  /**
   * Checks if an application exists.
   *
   * @param string $app  The application name
   *
   * @return void if the application exists, throws otherwise
   *
   * @throws sfException
   */
  public function checkAppExists(string $app): void
  {
    if ( ! is_dir(sfConfig::get('sf_apps_dir') . '/' . $app)) {
      throw new sfException(sprintf('Application "%s" does not exist', $app));
    }
  }

  /**
   * Checks if a module exists.
   *
   * @param string $app     The application name
   * @param string $module  The module name
   *
   * @return void if the module exists, throws otherwise
   *
   * @throws sfException
   */
  public function checkModuleExists(string $app, string $module): void
  {
    if ( ! is_dir(sfConfig::get('sf_apps_dir') . '/' . $app . '/modules/' . $module)) {
      throw new sfException(sprintf('Module "%s/%s" does not exist.', $app, $module));
    }
  }

  /**
   * Checks if trace mode is enabled
   *
   * @return boolean
   */
  protected function withTrace(): bool
  {
    if (null !== $this->commandApplication && ! $this->commandApplication->withTrace()) {
      return false;
    }

    return true;
  }

  /**
   * Checks if verbose mode is enabled
   *
   * @return boolean
   */
  protected function isVerbose(): bool
  {
    if (null !== $this->commandApplication && ! $this->commandApplication->isVerbose()) {
      return false;
    }

    return true;
  }

  /**
   * Checks if debug mode is enabled
   *
   * @return boolean
   */
  protected function isDebug(): bool
  {
    if (null !== $this->commandApplication && ! $this->commandApplication->isDebug()) {
      return false;
    }

    return true;
  }

  /**
   * Creates a configuration object.
   *
   * @param string|null $application  The application name
   * @param string      $env          The environment name
   *
   * @return sfProjectConfiguration A sfProjectConfiguration instance
   */
  protected function createConfiguration(string | null $application, string $env): sfProjectConfiguration
  {
    if (null !== $application) {
      $this->checkAppExists($application);

      require_once sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php';

      return ProjectConfiguration::getApplicationConfiguration($application, $env, $this->isDebug(), null, $this->dispatcher);
    }

    if (file_exists(sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php')) {
      require_once sfConfig::get('sf_config_dir') . '/ProjectConfiguration.class.php';
      $configuration = new ProjectConfiguration(null, $this->dispatcher);
    } else {
      $configuration = new sfProjectConfiguration(getcwd(), $this->dispatcher);
    }

    if (null !== $env) {
      sfConfig::set('sf_environment', $env);
    }

    return $configuration;
  }

  /**
   * Returns the first application in apps.
   *
   * @return string|null The Application name, if available
   */
  protected function getFirstApplication(): string | null
  {
    $dirs = Finder::dirs()->maxDepth(0)->followLinks()->relative()->in(sfConfig::get('sf_apps_dir'));

    return $dirs[0] ?? null;
  }

  /**
   * Mirrors a directory structure inside the created project.
   *
   * @param string      $dir     The directory to mirror
   * @param Finder|null $finder  A sfFinder instance to use for the mirroring
   */
  protected function installDir(string $dir, Finder | null $finder = null)
  {
    if (null === $finder) {
      $finder = Finder::any()->discard('.sf');
    }

    $this->getFilesystem()->mirror($dir, sfConfig::get('sf_root_dir'), $finder);
  }

  /**
   * Replaces tokens in files contained in a given directory.
   *
   * If you don't pass a directory, it will replace in the config/ and lib/ directory.
   *
   * You can define global tokens by defining the $this->tokens property.
   *
   * @param string[]             $dirs    An array of directory where to do the replacement
   * @param array<string,string> $tokens  An array of tokens to use
   */
  protected function replaceTokens(array $dirs = [], array $tokens = [])
  {
    $dirs = $dirs ?: [sfConfig::get('sf_config_dir'), sfConfig::get('sf_lib_dir')];

    $tokens = array_merge($this->tokens ?? [], $tokens);

    $this->getFilesystem()->replaceTokens(Finder::files()->prune('vendor')->in($dirs), '##', '##', $tokens);
  }

  /**
   * Reloads tasks.
   *
   * Useful when you install plugins with tasks and if you want to use them with the runTask() method.
   */
  protected function reloadTasks()
  {
    if (null === $this->commandApplication) {
      return;
    }

    $this->configuration = $this->createConfiguration(null, null);

    $this->commandApplication->clearTasks();
    $this->commandApplication->loadTasks($this->configuration);

    $disabledPluginsRegex = sprintf('#^(%s)#', implode('|', array_diff($this->configuration->getAllPluginPaths(), $this->configuration->getPluginPaths())));
    $tasks                = [];
    foreach (get_declared_classes() as $class) {
      $r = new Reflectionclass($class);
      if ($r->isSubclassOf('sfTask') && ! $r->isAbstract() && ! preg_match($disabledPluginsRegex, $r->getFileName())) {
        $tasks[] = new $class($this->dispatcher, $this->formatter);
      }
    }

    $this->commandApplication->registerTasks($tasks);
  }

  /**
   * @see sfCommandApplicationTask
   */
  protected function createTask($name): sfCommandApplicationTask
  {
    $task = parent::createTask($name);

    if ($task instanceof sfBaseTask) {
      $task->setConfiguration($this->configuration);
    }

    return $task;
  }

  /**
   * Show status of task
   *
   * @param int $done
   * @param int $total
   * @param int $size
   * @return void
   */
  protected function showStatus(int $done, int $total, int $size = 30): void
  {
    // if we go over our bound, just ignore it
    if ($done > $total) {
      $this->statusStartTime = null;
      return;
    }

    if (null === $this->statusStartTime) {
      $this->statusStartTime = time();
    }

    $now  = time();
    $perc = (double)($done / $total);
    $bar  = floor($perc * $size);

    $statusBar = "\r[";
    $statusBar .= str_repeat('=', $bar);
    if ($bar < $size) {
      $statusBar .= '>';
      $statusBar .= str_repeat(' ', $size - $bar);
    } else {
      $statusBar .= "=";
    }

    $disp = number_format($perc * 100, 0);

    $statusBar .= "] $disp% ($done/$total)";

    $rate = $done ? ($now - $this->statusStartTime) / $done : 0;
    $left = $total - $done;
    $eta  = round($rate * $left, 2);

    $elapsed = $now - $this->statusStartTime;

    $eta     = $this->convertTime($eta);
    $elapsed = $this->convertTime($elapsed);

    $memory = memory_get_usage(true);
    if ($memory > 1024 * 1024 * 1024 * 10) {
      $memory = sprintf('%.2fGB', $memory / 1024 / 1024 / 1024);
    } elseif ($memory > 1024 * 1024 * 10) {
      $memory = sprintf('%.2fMB', $memory / 1024 / 1024);
    } elseif ($memory > 1024 * 10) {
      $memory = sprintf('%.2fkB', $memory / 1024);
    } else {
      $memory = sprintf('%.2fB', $memory);
    }

    $statusBar .= ' [ remaining: ' . $eta . ' | elapsed: ' . $elapsed . ' ] (memory: ' . $memory . ')     ';

    echo $statusBar;

    // when done, send a newline
    if ($done == $total) {
      $this->statusStartTime = null;
      echo "\n";
    }
  }

  /**
   * Convert time into human format
   *
   * @param int $time
   * @return string
   */
  private function convertTime(int $time): string
  {
    $string = '';

    if ($time > 3600) {
      $h      = (int)abs($time / 3600);
      $time   -= ($h * 3600);
      $string .= $h . ' h ';
    }

    if ($time > 60) {
      $m      = (int)abs($time / 60);
      $time   -= ($m * 60);
      $string .= $m . ' min ';
    }

    $string .= (int)$time . ' sec';

    return $string;
  }
}
