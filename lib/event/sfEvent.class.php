<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfEvent.
 *
 * @package    symfony
 * @subpackage event
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfEvent.class.php 8698 2008-04-30 16:35:28Z fabien $
 */
class sfEvent
{
  /** @var mixed */
  protected mixed $subject;

  protected string $name;

  /** @var array<string,mixed> */
  protected array $parameters;

  /** @var mixed */
  protected mixed $value = null;

  protected bool $processed = false;

  /**
   * Constructs a new sfEvent.
   *
   * @param mixed   $subject    The subject
   * @param string  $name       The event name
   * @param array   $parameters An array of parameters
   */
  public function __construct(mixed $subject, string $name, array $parameters = [])
  {
    $this->subject = $subject;
    $this->name = $name;
    $this->parameters = $parameters;
  }

  /**
   * Returns the subject.
   *
   * @return mixed The subject
   */
  public function getSubject(): mixed
  {
    return $this->subject;
  }

  /**
   * Returns the event name.
   *
   * @return string The event name
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Sets the return value for this event.
   *
   * @param mixed  $value The return value
   */
  public function setReturnValue(mixed $value): void
  {
    $this->value = $value;
  }

  /**
   * Returns the return value.
   *
   * @return mixed The return value
   */
  public function getReturnValue(): mixed
  {
    return $this->value;
  }

  /**
   * Sets the processed flag.
   *
   * @param boolean $processed The processed flag value
   */
  public function setProcessed(bool $processed): void
  {
    $this->processed = $processed;
  }

  /**
   * Returns whether the event has been processed by a listener or not.
   *
   * @return bool true if the event has been processed, false otherwise
   */
  public function isProcessed(): bool
  {
    return $this->processed;
  }

  /**
   * Returns the event parameters.
   *
   * @return array The event parameters
   */
  public function getParameters(): array
  {
    return $this->parameters;
  }

  /**
   * Checks if the given parameter is set for the event instance.
   *
   * @param string  $name The parameter name
   * @return bool
   */
  public function hasParameter(string $name): bool
  {
    return array_key_exists($name, $this->parameters);
  }

  /**
   * Returns the given event parameter value, falling back to the default, if not present.
   *
   * @param string  $name    The parameter name
   * @param mixed   $default Fallback value
   *
   * @return mixed
   */
  public function getParameter(string $name, mixed $default = null): mixed
  {
    return $this->parameters[$name] ?? $default;
  }
}
