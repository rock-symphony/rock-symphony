<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__ . '/../../bootstrap/unit.php');
require_once(__DIR__ . '/../../unit/sfContextMock.class.php');

$t = new lime_test(16);

class myFilter extends sfFilter
{
  public function isFirstCall(): bool
  {
    return parent::isFirstCall();
  }

  function execute(sfFilterChain $chain): void
  {

  }
}

$context = sfContextMock::mockInstance();
$filter = new myFilter($context, ['foo' => 'bar']);

// ->initialize()
$t->diag('->__construct()');
$t->is($filter->getContext(), $context, '->__construct() takes a sfContext object as its first argument');
$t->is($filter->getParameter('foo'), 'bar', '->__construct() takes an array of parameters as its second argument');

// ->isFirstCall()
$t->diag('->isFirstCall()');
$t->is($filter->isFirstCall('beforeExecution'), true, '->isFirstCall() returns true if this is the first call with this argument');
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');

$filter = new myFilter($context);
$t->is($filter->isFirstCall('beforeExecution'), false, '->isFirstCall() returns false if this is not the first call with this argument');

// parameter holder proxy
require_once($_test_dir.'/unit/sfParameterHolderTest.class.php');
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($filter, 'parameter');
