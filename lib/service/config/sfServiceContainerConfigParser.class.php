<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfServiceContainerConfigParser parses a service definitions array
 * and translates it to sfServiceContainerBuilder method calls.
 *
 * @package    symfony
 * @subpackage service
 * @author     Jerome Macias <jmacias@groupe-exp.com>
 */
class sfServiceContainerConfigParser implements sfServiceContainerConfigParserInterface
{
  /** @var sfServiceContainerBuilder */
  protected $builder;

  /**
   * Constructor.
   *
   * @param sfServiceContainerBuilder $builder A builder instance
   */
  public function __construct(sfServiceContainerBuilder $builder = null)
  {
    $this->builder = $builder ?: new sfServiceContainerBuilder();
  }

  /**
   * @inheritdoc
   */
  public function parse(array $config)
  {
    list($definitions, $parameters) = $this->doParse($config);

    return $this->doBuild($definitions, $parameters);
  }

  /**
   * @param array $definitions
   * @param array $parameters
   * @return sfServiceContainerBuilder
   */
  protected function doBuild($definitions, $parameters)
  {
    foreach ($definitions as $id => $definition)
    {
      if (is_string($definition))
      {
        $this->builder->setAlias($id, $definition);
      }
      else
      {
        $this->builder->setServiceDefinition($id, $definition);
      }
    }

    foreach ($parameters as $key => $value)
    {
      $this->builder->setParameter($key, $value);
    }

    return $this->builder;
  }

  /**
   * @param array|mixed $config Config array (may be invalid type value though, exception will be thrown)
   *
   * @throws InvalidArgumentException if $config value or structure is invalid
   *
   * @return array
   */
  protected function doParse($config)
  {
    $this->validate($config);

    $parameters = array();
    $definitions = array();

    // parameters
    if (isset($config['parameters']))
    {
      foreach ($config['parameters'] as $key => $value)
      {
        $parameters[strtolower($key)] = $this->parseParameterValue($value);
      }
    }

    // services
    if (isset($config['services']))
    {
      foreach ($config['services'] as $id => $service)
      {
        $definitions[$id] = $this->parseServiceDefinition($service);
      }
    }

    return array($definitions, $parameters);
  }

  protected function validate($config)
  {
    if (!is_array($config))
    {
      throw new InvalidArgumentException('The service definition is not valid.');
    }

    foreach (array_keys($config) as $key)
    {
      if (!in_array($key, array('parameters', 'services')))
      {
        throw new InvalidArgumentException(sprintf('The service definition is not valid ("%s" is not recognized).', $key));
      }
    }

    return $config;
  }

  /**
   * Recursively parses any value for service or parameter references
   *
   * @param mixed $value
   *
   * @return mixed|sfServiceParameter|class
   */
  protected function parseParameterValue($value)
  {
    // Process arrays recursively
    if (is_array($value))
    {
      foreach ($value as $k => $v)
      {
        $value[$k] = $this->parseParameterValue($v);
      }
      return $value;
    }

    if (! is_string($value) || strlen($value) === 0) {
      // return non-string values as is (int, bool, null)
      return $value;
    }

    // Allow escaping leading @ with @@
    if (substr($value, 0, 2) === '@')
    {
      return '@' . substr($value, 2);
    }

    // Replace @service reference
    if ($value[0] === '@')
    {
      return new sfServiceReference(substr($value, 1));
    }

    // Short-circuit %-free strings
    if (strpos($value, '%') === false)
    {
      return $value;
    }

    // Short-circuit %...% strings
    if (preg_match('/^%([^%]+)%$/', $value, $match))
    {
      return new sfServiceParameter($value);
    }

    // Deep-process the string
    return $this->parseParameterStringExpression($value);
  }

  /**
   * Parses a parameter-referencing string into sfServiceParameterStringExpression.
   *
   * @see sfServiceParameterStringExpression
   *
   * @param string $string
   *
   * @return string|class
   */
  protected function parseParameterStringExpression($string)
  {
    $n = preg_match_all('/(?<!%)(%)([^%]+)\1/', $string, $matches, PREG_OFFSET_CAPTURE);
    // Shortcut for "No matches"
    if ($n === 0) {
      return $string;
    }

    $parts = array();
    $last_offset = 0;

    foreach ($matches[0] as $match)
    {
      list($matched_text, $offset) = $match;
      // Add prefixed plain-text part
      if ($offset > 0)
      {

        $parts[] = substr($string, $last_offset, $offset - $last_offset);
      }
      // Add service parameter reference
      $parts[] = new sfServiceParameter(substr($matched_text, 1, -1));
      // Advance last_offset
      $last_offset = $offset;
    }

    // Add suffix plain-text part
    if ($last_offset < (strlen($string) - 1))
    {
      $parts[] = substr($string, $last_offset);
    }

    return $parts;
  }

  /**
   * @param array|string $service
   *
   * @return sfServiceDefinition|string
   */
  protected function parseServiceDefinition($service)
  {
    if (is_string($service) && 0 === strpos($service, '@'))
    {
      return substr($service, 1);
    }

    $definition = new sfServiceDefinition($service['class']);

    if (isset($service['shared']))
    {
      $definition->setShared($service['shared']);
    }

    if (isset($service['constructor']))
    {
      $definition->setConstructor($service['constructor']);
    }

    if (isset($service['file']))
    {
      $definition->setFile($service['file']);
    }

    if (isset($service['arguments']))
    {
      $definition->setArguments($this->parseParameterValue($service['arguments']));
    }

    if (isset($service['configurator']))
    {
      if (is_string($service['configurator']))
      {
        $definition->setConfigurator($service['configurator']);
      }
      else
      {
        $definition->setConfigurator(array($this->resolveServices($service['configurator'][0]), $service['configurator'][1]));
      }
    }

    if (isset($service['calls']))
    {
      foreach ($service['calls'] as $call)
      {
        $definition->addMethodCall($call[0], $this->parseParameterValue($call[1]));
      }
    }

    return $definition;
  }

  protected function resolveServices($value)
  {
    if (is_array($value))
    {
      $value = array_map(array($this, 'resolveServices'), $value);
    }
    else if (is_string($value) && 0 === strpos($value, '@'))
    {
      $value = new sfServiceReference(substr($value, 1));
    }

    return $value;
  }
}
