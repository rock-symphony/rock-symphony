<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__ . '/../../bootstrap/unit.php');
require_once(__DIR__ . '/../../unit/sfContextMock.class.php');

$t = new lime_test(21);

define('ESC_TEST_ESCAPING', 'esc_test_escaping');
function esc_test_escaping($value)
{
  return "-ESCAPED-$value-ESCAPED-";
}

define('ESC_TEST_RAW', 'esc_test_raw');
function esc_test_raw($value)
{
  return $value;
}

class myRequest
{
  public function getParameterHolder()
  {
    return new sfParameterHolder();
  }
}

$context = sfContextMock::mockInstance();
$dispatcher = $context->getEventDispatcher();

// ->__construct()
$t->diag('->__construct()');
$p = new sfViewParameterHolder($dispatcher);
$t->is($p->getAll(), [], '->__construct() initializes the parameters as an empty array');

$p = new sfViewParameterHolder($dispatcher, ['foo' => 'bar']);
$t->is($p->get('foo'), 'bar', '->initialize() takes an array of default parameters as its second argument');

$p = new sfViewParameterHolder($dispatcher, [], ['escaping_strategy' => 'on', 'escaping_method' => 'ESC_TEST_RAW']);
$t->is($p->getEscaping(), true, '->initialize() takes an array of options as its third argument');
$t->is($p->getEscapingMethod(), ESC_TEST_RAW, '->initialize() takes an array of options as its third argument');

// ->isEscaped()
$t->diag('->isEscaped()');
$p->setEscaping(true);
$t->is($p->isEscaped(), true, '->isEscaped() returns true if data will be escaped');
$p->setEscaping(false);
$t->is($p->isEscaped(), false, '->isEscaped() returns false if data won\'t be escaped');

// ->getEscaping() ->setEscaping()
$t->diag('->getEscaping() ->setEscaping()');
$p = new sfViewParameterHolder($dispatcher);
$p->setEscaping(true);
$t->is($p->getEscaping(), true, '->setEscaping() changes the escaping strategy');

// ->getEscapingMethod() ->setEscapingMethod()
$t->diag('->getEscapingMethod() ->setEscapingMethod()');
$p->setEscapingMethod('ESC_TEST_RAW');
$t->is($p->getEscapingMethod(), ESC_TEST_RAW, '->setEscapingMethod() changes the escaping method');

$p->setEscapingMethod('');
$t->is($p->getEscapingMethod(), '', '->getEscapingMethod() returns an empty value if the method is empty');

try {
  $p->setEscapingMethod('nonexistant');
  $t->fail('->setEscapingMethod() throws an InvalidArgumentException if the escaping method does not exist');
} catch (InvalidArgumentException $e) {
  $t->pass('->setEscapingMethod() throws an InvalidArgumentException if the escaping method does not exist');
}

// ->toArray()
$t->diag('->toArray()');
$p = new sfViewParameterHolder($dispatcher, ['foo' => 'bar']);
$a = $p->toArray();
$t->is($a['foo'], 'bar', '->toArray() returns an array representation of the parameter holder');

// escaping strategies
try {
  new sfViewParameterHolder(new sfEventDispatcher(), ['foo' => 'bar'], ['escaping_strategy' => 'null']);
  $t->fail('new sfViewParameterHolder() throws an InvalidArgumentException if the escaping strategy does not exist');
} catch (InvalidArgumentException $e) {
  $t->pass('new sfViewParameterHolder() throws an InvalidArgumentException if the escaping strategy does not exist');
}

$p = new sfViewParameterHolder(
  new sfEventDispatcher(),
  ['foo' => 'bar'],
  ['escaping_method' => 'ESC_TEST_ESCAPING']
);

$t->diag('Escaping strategy to on');
$p->setEscaping(true);
$values = $p->toArray();
$t->is(count($values), 2, '->toArray() knows about the "on" strategy');
$t->is(count($values['sf_data']), 1, '->toArray() knows about the "on" strategy');
$t->is($values['foo'], '-ESCAPED-bar-ESCAPED-', '->toArray() knows about the "on" strategy');
$t->is($values['sf_data']['foo'], '-ESCAPED-bar-ESCAPED-', '->toArray() knows about the "on" strategy');

$t->diag('Escaping strategy to off');
$p->setEscaping(false);
$values = $p->toArray();
$t->is(count($values), 2, '->toArray() knows about the "off" strategy');
$t->is(count($values['sf_data']), 1, '->toArray() knows about the "on" strategy');
$t->is($values['foo'], 'bar', '->toArray() knows about the "off" strategy');
$t->is($values['sf_data']['foo'], 'bar', '->toArray() knows about the "off" strategy');

// ->serialize() / ->unserialize()
$t->diag('->serialize()');
$p = new sfViewParameterHolder($dispatcher, ['foo' => 'bar']);
try {
  serialize($p);
  $t->fail('sfViewParameterHolder should throw when tried to be serialized.');
} catch (LogicException $exception) {

}

$t->ok(true, 'sfViewParameterHolder should throw when tried to be serialized.');
