<?php
/**
 * @return \sfServiceContainerInterface
 */
return function() {
  $container = new sfServiceContainer();

  $container->alias('service_container', 'Psr\\Container\\ContainerInterface');
  $container->alias('service_container', 'sfServiceContainerInterface');

  return $container;
};
