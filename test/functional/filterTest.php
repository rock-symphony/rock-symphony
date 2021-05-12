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

$b = new sfTestBrowser();

// filter
$b->get('/filter')

  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'filter');
    $request->isParameter('action', 'index');
  })

  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('div[class="before"]', 1);
    $response->checkElement('div[class="after"]', 1);
  })
;

// filter with a forward in the same module
$b->get('/filter/indexWithForward')

  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'filter');
    $request->isParameter('action', 'indexWithForward');
  })

  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->checkElement('div[class="before"]', 2);
    $response->checkElement('div[class="after"]', 1);
  })
;
