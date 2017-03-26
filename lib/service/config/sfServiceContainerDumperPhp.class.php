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
 */
class sfServiceContainerDumperPhp implements sfServiceContainerDumperInterface
{
  /**
   * Dumps the service container as a PHP class.
   *
   * Available options:
   *
   *  * class:      The class name
   *  * base_class: The base class name
   *
   * @param  sfServiceContainerBuilder $builder
   * @param  array                     $options
   *
   * @return string A PHP class representing of the service container
   */
  public function dump(sfServiceContainerBuilder $builder, array $options = array())
  {
    $options = array_merge(array(
      'class'      => 'ProjectServiceContainer',
      'base_class' => 'sfServiceContainer',
    ), $options);

    return
      $this->startClass($options['class'], $options['base_class']).
      $this->addConstructor($builder).
      $this->addParametersMethods($builder) .
      $this->addServicesMethods($builder) .
      $this->addServices($builder).
      $this->endClass()
    ;
  }

  protected function addServicesMethods(sfServiceContainerBuilder $builder)
  {
    $services = $builder->getServiceDefinitions();
    $aliases = $builder->getAliases();

    $known_ids = array_merge(array_keys($services), array_keys($aliases));

    $code = <<<PHP
    
  /**
   * @inheritdoc
   */
  public function hasService(\$id)
  {
     if (parent::hasService(\$id)) {
       return true; 
     }
     
     return in_array(\$id, {$this->dumpValue($known_ids)});
  }
    
  /**
   * @inheritdoc
   */
  public function getService(\$id)
  {
    if (parent::hasService(\$id)) {
      return parent::getService(\$id);
    }
    
    if (in_array(\$id, {$this->dumpValue($known_ids)})) {
      \$method = 'get' . sfServiceContainer::camelize(\$id) . 'Service';
      \$instance = \$this->\$method();
      return \$instance;
    }
    
    // make parent throw "missing service" exception
    return parent::getService(\$id);
  }
  
  /**
   * @inheritdoc
   */
  public function getServiceIds()
  {  
    return array_merge(parent::getServiceIds(), {$this->dumpValue($known_ids)});
  }
    
PHP;

    return $code;
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
  }

