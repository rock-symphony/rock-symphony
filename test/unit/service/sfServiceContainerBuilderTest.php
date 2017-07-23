<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(14);

// ->setServiceDefinitions() ->addServiceDefinitions() ->getServiceDefinitions() ->setServiceDefinition() ->getServiceDefinition() ->hasServiceDefinition()
$t->diag('->setServiceDefinitions() ->addServiceDefinitions() ->getServiceDefinitions() ->setServiceDefinition() ->getServiceDefinition() ->hasServiceDefinition()');
$builder = new sfServiceContainerBuilder();
$definitions = array(
  'foo' => new sfServiceDefinition('FooClass'),
  'bar' => new sfServiceDefinition('BarClass'),
);
$builder->setServiceDefinition('foo', $definitions['foo']);
$builder->setServiceDefinition('bar', $definitions['bar']);
$t->is($builder->getServiceDefinitions(), $definitions, '->setServiceDefinition() sets the service definition');
$t->ok($builder->hasServiceDefinition('foo'), '->hasServiceDefinition() returns true if a service definition exists');
$t->ok(!$builder->hasServiceDefinition('foobar'), '->hasServiceDefinition() returns false if a service definition does not exist');

$builder->setServiceDefinition('foobar', $foo = new sfServiceDefinition('FooBarClass'));
$t->is($builder->getServiceDefinition('foobar'), $foo, '->getServiceDefinition() returns a service definition if defined');
$t->ok($builder->setServiceDefinition('foobar', $foo = new sfServiceDefinition('FooBarClass')) === $foo, '->setServiceDefinition() implements a fluid interface by returning the service reference');

$builder->setServiceDefinition('foobar', $def = new sfServiceDefinition('FooBarClass'));
$t->is($builder->getServiceDefinitions(), array_merge($definitions, ['foobar' => $def]), '->setServiceDefinition() add a service definition');

try
{
  $builder->getServiceDefinition('baz');
  $t->fail('->getServiceDefinition() throws an InvalidArgumentException if the service definition does not exist');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->getServiceDefinition() throws an InvalidArgumentException if the service definition does not exist');
}

// ->register()
$t->diag('->register()');
$builder = new sfServiceContainerBuilder();
$builder->registerServiceClass('foo', 'FooClass');
$t->ok($builder->hasServiceDefinition('foo'), '->setAlias() registers a new service definition');
$t->ok($builder->getServiceDefinition('foo') instanceof sfServiceDefinition, '->register() returns the newly created sfServiceDefinition instance');

// ->setAlias()
$t->diag('->setAlias()');
$builder = new sfServiceContainerBuilder();
$builder->registerServiceClass('foo', 'stdClass');
$builder->setAlias('bar', 'foo');
$t->ok($builder->hasServiceDefinition('bar'), '->setAlias() defines a new service');
$t->ok($builder->getServiceDefinition('bar') === $builder->getServiceDefinition('foo'), '->setAlias() creates a service that is an alias to another one');

// ->getAliases()
$t->diag('->getAliases()');
$builder = new sfServiceContainerBuilder();
$builder->setAlias('bar', 'foo');
$builder->setAlias('foobar', 'foo');
$t->is($builder->getAliases(), array('bar' => 'foo', 'foobar' => 'foo'), '->getAliases() returns all service aliases');
$builder->registerServiceClass('bar', 'stdClass');
$t->is($builder->getAliases(), array('foobar' => 'foo'), '->getAliases() does not return aliased services that have been overridden');
$builder->registerServiceClass('foobar', 'stdClass');
$t->is($builder->getAliases(), array(), '->getAliases() does not return aliased services that have been overridden');
