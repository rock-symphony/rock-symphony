<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');
require_once(__DIR__.'/sfCacheDriverTests.class.php');

$plan = 129;
$t = new lime_test($plan);

if (!extension_loaded('SQLite') && !extension_loaded('pdo_SQLite'))
{
  $t->skip('SQLite extension not loaded, skipping tests', $plan);
  return;
}

try
{
  new sfSQLiteCache(array('database' => ':memory:'));
}
catch (sfInitializationException $e)
{
  $t->skip($e->getMessage(), $plan);
  return;
}

try
{
  $cache = new sfSQLiteCache();
  $t->fail('->__construct() throws an sfInitializationException exception if you don\'t pass a "database" parameter');
}
catch (sfInitializationException $e)
{
  $t->pass('->__construct() throws an sfInitializationException exception if you don\'t pass a "database" parameter');
}

$test = new class extends sfCacheDriverTests
{
  public function createCache(array $options = []): sfCache
  {
    return new sfSQLiteCache(array_merge(['database' => ':memory:'], $options));
  }
};
$test->launch($t);


// database on disk
$database = tempnam('/tmp/cachedir', 'tmp');
unlink($database);

$test = new class extends sfCacheDriverTests
{
  public function createCache(array $options = []): sfCache
  {
    global $database;
    return new sfSQLiteCache(array_merge(['database' => $database], $options));
  }
};
$test->launch($t);

unlink($database);