  /**
   * @param string $id
   * @param sfServiceDefinition $definition
   * @return string
   */
  protected function addServiceReturn($id, $definition)
  {
    if ($definition->isShared())
    {
      return <<<EOF

    parent::setService('$id', \$instance);
    return \$instance;
  }

EOF;
    }
    else
    {
      return <<<EOF

    return \$instance;
  }

EOF;
    }
  }

  /**
   * @param string $id
   * @param sfServiceDefinition $definition
   * @return string
   */
  protected function addServiceInstance($id, $definition)
  {
    $class = $this->dumpValue($definition->getClass());

    $arguments = array();
    foreach ($definition->getArguments() as $value)
    {
      $arguments[] = $this->dumpValue($value);
    }

    if (null !== $definition->getConstructor())
    {
      return sprintf("    \$instance = call_user_func(array(%s, '%s')%s);\n", $class, $definition->getConstructor(), $arguments ? ', '.implode(', ', $arguments) : '');
    }
    else
    {
      if ($class != "'".$definition->getClass()."'")
      {
        return sprintf("    \$class = %s;\n    \$instance = new \$class(%s);\n", $class, implode(', ', $arguments));
      }
      else
      {
        return sprintf("    \$instance = new %s(%s);\n", $definition->getClass(), implode(', ', $arguments));
      }
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
      $arguments = array();
      foreach ($call[1] as $value)
      {
        $arguments[] = $this->dumpValue($value);
      }

      $calls .= sprintf("    \$instance->%s(%s);\n", $call[0], implode(', ', $arguments));
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
        return sprintf("    call_user_func(array(%s, '%s'), \$instance);\n", $this->dumpValue($callable[0]), $callable[1]);
      }
    }
    else
    {
      return sprintf("    %s(\$instance);\n", $callable);
    }
  }

  protected function addService($id, $definition)
  {
    $name = sfServiceContainer::camelize($id);

    $code = <<<EOF

  protected function get{$name}Service()
  {

EOF;

    $code .=
      $this->addServiceInclude($id, $definition).
      $this->addServiceInstance($id, $definition).
      $this->addServiceMethodCalls($id, $definition).
      $this->addServiceConfigurator($id, $definition).
      $this->addServiceReturn($id, $definition)
    ;

    return $code;
  }

  protected function addServiceAlias($alias, $id)
  {
    $name = sfServiceContainer::camelize($alias);

    return <<<EOF

  protected function get{$name}Service()
  {
    return {$this->getServiceCall($id)};
  }

EOF;
  }

  protected function addServices(sfServiceContainerBuilder $builder)
  {
    $code = '';
    foreach ($builder->getServiceDefinitions() as $id => $definition)
    {
      $code .= $this->addService($id, $definition);
    }

    foreach ($builder->getAliases() as $alias => $id)
    {
      $code .= $this->addServiceAlias($alias, $id);
    }

    return $code;
  }

  protected function startClass($class, $baseClass)
  {
    return <<<EOF
class $class extends $baseClass
{

EOF;
  }

  protected function addConstructor(sfServiceContainerBuilder $builder)
  {
    if (!$builder->getParameters())
    {
      return '';
    }

    return <<<EOF

  public function __construct()
  {
    parent::__construct();
    
    \$this->addParameters(\$this->getDefaultParameters());
  }

EOF;
  }

  protected function addParametersMethods(sfServiceContainerBuilder $builder)
  {
    if (!$builder->getParameters())
    {
      return '';
    }

    $parameters = $builder->getParameters();

    $primitiveParameters = array_filter($parameters, array($this, 'isPrimitiveValue'));
    $complexParameters = array_diff($parameters, $primitiveParameters);

    return <<<EOF
    
  protected function getDefaultParameters()
  {
    return {$this->dumpValue($primitiveParameters)};
  }
    
  /**
   * @inheritdoc
   */
  public function hasParameter(\$name)
  {
    if (parent::hasParameter(\$name)) {
      return true;
    } 
    return in_array(\$name, {$this->dumpValue(array_keys($parameters))}); 
  }

  /**
   * @inheritdoc
   */
  public function getParameter(\$name)
  {
    if (parent::hasParameter(\$name) {
      return parent::getParameter(\$name);
    }

    switch (\$name) {
      {$this->dumpParameterResolvers($complexParameters)};
      
      default:
        // make parent::getParameter() throw "missing parameter" exception
        return parent::getParameter(\$name);
    }
    parent::setParameter(\$name, \$value);
    return \$value;
  }

EOF;
  }

  protected function dumpParameterResolvers($parameters)
  {
    $cases = array();

    foreach ($parameters as $key => $value)
    {
      $key = var_export($key, true);
      $value = $this->dumpValue($value);

      $cases[] = <<<PHP
      case {$key}:
         \$value = $value;\n
         break;
PHP;
    }

    return "\n" . implode("\n", $cases);
  }

  /**
   * @internal Do not use this method from outside. It's not a part of API.
   *
   * @param mixed $value
   * @return bool
   */
  public function isPrimitiveValue($value)
  {
    if (is_array($value)) {
      foreach ($value as $v) {
        if (!$this->isPrimitiveValue($v)) {
          return false;
        }
      }
    } elseif (is_object($value)) { // to cover sfServiceReference/sfServiceParameter/sfServiceParameterStringException
      return false;
    }
    // otherwise it's primitive
    return true;
  }

  protected function endClass()
  {
    return <<<EOF
}

EOF;
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
  public function dumpValue($value)
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
      return sprintf("\$this->getParameter('%s')", strtolower($value->getParameterName()));
    }
    elseif ($value instanceof sfServiceParameterStringExpression)
    {
      // concat dumpValue of each expression part
      return implode('.', array_map(array($this, 'dumpValue'), $value->getParts()));
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

    return sprintf('$this->getService(\'%s\')', $id);
  }
}
