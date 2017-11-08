<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RockSymphony\ServiceContainer\Exceptions\BindingNotFoundException;

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(8);

// __construct()
$t->diag('__construct()');
$sc = new sfServiceContainer();
$t->is(spl_object_hash($sc->get('service_container')), spl_object_hash($sc), '__construct() automatically registers itself as a service');

// ->set() ->has() ->get()
$t->diag('->set() ->has() ->get()');
$sc = new sfServiceContainer();
$sc->set('foo', $obj = new stdClass());
$t->is(spl_object_hash($sc->get('foo')), spl_object_hash($obj), '->set() registers a service under a key name');

$sc->foo1 = $obj1 = new stdClass();
$t->ok($sc->has('foo'), '->has() returns true if the service is defined');
$t->ok(!$sc->has('bar'), '->has() returns false if the service is not defined');

$bar = new stdClass();

$sc = new sfServiceContainer();
$sc->set('bar', $bar);

$t->is(spl_object_hash($sc->get('bar')), spl_object_hash($bar ), '->get() returns the same service object');
$t->ok($sc->has('bar'), '->has() returns true if the service has been defined');

$sc->set('bar', $another_bar = new stdClass());
$t->is(spl_object_hash($sc->get('bar')), spl_object_hash($another_bar), '->get() prefers to return a new service object if overrided');

try
{
  $sc->get('baba');
  $t->fail('->get() thrown an InvalidArgumentException if the service does not exist');
}
catch (BindingNotFoundException $e)
{
  $t->pass('->get() thrown a BindingResolutionException if the service does not exist');
}
