<?php
/**
 * @return \sfServiceContainerInterface
 */
return function() {
  $container = new MyServiceContainer();

  $container->alias('service_container', 'Psr\\Container\\ContainerInterface');
  $container->alias('service_container', 'sfServiceContainerInterface');
  $container->alias('service_container', 'sfServiceContainer');

  return $container;
};
