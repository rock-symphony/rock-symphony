<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$plan = 64;
$t = new lime_test($plan);

try
{
  new sfAPCuCache();
}
catch (sfInitializationException $e)
{
  $t->skip($e->getMessage(), $plan);
  return;
}

if (!ini_get('apc.enable_cli'))
{
  $t->skip('APC must be enable on CLI to run these tests', $plan);
  return;
}

require_once(__DIR__.'/sfCacheDriverTests.class.php');

// setup
sfConfig::set('sf_logging_enabled', false);

$test = new class extends sfCacheDriverTests
{
  public function createCache(array $options = []): sfCache
  {
    return new sfAPCuCache($options);
  }
};

$test->launch($t);
