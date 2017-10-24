<?php

require_once __DIR__.'/../includes/classes.php';

$container = new sfServiceContainerBuilder();
$container->
  registerServiceClass('foo', 'FooClass')->
  setConstructor('getInstance')->
  setArguments(array('foo', new sfServiceReference('foo.baz'), array('%foo%' => 'foo is %foo%'), true, new sfServiceReference('service_container')))->
  setFile(realpath(__DIR__.'/../includes/foo.php'))->
  setShared(false)->
  addMethodCall('setBar', array('bar'))->
  addMethodCall('initialize')->
  setConfigurator('sc_configure')
;
$container->
  registerServiceClass('bar', 'FooClass')->
  setArguments(array('foo', new sfServiceReference('foo.baz'), new sfServiceParameter('foo_bar')))->
  setShared(true)->
  setConfigurator(array(new sfServiceReference('foo.baz'), 'configure'))
;
$container->
  registerServiceClass('foo.baz', '%baz_class%')->
  setConstructor('getInstance')->
  setConfigurator(array('%baz_class%', 'configureStatic1'))
;
$container->registerServiceClass('foo_bar', 'FooClass');
$container->setAlias('alias_for_foo', 'foo');

return $container;
