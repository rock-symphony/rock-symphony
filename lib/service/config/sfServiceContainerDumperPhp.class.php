<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfServiceContainerDumperPhp dumps a service container as a PHP code.
 *
 * @package    symfony
 * @subpackage service
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Ivan Voskoboinyk <ivan.voskoboinyk@gmail.com>
 */
class sfServiceContainerDumperPhp implements sfServiceContainerDumperInterface
{
  /**
   * Dumps the service container initialization as PHP code.
   *
   * @param  sfServiceContainerBuilder $builder
   * @param  array                     $options
   *
   * @return string A PHP class representing of the service container
   */
  public function dump(sfServiceContainerBuilder $builder, array $options = array())
  {
    $unsupported_options = array_diff(array_keys($options), ['class']);

    if (count($unsupported_options) > 0) {
      throw new InvalidArgumentException('Unsupported options given: ' . implode(', ', $unsupported_options));
    }

    $class = isset($options['class']) ? $options['class'] : '\sfServiceContainer';

    return
      $this->createClosureFunction(
        $class,
        $this->addServices($builder)
      );
  }

  /**
   * @param string $id
   * @param sfServiceDefinition $definition
   * @return string
   */
  protected function addServiceInclude($id, $definition)
  {
    if (null !== $definition->getFile())
    {
      return sprintf("    require_once %s;\n\n", $this->dumpValue($definition->getFile()));
    }

    return '';
  }

  /**
   * @param string $id
   * @param sfServiceDefinition $definition
   * @return string
   */
  protected function addServiceReturn($id, $definition)
  {
    return "    return \$instance;\n";
  }

  /**
   * @param string $id
   * @param sfServiceDefinition $definition
   * @return string
   */
  protected function addServiceInstance($id, $definition)
  {
    $class = $this->dumpValue($definition->getClass());

    $arguments = $definition->getArguments();

    if (null !== $definition->getConstructor())
    {
      return sprintf("    \$instance = \$container->call(array(%s, '%s'), %s);\n", $class, $definition->getConstructor(), $this->dumpValue($arguments));
    }
    else
    {
      return sprintf("    \$instance = \$container->construct(%s, %s);\n", $class, $this->dumpValue($arguments));
    }
  }

  /**
   * @param string $id
   * @param sfServiceDefinition $definition
   * @return string
   */
  protected function addServiceMethodCalls($id, $definition)
  {
    $calls = '';
    foreach ($definition->getMethodCalls() as $call)
    {
      $arguments = $call[1];

      $calls .= sprintf("    \$container->call(array(\$instance, %s), %s);\n", $this->dumpValue($call[0]), $this->dumpValue($arguments));
    }

    return $calls;
  }

  /**
   * @param string $id
   * @param sfServiceDefinition $definition
   * @return string
   */
  protected function addServiceConfigurator($id, $definition)
  {
    if (!$callable = $definition->getConfigurator())
    {
      return '';
    }

    if (is_array($callable))
    {
      if (is_object($callable[0]) && $callable[0] instanceof sfServiceReference)
      {
        return sprintf("    %s->%s(\$instance);\n", $this->getServiceCall((string) $callable[0]), $callable[1]);
      }
      else
      {
        return sprintf("    \$container->call(%s, array(\$instance));\n", $this->dumpValue($callable));
      }
    }
    else
    {
      return sprintf("    %s(\$instance);\n", $callable);
    }
  }

  /**
   * @param string $id
   * @param \sfServiceDefinition $definition
   * @return string
   */
  protected function addService($id, $definition)
  {
    $code =
      $this->addServiceInclude($id, $definition).
      $this->addServiceInstance($id, $definition).
      $this->addServiceMethodCalls($id, $definition).
      $this->addServiceConfigurator($id, $definition).
      $this->addServiceReturn($id, $definition)
    ;

    if ($definition->isShared()) {
      return $this->bindSingletonResolver($id, $code);
    }

    return $this->bindResolver($id, $code);
  }

  protected function addServiceAlias($alias, $id)
  {
    return sprintf("  \$container->alias(%s, %s);\n", $this->dumpValue($id), $this->dumpValue($alias));
  }

  protected function addServices(sfServiceContainerBuilder $builder)
  {
    $code = '';
    foreach ($builder->getServiceDefinitions() as $id => $definition)
    {
      $code .=
        "\n" .
        "  // $id\n" .
        $this->addService($id, $definition);
    }

    foreach ($builder->getAliases() as $alias => $id)
    {
      $code .=
        "\n" .
        "  // $alias => $id\n" .
        $this->addServiceAlias($alias, $id);
    }

    return $code;
  }

  /**
   * @param string $class
   * @param string $body
   * @return string
   */
  protected function createClosureFunction($class, $body)
  {
    $body = rtrim($body);

    $template = <<<EOF
/**
 * @return \sfServiceContainer
 */
return function() {
  \$container = new %s();
%s
  return \$container;
};

EOF;

    return sprintf($template, $class, $body ? "{$body}\n" : '');
  }

  /**
   * @param string $id
   * @param string $code
   * @return string
   */
  protected function bindResolver($id, $code)
  {
    $code = rtrim($code);

    $template = <<<EOL
  \$container->bindResolver(%s, function(\sfServiceContainer \$container) {
    %s
  });

EOL;

    return sprintf($template, $this->dumpValue($id), ltrim($code));
  }

  /**
   * @param string $id
   * @param string $code
   * @return string
   */
  protected function bindSingletonResolver($id, $code)
  {
    $code = rtrim($code);

    $template = <<<EOL
  \$container->bindSingletonResolver(%s, function(\sfServiceContainer \$container) {
    %s
  });

EOL;

    return sprintf($template, $this->dumpValue($id), ltrim($code));
  }

  /**
   * Dump any supported dumpable value to string representation
   *
   * @throws RuntimeException if unable to dump the given value.
   *
   * @internal Do not use this method from outside. It's not a part of API.
   *
   * @param mixed $value
   * @return string
   */
  protected function dumpValue($value)
  {
    if (is_array($value))
    {
      $code = array();
      foreach ($value as $k => $v)
      {
        $code[] = sprintf("%s => %s", $this->dumpValue($k), $this->dumpValue($v));
      }

      return sprintf("array(%s)", implode(', ', $code));
    }
    elseif ($value instanceof sfServiceReference)
    {
      return $this->getServiceCall($value->getServiceId());
    }
    elseif ($value instanceof sfServiceParameter)
    {
      return sprintf("sfConfig::get('%s')", strtolower($value->getParameterName()));
    }
    elseif ($value instanceof sfServiceParameterStringExpression)
    {
      // concat dumpValue of each expression part
      return implode('.', array_map(function($value) { return $this->dumpValue($value); }, $value->getParts()));
    }
    elseif (is_object($value) || is_resource($value))
    {
      throw new RuntimeException('Unable to dump a service container if a parameter is an object or a resource.');
    }
    else
    {
      return var_export($value, true);
    }
  }

  protected function getServiceCall($id)
  {
    if ('service_container' == $id)
    {
      return '$this';
    }

    return sprintf('$this->get(%s)', $this->dumpValue($id));
  }
}
