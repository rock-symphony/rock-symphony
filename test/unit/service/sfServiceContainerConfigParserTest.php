<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(18);

$parser = new sfServiceContainerConfigParser();

// ->parse()
try
{
  $parser->parse(sfYaml::load(__DIR__.'/fixtures/yaml/nonvalid1.yml'));
  $t->fail('->parse() throws an InvalidArgumentException if the loaded definition is not an array');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->parse() throws an InvalidArgumentException if the loaded definition is not an array');
}

// *** DISABLED As method argument is enforced to be `array`.
// *** You cannot catch argument type mismatch exception in pre-PHP7 envs.
// *** Skipping for now.
//try
//{
//  $parser->parse(sfYaml::load(__DIR__.'/fixtures/yaml/nonvalid2.yml'));
//  $t->fail('->parse() throws an InvalidArgumentException if the loaded definition is not a valid array');
//}
//catch (InvalidArgumentException $e)
//{
//  $t->pass('->parse() throws an InvalidArgumentException if the loaded definition is not a valid array');
//}

try
{
  $parser->parse(sfYaml::load(__DIR__.'/fixtures/yaml/nonvalid3.yml'));
  $t->fail('->parse() throws an InvalidArgumentException if the loaded definition is not an array');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->parse() throws an InvalidArgumentException if the loaded definition has "parameters" section');
}


// ->parse # parameters
$t->diag('->parse # parameters');

$builder = $parser->parse(array());
$t->is($builder->getServiceDefinitions(), array(), '->parse defines no services');

$t->diag('->parse # services');
$builder = $parser->parse(sfYaml::load(__DIR__.'/fixtures/yaml/services2.yml'));
$t->ok($builder->hasServiceDefinition('foo'), '->parse parses service elements');
$t->is(get_class($builder->getServiceDefinition('foo')), 'sfServiceDefinition', '->parse converts service element to sfServiceDefinition instances');
$t->is($builder->getServiceDefinition('foo')->getClass(), 'FooClass', '->parse parses the class attribute');
$t->ok($builder->getServiceDefinition('shared')->isShared(), '->parse parses the shared attribute');
$t->ok(!$builder->getServiceDefinition('non_shared')->isShared(), '->parse parses the shared attribute');
$t->is($builder->getServiceDefinition('constructor')->getConstructor(), 'getInstance', '->parse parses the constructor attribute');
$t->is($builder->getServiceDefinition('file')->getFile(), '%path%/foo.php', '->parse parses the file tag');
$t->is($builder->getServiceDefinition('arguments')->getArguments(), array('foo', new sfServiceReference('foo'), array(true, false)), '->parse parses the argument tags');
$t->is($builder->getServiceDefinition('configurator1')->getConfigurator(), 'sc_configure', '->parse parses the configurator tag');
$t->is($builder->getServiceDefinition('configurator2')->getConfigurator(), array(new sfServiceReference('baz'), 'configure'), '->parse parses the configurator tag');
$t->is($builder->getServiceDefinition('configurator3')->getConfigurator(), array('BazClass', 'configureStatic'), '->parse parses the configurator tag');
$t->is($builder->getServiceDefinition('method_call1')->getMethodCalls(), array(array('setBar', array())), '->parse parses the method_call tag');
$t->is($builder->getServiceDefinition('method_call2')->getMethodCalls(), array(array('setBar', array('foo', new sfServiceReference('foo'), array(true, false)))), '->parse parses the method_call tag');
$t->ok($builder->hasAlias('alias_for_foo'), '->parse parses aliases');
$t->is($builder->getAlias('alias_for_foo'), 'foo', '->parse parses aliases');
