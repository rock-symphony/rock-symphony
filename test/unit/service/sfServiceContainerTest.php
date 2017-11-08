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

class ProjectServiceContainer extends sfServiceContainer
{
  public $__bar, $__foo_bar, $__foo_baz;

  public function __construct()
  {
    parent::__construct();

    $this->__bar = new stdClass();
    $this->__foo_bar = new stdClass();
    $this->__foo_baz = new stdClass();

    $this->set('bar', $this->__bar);
    $this->set('foo_bar', $this->__foo_bar);
    $this->set('foo_baz', $this->__foo_baz);
  }
}

$sc = new ProjectServiceContainer();
$t->is(spl_object_hash($sc->get('bar')), spl_object_hash($sc->__bar), '->get() returns the same service object');
$t->ok($sc->has('bar'), '->has() returns true if the service has been defined');

$sc->set('bar', $bar = new stdClass());
$t->is(spl_object_hash($sc->get('bar')), spl_object_hash($bar), '->get() prefers to return a new service object if overrided');

try
{
  $sc->get('baba');
  $t->fail('->get() thrown an InvalidArgumentException if the service does not exist');
}
catch (BindingNotFoundException $e)
{
  $t->pass('->get() thrown a BindingResolutionException if the service does not exist');
}
