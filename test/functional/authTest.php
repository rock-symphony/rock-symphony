<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
if (!include(__DIR__.'/../bootstrap/functional.php'))
{
  return;
}

class sfAuthTestBrowser extends sfTestBrowser
{
  public function checkNonAuth(): self
  {
    return $this->get('/auth/basic')

      ->with('request', function (sfTesterRequest $request) {
        $request->isParameter('module', 'auth');
        $request->isParameter('action', 'basic');
      })

      ->with('response', function (sfTesterResponse $response) {
        $response->isStatusCode(401);
        $response->checkElement('#user', '');
        $response->checkElement('#password', '');
        $response->checkElement('#msg', 'KO');
      });
  }

  public function checkAuth(): self
  {
    return $this->get('/auth/basic')

      ->with('request', function (sfTesterRequest $request) {
        $request->isParameter('module', 'auth');
        $request->isParameter('action', 'basic');
      })

      ->with('response', function (sfTesterResponse $response) {
        $response->isStatusCode(200);
        $response->checkElement('#user', 'foo');
        $response->checkElement('#password', 'bar');
        $response->checkElement('#msg', 'OK');
      });
  }
}

$b = new sfAuthTestBrowser();

// default main page
$b->
  checkNonAuth()->

  setAuth('foo', 'bar')->

  checkAuth()->

  restart()->

  checkNonAuth()
;
