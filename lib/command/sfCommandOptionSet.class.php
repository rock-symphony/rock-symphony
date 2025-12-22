<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represent a set of command line options.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCommandOptionSet
{
  /**
   * @var array<string,sfCommandOption>
   */
  protected array $options = [];

  protected array $shortcuts = [];

  /**
   * @param sfCommandOption[] $options
   */
  public function __construct(array $options = [])
  {
    $this->setOptions($options);
  }

  /**
   * Sets the sfCommandOption objects.
   *
   * @param sfCommandOption[] $options
   */
  public function setOptions(array $options = []): void
  {
    $this->options   = [];
    $this->shortcuts = [];

    $this->addOptions($options);
  }

  /**
   * Add an array of sfCommandOption objects.
   *
   * @param array $options  An array of sfCommandOption objects
   */
  public function addOptions(array $options = []): void
  {
    foreach ($options as $option) {
      $this->addOption($option);
    }
  }

  /**
   * Add a sfCommandOption objects.
   *
   * @param sfCommandOption $option  A sfCommandOption object
   *
   * @throws sfCommandException
   */
  public function addOption(sfCommandOption $option): void
  {
    if (isset($this->options[$option->getName()])) {
      throw new sfCommandException(sprintf('An option named "%s" already exist.', $option->getName()));
    }

    if (isset($this->shortcuts[$option->getShortcut()])) {
      throw new sfCommandException(sprintf('An option with shortcut "%s" already exist.', $option->getShortcut()));
    }

    $this->options[$option->getName()] = $option;

    if ($option->getShortcut()) {
      $this->shortcuts[$option->getShortcut()] = $option->getName();
    }
  }

  /**
   * Returns an option by name.
   *
   * @param string $name  The option name
   *
   * @return sfCommandOption A sfCommandOption object
   *
   * @throws sfCommandException
   */
  public function getOption(string $name): sfCommandOption
  {
    if ( ! $this->hasOption($name)) {
      throw new sfCommandException(sprintf('The "--%s" option does not exist.', $name));
    }

    return $this->options[$name];
  }

  /**
   * Returns true if an option object exists by name.
   *
   * @param string $name  The option name
   *
   * @param bool true if the option object exists, false otherwise
   */
  public function hasOption(string $name): bool
  {
    return isset($this->options[$name]);
  }

  /**
   * Gets the array of sfCommandOption objects.
   *
   * @return array<string,sfCommandOption>
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * Returns true if an option object exists by shortcut.
   *
   * @param string $name  The option shortcut
   *
   * @param bool true if the option object exists, false otherwise
   */
  public function hasShortcut(string $name): bool
  {
    return isset($this->shortcuts[$name]);
  }

  /**
   * Gets an option by shortcut.
   *
   * @param string $shortcut
   *
   * @return sfCommandOption A sfCommandOption object
   */
  public function getOptionForShortcut(string $shortcut): sfCommandOption
  {
    return $this->getOption($this->shortcutToName($shortcut));
  }

  /**
   * Gets an array of default values.
   *
   * @return array<string,mixed> An array of all default values
   */
  public function getDefaults(): array
  {
    $values = [];

    foreach ($this->options as $option) {
      $values[$option->getName()] = $option->getDefault();
    }

    return $values;
  }

  /**
   * Returns the option name given a shortcut.
   *
   * @param string $shortcut  The shortcut
   *
   * @return string The option name
   *
   * @throws sfCommandException
   */
  protected function shortcutToName(string $shortcut): string
  {
    if ( ! isset($this->shortcuts[$shortcut])) {
      throw new sfCommandException(sprintf('The "-%s" option does not exist.', $shortcut));
    }

    return $this->shortcuts[$shortcut];
  }
}
