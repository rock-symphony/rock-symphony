<?php
/**
 * @return \sfServiceContainerInterface
 */
return function() {
  $container = new \sfServiceContainer();

  // baz
  $container->bindSingletonResolver('baz', function(\sfServiceContainerInterface $container) {
    $instance = $container->call(array('BazClass', 'getInstance'), array());
    return $instance;
  });

  // baz_dependent
  $container->bindSingletonResolver('baz_dependent', function(\sfServiceContainerInterface $container) {
    $instance = $container->construct('BazDependentClass', array('baz' => $this->get('baz')));
    return $instance;
  });

  return $container;
};
