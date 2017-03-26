<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfServiceContainerBuilder is used to build services.yml configuration before dumping it.
 *
 * @package    symfony
 * @subpackage service
 */
class sfServiceContainerBuilder
{
  /** @var mixed[] */
  protected $parameters = array();
  /** @var sfServiceDefinition[] */
  protected $definitions = array();
  /** @var string[] */
  protected $aliases = array();

  /**
   * Sets a service definition.
   *
   * @param  string              $id         The service identifier
   * @param  sfServiceDefinition $definition A sfServiceDefinition instance
   *
   * @return sfServiceDefinition
   */
  public function setServiceDefinition($id, sfServiceDefinition $definition)
  {
    unset($this->aliases[$id]);

    return $this->definitions[$id] = $definition;
  }

  /**
   * Returns true if a service definition exists under the given identifier.
   *
   * @param  string  $id The service identifier
   *
   * @return boolean
   */
  public function hasServiceDefinition($id)
  {
    return array_key_exists($id, $this->definitions);
  }

  /**
   * Gets a service definition.
   *
   * @param  string  $id The service identifier
   *
   * @return sfServiceDefinition A sfServiceDefinition instance
   *
   * @throw InvalidArgumentException if the service definition does not exist
   */
  public function getServiceDefinition($id)
  {
    if (!$this->hasServiceDefinition($id))
    {
      throw new InvalidArgumentException(sprintf('The service definition "%s" does not exist.', $id));
    }

    return $this->definitions[$id];
  }

  /**
   * Gets all service definitions.
   *
   * @return sfServiceDefinition[]
   */
  public function getServiceDefinitions()
  {
    return $this->definitions;
  }

  /**
   * @param string $alias
   * @return bool
   */
  public function hasAlias($alias)
  {
    return isset($this->aliases[$alias]);
  }

  /**
   * @param string $alias
   * @return string
   */
  public function getAlias($alias)
  {
    return $this->aliases[$alias];
  }

  /**
   * Sets an alias for an existing service.
   *
   * @param string $alias The alias to create
   * @param string $id    The service to alias
   */
  public function setAlias($alias, $id)
  {
    $this->aliases[$alias] = $id;
  }

  /**
   * Gets a map all declared aliases ($alias => $id)
   *
   * @return string[]
   */
  public function getAliases()
  {
    return $this->aliases;
  }

  /**
   * Gets all aliases of a given service
   *
   * @param string $id
   *
   * @return array|\string[]
   */
  public function getAliasesOfService($id)
  {
    $aliases = array_filter($this->aliases, function ($alias_id) use ($id) {
      return $alias_id === $id;
    });

    return $aliases;
  }

  /**
   * Sets a service container parameter.
   *
   * @param string $name The parameter name
   * @param mixed $value The parameter value
   */
  public function setParameter($name, $value)
  {
    $this->parameters[$name] = $value;
  }

  /**
   * Returns true if a service parameter exists
   *
   * @param string $name The parameter name
   *
   * @return boolean
   */
  public function hasParameter($name)
  {
    return isset($this->parameters[$name]);
  }

  /**
   * Sets a service container parameter.
   *
   * @param string $name The parameter name
   * @return mixed
   */
  public function getParameter($name)
  {
    return $this->parameters[$name];
  }

  /**
   * Gets the service container parameters.
   *
   * @return array An array of parameters
   */
  public function getParameters()
  {
    return $this->parameters;
  }
}
