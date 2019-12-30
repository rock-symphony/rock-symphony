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
 * sfNamespacedParameterHolder provides a class for managing parameters
 * with support for namespaces.
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
class sfNamespacedParameterHolder extends sfParameterHolder
{
  /** @var string */
  protected $default_namespace;

  /**
   * The constructor for sfNamespacedParameterHolder.
   *
   * The default namespace may be overridden at initialization as follows:
   * <code>
   * <?php
   * $mySpecialPH = new sfNamespacedParameterHolder('symfony/special');
   * ?>
   * </code>
   *
   * @param string $namespace
   */
  public function __construct(string $namespace = 'symfony/default')
  {
    $this->default_namespace = $namespace;
    parent::__construct();
  }

  /**
   * Sets the default namespace value.
   *
   * @param string $namespace  Default namespace
   * @param bool   $move       Move all values of the old default namespace to the new one or not
   */
  public function setDefaultNamespace(string $namespace, bool $move = true): void
  {
    if ($move)
    {
      if (null !== $values = $this->removeNamespace())
      {
          $this->addByRef($values, $namespace);
      }
    }

    $this->default_namespace = $namespace;
  }

  /**
   * Get the default namespace value.
   *
   * The $default_namespace is defined as 'symfony/default'.
   *
   * @return string The default namespace
   */
  public function getDefaultNamespace(): string
  {
    return $this->default_namespace;
  }

  /**
   * Clear all parameters associated with this request.
   */
  public function clear(): void
  {
    $this->parameters = null;
    $this->parameters = array();
  }

  /**
   * Retrieve a parameter with an optionally specified namespace.
   *
   * An isolated namespace may be identified by providing a value for the third
   * argument.  If not specified, the default namespace 'symfony/default' is
   * used.
   *
   * @param string $name     A parameter name
   * @param mixed  $default  A default parameter value
   * @param string $ns       A parameter namespace
   *
   * @return mixed A parameter value, if the parameter exists, otherwise null
   */
  public function & get(string $name, $default = null, string $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (isset($this->parameters[$ns][$name]))
    {
      $value = & $this->parameters[$ns][$name];
    }
    else
    {
      $value = $default;
    }

    return $value;
  }

  /**
   * Retrieve an array of parameter names from an optionally specified namespace.
   *
   * @param  string $ns  A parameter namespace.
   *
   * @return array An indexed array of parameter names, if the namespace exists, otherwise null
   */
  public function getNames(string $ns = null): array
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (isset($this->parameters[$ns]))
    {
      return array_keys($this->parameters[$ns]);
    }

    return array();
  }

  /**
   * Retrieve an array of parameter namespaces.
   *
   * @return string[] An indexed array of parameter namespaces
   */
  public function getNamespaces(): array
  {
    return array_keys($this->parameters);
  }

  /**
   * Retrieve an array of parameters, within a namespace.
   *
   * This method is limited to a namespace.  Without any argument,
   * it returns the parameters of the default namespace.  If a
   * namespace is passed as an argument, only the parameters of the
   * specified namespace are returned.
   *
   * @param  string $ns  A parameter namespace
   *
   * @return array An associative array of parameters
   */
  public function & getAll(string $ns = null): array
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $parameters = array();

    if (isset($this->parameters[$ns]))
    {
      $parameters = $this->parameters[$ns];
    }

    return $parameters;
  }

  /**
   * Indicates whether or not a parameter exists.
   *
   * @param  string $name  A parameter name
   * @param  string $ns    A parameter namespace
   *
   * @return bool true, if the parameter exists, otherwise false
   */
  public function has(string $name, string $ns = null): bool
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    return isset($this->parameters[$ns][$name]);
  }

  /**
   * Indicates whether or not A parameter namespace exists.
   *
   * @param  string $ns  A parameter namespace
   *
   * @return bool true, if the namespace exists, otherwise false
   */
  public function hasNamespace(string $ns): bool
  {
    return isset($this->parameters[$ns]);
  }

  /**
   * Remove a parameter.
   *
   * @param  string $name     A parameter name
   * @param  mixed  $default  A default parameter value
   * @param  string $ns       A parameter namespace
   *
   * @return mixed A parameter value, if the parameter was removed, otherwise default
   */
  public function remove(string $name, $default = null, string $ns = null)
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $retval = $default;

    if (isset($this->parameters[$ns]) && array_key_exists($name, $this->parameters[$ns]))
    {
      $retval = $this->parameters[$ns][$name];
      unset($this->parameters[$ns][$name]);
    }

    return $retval;
  }

  /**
   * Remove A parameter namespace and all of its associated parameters.
   *
   * @param string $ns A parameter namespace.
   *
   * @return array|null
   */
  public function & removeNamespace(string $ns = null): ?array
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    $retval = null;

    if (isset($this->parameters[$ns]))
    {
      $retval =& $this->parameters[$ns];
      unset($this->parameters[$ns]);
    }

    return $retval;
  }

  /**
   * Set a parameter.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string $name   A parameter name
   * @param mixed  $value  A parameter value
   * @param string $ns     A parameter namespace
   */
  public function set(string $name, $value, string $ns = null): void
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    $this->parameters[$ns][$name] = $value;
  }

  /**
   * Set a parameter by reference.
   *
   * If a parameter with the name already exists the value will be overridden.
   *
   * @param string $name   A parameter name
   * @param mixed  $value  A reference to a parameter value
   * @param string $ns     A parameter namespace
   */
  public function setByRef(string $name, & $value, string $ns = null): void
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    $this->parameters[$ns][$name] =& $value;
  }

  /**
   * Set an array of parameters.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array  $parameters  An associative array of parameters and their associated values
   * @param string $ns          A parameter namespace
   */
  public function add(?array $parameters, string $ns = null): void
  {
    if ($parameters === null) return;

    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    foreach ($parameters as $key => $value)
    {
      $this->parameters[$ns][$key] = $value;
    }
  }

  /**
   * Set an array of parameters by reference.
   *
   * If an existing parameter name matches any of the keys in the supplied
   * array, the associated value will be overridden.
   *
   * @param array  $parameters  An associative array of parameters and references to their associated values
   * @param string $ns          A parameter namespace
   */
  public function addByRef(array & $parameters, string $ns = null): void
  {
    if (!$ns)
    {
      $ns = $this->default_namespace;
    }

    if (!isset($this->parameters[$ns]))
    {
      $this->parameters[$ns] = array();
    }

    foreach ($parameters as $key => &$value)
    {
      $this->parameters[$ns][$key] =& $value;
    }
  }

  /**
   * Serializes the current instance.
   */
  public function serialize()
  {
    return serialize(array($this->default_namespace, $this->parameters));
  }

  /**
   * Unserializes a sfNamespacedParameterHolder instance.
   *
   * @param string $serialized  A serialized sfNamespacedParameterHolder instance
   */
  public function unserialize($serialized)
  {
    $data = unserialize($serialized);

    $this->default_namespace = $data[0];
    $this->parameters = $data[1];
  }
}
