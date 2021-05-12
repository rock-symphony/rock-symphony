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

$b->get('/escaping/on')

  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'escaping');
    $request->isParameter('action', 'on');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->matches('#<h1>Lorem &lt;strong&gt;ipsum&lt;/strong&gt; dolor sit amet.</h1>#');
    $response->matches('#<h2>Lorem &lt;strong&gt;ipsum&lt;/strong&gt; dolor sit amet.</h2>#');
    $response->matches('#<h3>Lorem &lt;strong&gt;ipsum&lt;/strong&gt; dolor sit amet.</h3>#');
    $response->matches('#<h4>Lorem <strong>ipsum</strong> dolor sit amet.</h4>#');
    $response->matches('#<h5>Lorem &lt;strong&gt;ipsum&lt;/strong&gt; dolor sit amet.</h5>#');
    $response->matches('#<h6>Lorem <strong>ipsum</strong> dolor sit amet.</h6>#');
    $response->checkElement('span.no', 2);
  })
;

$b->get('/escaping/off')

  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'escaping');
    $request->isParameter('action', 'off');
  })

  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->matches('#<h1>Lorem <strong>ipsum</strong> dolor sit amet.</h1>#');
    $response->matches('#<h2>Lorem <strong>ipsum</strong> dolor sit amet.</h2>#');
  })
;
