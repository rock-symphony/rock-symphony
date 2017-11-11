<?php
/**
 * @return \sfServiceContainerInterface
 */
return function() {
  $container = new \sfServiceContainer();

  // BazClass
  $container->bindSingletonResolver('BazClass', function(\sfServiceContainerInterface $container) {
    $instance = $container->call(array('BazClass', 'getInstance'), array());
    return $instance;
  });

  // BazDependentClass
  $container->bindSingletonResolver('BazDependentClass', function(\sfServiceContainerInterface $container) {
    $instance = $container->construct('BazDependentClass', array());
    return $instance;
  });

  return $container;
};
