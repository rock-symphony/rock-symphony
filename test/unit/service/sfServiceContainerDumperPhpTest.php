<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(11);

// ->dump()
$t->diag('->dump()');
$builder = new sfServiceContainerBuilder();
$dumper = new sfServiceContainerDumperPhp();

$t->is("<?php\n" . $dumper->dump($builder), file_get_contents(__DIR__.'/fixtures/php/services1.php'), '->dump() dumps an empty container as an empty closure function');
$t->is("<?php\n" . $dumper->dump($builder, ['class' => 'CustomContainer']), file_get_contents(__DIR__ . '/fixtures/php/services1-1.php'), '->dump() takes a class option');

// ->addService()
$t->diag('->addService()');
$builder = include __DIR__.'/fixtures/containers/container9.php';
$dumper = new sfServiceContainerDumperPhp();
$t->is("<?php\n" . $dumper->dump($builder), str_replace('%path%', __DIR__.'/fixtures/includes', file_get_contents(__DIR__.'/fixtures/php/services9.php')), '->dump() dumps services');


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

// ->addService()
$t->diag('->addService() recursive resolution');
$builder = include __DIR__.'/fixtures/containers/container10.php';
$dumper = new sfServiceContainerDumperPhp();
$t->is("<?php\n" . $dumper->dump($builder), file_get_contents(__DIR__.'/fixtures/php/services10.php'), '->dump() handles recursive services dependencies');

/** @var \sfServiceContainerInterface $container */
$container = call_user_func(require __DIR__.'/fixtures/php/services10.php');
$t->ok($container->has('BazClass'), 'Generated container has "BazClass" inside');
$t->ok($container->has('BazDependentClass'), 'Generated container has "BazDependentClass" inside');

/** @var \BazClass $baz */
$baz = $container->get('BazClass');
/** @var \BazDependentClass $baz_dependent */
$baz_dependent = $container->get('BazDependentClass');

$t->ok($baz instanceof BazClass, '$baz is instance of BazClass');
$t->ok($baz_dependent instanceof BazDependentClass, '$baz_dependent is instance of BazDependentClass');
$t->ok($baz === $baz_dependent->baz, '$baz instance is reused');


// ->addService()
$t->diag('->addService() recursive dependency');
$builder = include __DIR__.'/fixtures/containers/container11.php';
$dumper = new sfServiceContainerDumperPhp();
$t->is("<?php\n" . $dumper->dump($builder), file_get_contents(__DIR__.'/fixtures/php/services11.php'), '->dump() handles recursive services dependencies');
