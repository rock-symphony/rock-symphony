<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please component the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__ . '/../../bootstrap/unit.php');
require_once(__DIR__ . '/../../unit/sfContextMock.class.php');
require_once(__DIR__ . '/../../unit/sfNoRouting.class.php');

$t = new lime_test(4);

class myComponent extends sfComponent
{
  function execute(sfRequest $request) {}
}

$context = sfContextMock::mockInstance([
  'routing' => 'sfNoRouting',
  'request' => 'sfWebRequest',
  'response' => 'sfWebResponse',
]);

// ->__construct()
$t->diag('->__construct()');
$component = new myComponent($context, 'module', 'action');

// ->getContext()
$t->diag('->getContext()');
$t->is($component->getContext(), $context, '->getContext() returns the current context');

// ->getRequest()
$t->diag('->getRequest()');
$t->is($component->getRequest(), $context->getRequest(), '->getRequest() returns the current request');

// ->getResponse()
$t->diag('->getResponse()');
$t->is($component->getResponse(), $context->getResponse(), '->getResponse() returns the current response');

// __set()
$t->diag('__set()');
$component->foo = array();
$component->foo[] = 'bar';
$t->is($component->foo, array('bar'), '__set() populates component variables');
