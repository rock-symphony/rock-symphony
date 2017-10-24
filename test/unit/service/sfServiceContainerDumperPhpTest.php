<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(4);

// ->dump()
$t->diag('->dump()');
$builder = new sfServiceContainerBuilder();
$dumper = new sfServiceContainerDumperPhp();

$t->is($dumper->dump($builder), file_get_contents(__DIR__.'/fixtures/php/services1.php'), '->dump() dumps an empty container as an empty PHP class');
$t->is($dumper->dump($builder, array('class' => 'Container', 'base_class' => 'AbstractContainer')), file_get_contents(__DIR__.'/fixtures/php/services1-1.php'), '->dump() takes a class and a base_class options');

// ->addService()
$t->diag('->addService()');
$builder = include __DIR__.'/fixtures/containers/container9.php';
$dumper = new sfServiceContainerDumperPhp();
$t->is($dumper->dump($builder), str_replace('%path%', __DIR__.'/fixtures/includes', str_replace('%path%', __DIR__.'/fixtures/includes', file_get_contents(__DIR__.'/fixtures/php/services9.php'))), '->dump() dumps services');


$dumper = new sfServiceContainerDumperPhp();
$builder->registerServiceClass('foo', 'FooClass')->addArgument(new stdClass());
try
{
  $dumper->dump($builder);
  $t->fail('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
}
catch (RuntimeException $e)
{
  $t->pass('->dump() throws a RuntimeException if the container to be dumped has reference to objects or resources');
}

