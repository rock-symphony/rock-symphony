<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../../bootstrap/unit.php';

$t = new lime_test(24);

$_SERVER['session_id'] = 'test';

$dispatcher = new sfEventDispatcher();
$sessionPath = sys_get_temp_dir().'/sessions_'.rand(11111, 99999);
$storage = new sfSessionTestStorage(['session_path' => $sessionPath]);

$user = new sfUser($dispatcher, $storage);

// ->initialize()
$t->diag('->__construct()');
$t->is($user->getCulture(), 'en', '->__construct() sets the culture to "en" by default');

$user = new sfUser($dispatcher, $storage, ['default_culture' => 'de']);
$t->is($user->getCulture(), 'de', '->initialize() sets the culture to the value of default_culture if available');

$user = user_flush($dispatcher, $user, $storage);
$t->is($user->getCulture(), 'de', '->initialize() reads the culture from the session data if available');

$userBis = new sfUser($dispatcher, $storage);
$t->is($userBis->getCulture(), 'de', '->initialize() serializes the culture to the session data');

// ->setCulture() ->getCulture()
$t->diag('->setCulture() ->getCulture()');
$user->setCulture('fr');
$t->is($user->getCulture(), 'fr', '->setCulture() changes the current user culture');

// ->setFlash() ->getFlash() ->hasFlash()
$t->diag('->setFlash() ->getFlash() ->hasFlash()');
$user = user_flush($dispatcher, $user, $storage, ['use_flash' => true]);
$user->setFlash('foo', 'bar');
$t->is($user->getFlash('foo'), 'bar', '->setFlash() sets a flash variable');
$t->is($user->hasFlash('foo'), true, '->hasFlash() returns true if the flash variable exists');
$user = user_flush($dispatcher, $user, $storage, ['use_flash' => true]);

$userBis = new sfUser($dispatcher, $storage, array('use_flash' => true));
$t->is($userBis->getFlash('foo'), 'bar', '->getFlash() returns a flash previously set');
$t->is($userBis->hasFlash('foo'), true, '->hasFlash() returns true if the flash variable exists');
$user = user_flush($dispatcher, $user, $storage, array('use_flash' => true));

$userBis = new sfUser($dispatcher, $storage, array('use_flash' => true));
$t->is($userBis->getFlash('foo'), null, 'Flashes are automatically removed after the next request');
$t->is($userBis->hasFlash('foo'), false, '->hasFlash() returns true if the flash variable exists');

// array access for user attributes
$user->setAttribute('foo', 'foo');

$user = new sfUser($dispatcher, $storage);

// attribute holder proxy
require_once __DIR__ . '/../../unit/sfParameterHolderTest.class.php';
$pht = new sfParameterHolderProxyTest($t);
$pht->launchTests($user, 'attribute');

$storage->clear();

function user_flush(sfEventDispatcher $dispatcher, sfUser $user, sfStorage $storage, array $options = []): sfUser
{
  $user->shutdown();
  return new sfUser($dispatcher, $storage, $options);
}

sfToolkit::clearDirectory($sessionPath);
