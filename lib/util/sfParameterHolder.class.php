<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfParameterHolder provides a base class for managing parameters.
 *
 * Parameters, in this case, are used to extend classes with additional data
 * that requires no additional logic to manage.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfParameterHolder
{
  /** @var array<string, mixed> */
  protected array $parameters = [];

  /**
   * The constructor for sfParameterHolder.
   */
  public function __construct(array $parameters = [])
  {
    $this->parameters = $parameters;
  }

  /**
   * Clears all parameters associated with this request.
   */
  public function clear(): void
  {
    $this->parameters = [];
  }

  /**
   * Retrieves a parameter.
   *
   * @param  string $name     A parameter name
   * @param  mixed  $default  A default parameter value
   *
   * @return mixed A parameter value, if the parameter exists, otherwise null
   */
  public function & get(string $name, $default = null)
  {
    if (array_key_exists($name, $this->parameters))
    {
      $value = & $this->parameters[$name];
    }
    else
    {
      $value = $default;
    }

    return $value;
  }

  /**
   * Retrieves an array of parameter names.
   *
   * @return string[] An indexed array of parameter names
   */
  public function getNames(): array
  {
    return array_keys($this->parameters);
  }

  /**
   * Retrieves an array of parameters.
   *
   * @return array<string, mixed> An associative array of parameters
   */
  public function & getAll(): array
  {
    return $this->parameters;
  }

  /**
   * Indicates whether a parameter exists.
   *
   * @param  string $name  A parameter name
   *
   * @return bool true, if the parameter exists, otherwise false
   */
  public function has(string $name): bool
  {
    return array_key_exists($name, $this->parameters);
  }

  /**
   * Remove a parameter.
   *
   * @param  string $name     A parameter name
   * @param  mixed  $default  A default parameter value
   *
   * @return mixed A parameter value, if the parameter was removed, otherwise $default
   */
  public function remove(string $name, $default = null)
  {
    $retval = $default;

    if (array_key_exists($name, $this->parameters))
    {
      $retval = $this->parameters[$name];
      unset($this->parameters[$name]);
    }

    return $retval;
  }

  /**
   * Sets a parameter.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string $name   A parameter name
   * @param mixed  $value  A parameter value
   */
  public function set(string $name, $value): void
  {
    $this->parameters[$name] = $value;
  }

  /**
   * Sets a parameter by reference.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string $name   A parameter name
   * @param mixed  $value  A reference to a parameter value
   */
  public function setByRef(string $name, & $value): void
  {
    $this->parameters[$name] =& $value;
  }

  /**
   * Sets an array of parameters.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array $parameters  An associative array of parameters and their associated values
   */
  public function add(array $parameters): void
  {
    foreach ($parameters as $key => $value)
    {
      $this->parameters[$key] = $value;
    }
  }

  /**
   * Sets an array of parameters by reference.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array $parameters  An associative array of parameters and references to their associated values
   */
  public function addByRef(array & $parameters): void
  {
    foreach ($parameters as $key => &$value)
    {
      $this->parameters[$key] =& $value;
    }
  }

  /**
   * Serializes the current instance.
   */
  public function __serialize(): array
  {
    return ['parameters' => $this->parameters];
  }

  /**
   * Unserializes a sfParameterHolder instance.
   *
   * @param array $serialized  A serialized sfParameterHolder instance
   */
  public function __unserialize(array $serialized)
  {
    $this->parameters = $serialized['parameters'] ?? [];
  }
}
