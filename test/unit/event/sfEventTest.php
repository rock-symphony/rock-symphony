<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(11);

$subject = new stdClass();
$parameters = array('foo' => 'bar');
$event = new sfEvent($subject, 'name', $parameters);

// ->getSubject()
$t->diag('->getSubject()');
$t->is($event->getSubject(), $subject, '->getSubject() returns the event subject');

// ->getName()
$t->diag('->getName()');
$t->is($event->getName(), 'name', '->getName() returns the event name');

// ->getParameters()
$t->diag('->getParameters()');
$t->is($event->getParameters(), $parameters, '->getParameters() returns the event parameters');

// ->getReturnValue() ->setReturnValue()
$t->diag('->getReturnValue() ->setReturnValue()');
$event->setReturnValue('foo');
$t->is($event->getReturnValue(), 'foo', '->getReturnValue() returns the return value of the event');

// ->setProcessed() ->isProcessed()
$t->diag('->setProcessed() ->isProcessed()');
$event->setProcessed(true);
$t->is($event->isProcessed(), true, '->isProcessed() returns true if the event has been processed');
$event->setProcessed(false);
$t->is($event->isProcessed(), false, '->setProcessed() changes the processed status');

// ArrayAccess interface
$t->diag('->getParameter()');
$t->is($event->getParameter('foo'), 'bar', '->getParameter() returns parameter value');
$t->is($event->getParameter('non-foo'), null, '->getParameter() returns null if parameter is not set');
$t->is($event->getParameter('non-foo', 'bar'), 'bar', '->getParameter() returns the fallback value if parameter is not set, and fallback is given');

$t->diag('->hasParameter()');
$t->ok($event->hasParameter('foo'), '->hasParameter() returns true if the parameter is set for the event');
$t->ok(!$event->hasParameter('bar'), '->hasParameter() returns false if the parameter is not set for the event');
