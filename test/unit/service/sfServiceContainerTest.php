<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(8);

// __construct()
$t->diag('__construct()');
$sc = new sfServiceContainer();
$t->is(spl_object_hash($sc->get('service_container')), spl_object_hash($sc), '__construct() automatically registers itself as a service');

// ->setService() ->hasService() ->getService()
$t->diag('->setService() ->hasService() ->getService()');
$sc = new sfServiceContainer();
$sc->set('foo', $obj = new stdClass());
$t->is(spl_object_hash($sc->get('foo')), spl_object_hash($obj), '->setService() registers a service under a key name');

$sc->foo1 = $obj1 = new stdClass();
$t->ok($sc->has('foo'), '->hasService() returns true if the service is defined');
$t->ok(!$sc->has('bar'), '->hasService() returns false if the service is not defined');

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
$t->is(spl_object_hash($sc->get('bar')), spl_object_hash($sc->__bar), '->getService() returns the same service object');
$t->ok($sc->has('bar'), '->hasService() returns true if the service has been defined');

$sc->set('bar', $bar = new stdClass());
$t->is(spl_object_hash($sc->get('bar')), spl_object_hash($bar), '->getService() prefers to return a new service object if overrided');

try
{
  $sc->get('baba');
  $t->fail('->getService() thrown an InvalidArgumentException if the service does not exist');
}
catch (InvalidArgumentException $e)
{
  $t->pass('->getService() thrown an InvalidArgumentException if the service does not exist');
}
