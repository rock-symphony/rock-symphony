<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represents a command line option.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCommandOption
{
  public const PARAMETER_NONE     = 1;
  public const PARAMETER_REQUIRED = 2;
  public const PARAMETER_OPTIONAL = 4;

  public const IS_ARRAY = 8;

  /**
   * The option name
   */
  protected string $name;

  /**
   * The shortcut
   */
  protected string | null $shortcut;

  /**
   * The option mode
   *
   * @see self::PARAMETER_REQUIRED
   * @see self::PARAMETER_NONE
   * @see self::PARAMETER_OPTIONAL
   * @see self::IS_ARRAY
   */
  protected int $mode;

  /**
   * The default value
   *
   * (must be null for self::PARAMETER_REQUIRED or self::PARAMETER_NONE)
   */
  protected mixed $default;

  /**
   * Help text
   */
  protected string $help;

  /**
   * @param string      $name      The option name
   * @param string|null $shortcut  The shortcut
   * @param int|null    $mode      The option mode: self::PARAMETER_REQUIRED, self::PARAMETER_NONE or self::PARAMETER_OPTIONAL
   * @param string      $help      A help text
   * @param mixed       $default   The default value (must be null for self::PARAMETER_REQUIRED or self::PARAMETER_NONE)
   *
   * @throws sfCommandException
   */
  public function __construct(string $name, string | null $shortcut = null, int | null $mode = null, string $help = '', mixed $default = null)
  {
    if ('--' == substr($name, 0, 2)) {
      $name = substr($name, 2);
    }

    if (empty($shortcut)) {
      $shortcut = null;
    }

    if (null !== $shortcut) {
      if ('-' == $shortcut[0]) {
        $shortcut = substr($shortcut, 1);
      }
    }

    if (null === $mode) {
      $mode = self::PARAMETER_NONE;
    } elseif (is_string($mode) || $mode > 15) {
      throw new sfCommandException(sprintf('Option mode "%s" is not valid.', $mode));
    }

    $this->name     = $name;
    $this->shortcut = $shortcut;
    $this->mode     = $mode;
    $this->help     = $help;

    $this->setDefault($default);
  }

  /**
   * Returns the shortcut.
   *
   * @return string|null The shortcut
   */
  public function getShortcut(): ?string
  {
    return $this->shortcut;
  }

  /**
   * Returns the name.
   *
   * @return string The name
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Returns true if the option accepts a parameter.
   *
   * @return bool true if parameter mode is not self::PARAMETER_NONE, false otherwise
   */
  public function acceptParameter(): bool
  {
    return $this->isParameterRequired() || $this->isParameterOptional();
  }

  /**
   * Returns true if the option requires a parameter.
   *
   * @return bool true if parameter mode is self::PARAMETER_REQUIRED, false otherwise
   */
  public function isParameterRequired(): bool
  {
    return self::PARAMETER_REQUIRED === (self::PARAMETER_REQUIRED & $this->mode);
  }

  /**
   * Returns true if the option takes an optional parameter.
   *
   * @return bool true if parameter mode is self::PARAMETER_OPTIONAL, false otherwise
   */
  public function isParameterOptional(): bool
  {
    return self::PARAMETER_OPTIONAL === (self::PARAMETER_OPTIONAL & $this->mode);
  }

  /**
   * Returns true if the option can take multiple values.
   *
   * @return bool true if mode is self::IS_ARRAY, false otherwise
   */
  public function isArray(): bool
  {
    return self::IS_ARRAY === (self::IS_ARRAY & $this->mode);
  }

  /**
   * Sets the default value.
   *
   * @param mixed $default  The default value
   *
   * @throws sfCommandException
   */
  public function setDefault(mixed $default = null): void
  {
    if (self::PARAMETER_NONE === (self::PARAMETER_NONE & $this->mode) && null !== $default) {
      throw new sfCommandException('Cannot set a default value when using sfCommandOption::PARAMETER_NONE mode.');
    }

    if ($this->isArray()) {
      if (null === $default) {
        $default = [];
      } elseif ( ! is_array($default)) {
        throw new sfCommandException('A default value for an array option must be an array.');
      }
    }

    $this->default = $this->acceptParameter() ? $default : false;
  }

  /**
   * Returns the default value.
   *
   * @return mixed The default value
   */
  public function getDefault(): mixed
  {
    return $this->default;
  }

  /**
   * Returns the help text.
   *
   * @return string The help text
   */
  public function getHelp(): string
  {
    return $this->help;
  }
}
