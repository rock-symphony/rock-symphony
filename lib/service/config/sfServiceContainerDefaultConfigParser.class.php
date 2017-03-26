<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfServiceContainerDefaultConfigParser parses a service definitions array
 * and translates it to sfServiceContainerBuilder method calls.
 *
 * @package    symfony
 * @subpackage service
 * @author     Jerome Macias <jmacias@groupe-exp.com>
 */
class sfServiceContainerDefaultConfigParser implements sfServiceContainerConfigParserInterface
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
    list($definitions, $parameters) = $this->doLoad($config);

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
      $this->builder->setParameter($key, $this->builder->resolveValue($value));
    }

    return $this->builder;
  }

  protected function doLoad($content)
  {
    $this->validate($content);

    $parameters = array();
    $definitions = array();

    // parameters
    if (isset($content['parameters']))
    {
      foreach ($content['parameters'] as $key => $value)
      {
        $parameters[strtolower($key)] = $this->resolveServices($value);
      }
    }

    // services
    if (isset($content['services']))
    {
      foreach ($content['services'] as $id => $service)
      {
        $definitions[$id] = $this->parseDefinition($service);
      }
    }

    return array($definitions, $parameters);
  }

  protected function validate($content)
  {
    if (!is_array($content))
    {
      throw new InvalidArgumentException('The service definition is not valid.');
    }

    foreach (array_keys($content) as $key)
    {
      if (!in_array($key, array('parameters', 'services')))
      {
        throw new InvalidArgumentException(sprintf('The service defintion is not valid ("%s" is not recognized).', $key));
      }
    }

    return $content;
  }

  protected function parseDefinition($service)
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
      $definition->setArguments($this->resolveServices($service['arguments']));
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
        $definition->addMethodCall($call[0], $this->resolveServices($call[1]));
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
