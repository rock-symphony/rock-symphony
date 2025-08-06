<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Represent a set of command line arguments.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCommandArgumentSet
{
  /**
   * @var array<string,sfCommandArgument>
   */
  protected array $arguments = [];

  protected int $requiredCount = 0;

  protected bool $hasAnArrayArgument = false;

  protected bool $hasOptional = false;

  /**
   * @param sfCommandArgument[] $arguments
   */
  public function __construct(array $arguments = [])
  {
    $this->setArguments($arguments);
  }

  /**
   * Sets the sfCommandArgument objects.
   *
   * @param sfCommandArgument[] $arguments
   */
  public function setArguments(array $arguments): void
  {
    $this->arguments     = [];
    $this->requiredCount = 0;
    $this->hasOptional   = false;

    $this->addArguments($arguments);
  }

  /**
   * Add an array of sfCommandArgument objects.
   *
   * @param sfCommandArgument[] $arguments
   */
  public function addArguments(array $arguments): void
  {
    foreach ($arguments as $argument) {
      $this->addArgument($argument);
    }
  }

  /**
   * Add an sfCommandArgument object.
   *
   * @param sfCommandArgument $argument
   *
   * @throws sfCommandException
   */
  public function addArgument(sfCommandArgument $argument): void
  {
    if (isset($this->arguments[$argument->getName()])) {
      throw new sfCommandException(sprintf('An argument with name "%s" already exist.', $argument->getName()));
    }

    if ($this->hasAnArrayArgument) {
      throw new sfCommandException('Cannot add an argument after an array argument.');
    }

    if ($argument->isRequired() && $this->hasOptional) {
      throw new sfCommandException('Cannot add a required argument after an optional one.');
    }

    if ($argument->isArray()) {
      $this->hasAnArrayArgument = true;
    }

    if ($argument->isRequired()) {
      ++$this->requiredCount;
    } else {
      $this->hasOptional = true;
    }

    $this->arguments[$argument->getName()] = $argument;
  }

  /**
   * Returns an argument by name.
   *
   * @param string $name  The argument name
   *
   * @return sfCommandArgument
   *
   * @throws sfCommandException
   */
  public function getArgument(string $name): sfCommandArgument
  {
    if ( ! $this->hasArgument($name)) {
      throw new sfCommandException(sprintf('The "%s" argument does not exist.', $name));
    }

    return $this->arguments[$name];
  }

  /**
   * Returns true if an argument object exists by name.
   *
   * @param string $name  The argument name
   *
   * @return bool true if the argument object exists, false otherwise
   */
  public function hasArgument(string $name): bool
  {
    return isset($this->arguments[$name]);
  }

  /**
   * Gets the array of sfCommandArgument objects.
   *
   * @return array<string,sfCommandArgument>
   */
  public function getArguments(): array
  {
    return $this->arguments;
  }

  /**
   * Returns the number of arguments.
   *
   * @return int The number of arguments
   */
  public function getArgumentCount(): int
  {
    return $this->hasAnArrayArgument ? PHP_INT_MAX : count($this->arguments);
  }

  /**
   * Returns the number of required arguments.
   *
   * @return int The number of required arguments
   */
  public function getArgumentRequiredCount(): int
  {
    return $this->requiredCount;
  }

  /**
   * Gets the default values.
   *
   * @return array<string,mixed> An array of default values
   */
  public function getDefaults(): array
  {
    $values = [];
    foreach ($this->arguments as $argument) {
      $values[$argument->getName()] = $argument->getDefault();
    }

    return $values;
  }
}
