<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../bootstrap/unit.php';

$rootDir = realpath(__DIR__ . '/../../functional/fixtures');
$pluginRoot = realpath($rootDir . '/plugins/sfConfigPlugin');

require_once $pluginRoot . '/config/sfConfigPluginConfiguration.class.php';

$t = new lime_test(2);

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup(): void
  {
    $this->enablePlugins(['sfConfigPlugin']);
  }
}

// ->guessRootDir() ->guessName()
$t->diag('->guessRootDir() ->guessName()');

$configuration = new sfProjectConfiguration($rootDir);
$pluginConfig = new sfConfigPluginConfiguration($configuration);

$t->is($pluginConfig->getRootDir(), $pluginRoot, '->guessRootDir() guesses plugin root directory');
$t->is($pluginConfig->getName(), 'sfConfigPlugin', '->guessName() guesses plugin name');
