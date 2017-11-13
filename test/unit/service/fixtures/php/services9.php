<?php
/**
 * @return \sfServiceContainerInterface
 */
return function() {
  $container = new sfServiceContainer();

  // foo
  $container->bindResolver('foo', function(\sfServiceContainerInterface $container) {
    require_once '%path%/foo.php';

    $instance = $container->call(array('FooClass', 'getInstance'), array(0 => 'foo', 1 => $container->get('foo.baz'), 2 => array('%foo%' => 'foo is %foo%'), 3 => true, 4 => $container));
    $container->call(array($instance, 'setBar'), array(0 => 'bar'));
    $container->call(array($instance, 'initialize'), array());
    sc_configure($instance);
    return $instance;
  });

  // bar
  $container->bindSingletonResolver('bar', function(\sfServiceContainerInterface $container) {
    $instance = $container->construct('FooClass', array(0 => 'foo', 1 => $container->get('foo.baz'), 2 => sfConfig::get('foo_bar')));
    $container->get('@foo.baz')->configure($instance);
    return $instance;
  });

  // foo.baz
  $container->bindSingletonResolver('foo.baz', function(\sfServiceContainerInterface $container) {
    $instance = $container->call(array('%baz_class%', 'getInstance'), array());
    $container->call(array(0 => '%baz_class%', 1 => 'configureStatic1'), array($instance));
    return $instance;
  });

  // foo_bar
  $container->bindSingletonResolver('foo_bar', function(\sfServiceContainerInterface $container) {
    $instance = $container->construct('FooClass', array());
    return $instance;
  });

  // alias_for_foo => foo
  $container->alias('foo', 'alias_for_foo');

  return $container;
};
