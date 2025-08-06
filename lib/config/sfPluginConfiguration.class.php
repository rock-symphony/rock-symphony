<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPluginConfiguration represents a configuration for a symfony plugin.
 *
 * @package    symfony
 * @subpackage config
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfPluginConfiguration
{
  /** @var \sfProjectConfiguration */
  protected sfProjectConfiguration $configuration;

  /** @var \sfEventDispatcher */
  protected sfEventDispatcher $dispatcher;

  /** @var string */
  protected string $name;

  /** @var string */
  protected string $rootDir;

  /**
   * @param sfProjectConfiguration $configuration  The project configuration
   * @param string|null            $rootDir        The plugin root directory
   * @param string|null            $name           The plugin name
   */
  public function __construct(sfProjectConfiguration $configuration, string | null $rootDir = null, string | null $name = null)
  {
    $this->configuration = $configuration;
    $this->dispatcher    = $configuration->getEventDispatcher();
    $this->rootDir       = null === $rootDir ? $this->guessRootDir() : realpath($rootDir);
    $this->name          = null === $name ? $this->guessName() : $name;

    $this->setup();
    $this->configure();

    if ( ! $this->configuration instanceof sfApplicationConfiguration) {
      $this->initialize();
    }
  }

  /**
   * Sets up the plugin.
   *
   * This method can be used when creating a base plugin configuration class for other plugins to extend.
   */
  public function setup(): void
  {
  }

  /**
   * Configures the plugin.
   */
  public function configure(): void
  {
  }

  /**
   * Initializes the plugin.
   */
  public function initialize(): void
  {
  }

  /**
   * Returns the plugin root directory.
   *
   * @return string
   */
  public function getRootDir(): string
  {
    return $this->rootDir;
  }

  /**
   * Returns the plugin name.
   *
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Guesses the plugin root directory.
   *
   * @return string
   */
  protected function guessRootDir(): string
  {
    $r = new ReflectionClass(get_class($this));

    return realpath(dirname($r->getFileName()) . '/..');
  }

  /**
   * Guesses the plugin name.
   *
   * @return string
   */
  protected function guessName(): string
  {
    return substr(get_class($this), 0, -13);
  }
}
