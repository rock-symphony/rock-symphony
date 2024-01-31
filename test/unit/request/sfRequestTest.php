<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../../bootstrap/unit.php';

class myRequest extends sfRequest
{
  public function getEventDispatcher(): sfEventDispatcher
  {
    return $this->dispatcher;
  }
}

class fakeRequest
{
}

$t = new lime_test(33);

$dispatcher = new sfEventDispatcher();

// ->__construct()
$t->diag('->__construct()');
$request = new myRequest($dispatcher, ['foo' => 'bar']);
$t->is($dispatcher, $request->getEventDispatcher(), '->__construct() takes a sfEventDispatcher object as its first argument');
$t->is($request->getParameter('foo'), 'bar', '->__construct() takes an array of parameters as its second argument');

$options = $request->getOptions();
$t->is($options['logging'], false, '->getOptions() returns options for request instance');

// ->getMethod() ->setMethod()
$t->diag('->getMethod() ->setMethod()');
$request->setMethod(sfRequest::GET);
$t->is($request->getMethod(), sfRequest::GET, '->getMethod() returns the current request method');

try
{
  $request->setMethod('foo');
  $t->fail('->setMethod() throws a sfException if the method is not valid');
}
catch (sfException $e)
{
  $t->pass('->setMethod() throws a sfException if the method is not valid');
}

// ->extractParameters()
$t->diag('->extractParameters()');
$request = new myRequest($dispatcher, ['foo' => 'foo', 'bar' => 'bar']);
$t->is($request->extractParameters([]), [], '->extractParameters() returns parameters');
$t->is($request->extractParameters(['foo']), ['foo' => 'foo'], '->extractParameters() returns parameters for keys in its first parameter');
$t->is($request->extractParameters(['bar']), ['bar' => 'bar'], '->extractParameters() returns parameters for keys in its first parameter');

// ->getOption()
$t->diag('->getOption()');
$request = new myRequest($dispatcher, [], [], ['val_1' => 'value', 'val_2' => false]);
$t->is($request->getOption('val_1'), 'value', '->getOption() returns the option value if exists');
$t->is($request->getOption('val_2'), false, '->getOption() returns the option value if exists');
$t->is($request->getOption('none'), null, '->getOption() returns the option value if not exists');

// ->getOption()
$t->diag('->__clone()');
$request = new myRequest($dispatcher);
$requestClone = clone $request;
$t->ok($request->getParameterHolder() !== $requestClone->getParameterHolder(), '->__clone() clone parameterHolder');
$t->ok($request->getAttributeHolder() !== $requestClone->getAttributeHolder(), '->__clone() clone attributeHolder');

$request = new myRequest($dispatcher);

// parameter holder proxy
require_once __DIR__ . '/../../unit/sfParameterHolderTest.class.php';
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($request, 'parameter');

// attribute holder proxy
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($request, 'attribute');
