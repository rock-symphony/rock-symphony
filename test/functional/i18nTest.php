<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'i18n';
if (!include(__DIR__.'/../bootstrap/functional.php'))
{
  return;
}

class myTestBrowser extends sfTestBrowser
{
  public function checkResponseForCulture(string $culture = 'fr'): self
  {
    return $this->with('response', function (sfTesterResponse $response) {
      // messages in the global directories
      $response->checkElement('#action', '/une phrase en français/i');
      $response->checkElement('#template', '/une phrase en français/i');

      // messages in the module directories
      $response->checkElement('#action_local', '/une phrase locale en français/i');
      $response->checkElement('#template_local', '/une phrase locale en français/i');

      // messages in another global catalogue
      $response->checkElement('#action_other', '/une autre phrase en français/i');
      $response->checkElement('#template_other', '/une autre phrase en français/i');

      // messages in another module catalogue
      $response->checkElement('#action_other_local', '/une autre phrase locale en français/i');
      $response->checkElement('#template_other_local', '/une autre phrase locale en français/i');
    });
  }
}

$b = new myTestBrowser();

// default culture (en)
$b->get('/')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'i18n');
    $request->isParameter('action', 'index');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('#action', '/an english sentence/i');
    $response->checkElement('#template', '/an english sentence/i');
  })
  ->with('user', function (sfTesterUser $user) {
    $user->isCulture('en');
  })
;

$b->get('/fr/i18n/index')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'i18n');
    $request->isParameter('action', 'index');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
  })
  ->with('user', function (sfTesterUser $user) {
    $user->isCulture('fr');
  })
  ->checkResponseForCulture('fr')
;

// change user culture in the action
$b->get('/en/i18n/indexForFr')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'i18n');
    $request->isParameter('action', 'indexForFr');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
  })
  ->with('user', function (sfTesterUser $user) {
    $user->isCulture('fr');
  })
  ->checkResponseForCulture('fr')
;

// messages for a module plugin
$b->get('/fr/sfI18NPlugin/index')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'sfI18NPlugin');
    $request->isParameter('action', 'index');
  })
  ->with('user', function (sfTesterUser $user) {
    $user->isCulture('fr');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('#action', '/une phrase en français - from plugin/i');
    $response->checkElement('#template', '/une phrase en français - from plugin/i');
    $response->checkElement('#action_local', '/une phrase locale en français - from plugin/i');
    $response->checkElement('#template_local', '/une phrase locale en français - from plugin/i');
    $response->checkElement('#action_other', '/une autre phrase en français - from plugin but translation overridden in the module/i');
    $response->checkElement('#template_other', '/une autre phrase en français - from plugin but translation overridden in the module/i');
    $response->checkElement('#action_yetAnother', '/encore une autre phrase en français - from plugin but translation overridden in the application/i');
    $response->checkElement('#template_yetAnother', '/encore une autre phrase en français - from plugin but translation overridden in the application/i');
    $response->checkElement('#action_testForPluginI18N', '/une phrase en français depuis un plugin - global/i');
    $response->checkElement('#template_testForPluginI18N', '/une phrase en français depuis un plugin - global/i');
  })
;
