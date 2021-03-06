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
require_once(__DIR__ . '/../../../lib/helper/PartialHelper.php');

$t = new lime_test(9);

class MyTestPartialView extends sfPartialView
{
  public function __construct(sfContext $context, string $moduleName, string $actionName, string $viewName)
  {
    // do nothing
  }

  public function render(): string
  {
    return '==RENDERED==';
  }

  public function setPartialVars(array $partialVars): void
  {
  }
}

sfContextMock::mockInstance();

$t->diag('get_partial()');
sfConfig::set('mod_module_partial_view_class', 'MyTest');

$t->is(get_partial('module/dummy'), '==RENDERED==', 'get_partial() uses the class specified in partial_view_class for the given module');
$t->is(get_partial('MODULE/dummy'), '==RENDERED==', 'get_partial() accepts a case-insensitive module name');

// slots tests
sfContextMock::mockInstance(['response' => 'sfWebResponse']);

$t->diag('get_slot()');
$t->is(get_slot('foo', 'baz'), 'baz', 'get_slot() retrieves default slot content');
slot('foo', 'bar');
$t->is(get_slot('foo', 'baz'), 'bar', 'get_slot() retrieves slot content');

$t->diag('has_slot()');
$t->ok(has_slot('foo'), 'has_slot() checks if a slot exists');
$t->ok(!has_slot('doo'), 'has_slot() checks if a slot does not exist');

$t->diag('include_slot()');
ob_start();
include_slot('foo');
$t->is(ob_get_clean(), 'bar', 'include_slot() prints out the content of an existing slot');

ob_start();
include_slot('doo');
$t->is(ob_get_clean(), '', 'include_slot() does not print out the content of an unexisting slot');

ob_start();
include_slot('doo', 'zoo');
$t->is(ob_get_clean(), 'zoo', 'include_slot() prints out the default content specified for an unexisting slot');
