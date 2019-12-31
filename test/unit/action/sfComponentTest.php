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

$t = new lime_test(6);

class myComponent extends sfComponent
{
  function execute(sfRequest $request) {}
}

$context = sfContextMock::mockInstance([
  'routing' => 'sfNoRouting',
  'request' => 'sfWebRequest',
  'response' => 'sfWebResponse',
]);

// ->initialize()
$t->diag('->initialize()');
$component = new myComponent($context, 'module', 'action');
$t->is($component->getContext(), $context, '->initialize() takes a sfContext object as its first argument');
$component->initialize($context, 'module', 'action');
$t->is($component->getContext(), $context, '->initialize() takes a sfContext object as its first argument');

// ->getContext()
$t->diag('->getContext()');
$component->initialize($context, 'module', 'action');
$t->is($component->getContext(), $context, '->getContext() returns the current context');

// ->getRequest()
$t->diag('->getRequest()');
$component->initialize($context, 'module', 'action');
$t->is($component->getRequest(), $context->getRequest(), '->getRequest() returns the current request');

// ->getResponse()
$t->diag('->getResponse()');
$component->initialize($context, 'module', 'action');
$t->is($component->getResponse(), $context->getResponse(), '->getResponse() returns the current response');

// __set()
$t->diag('__set()');
$component->foo = array();
$component->foo[] = 'bar';
$t->is($component->foo, array('bar'), '__set() populates component variables');
