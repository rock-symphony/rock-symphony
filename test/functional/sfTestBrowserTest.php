<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'frontend';
if (!include(__DIR__.'/../bootstrap/functional.php'))
{
  return;
}

class TestBrowser extends sfTestBrowser
{
  public $events = [];

  public function listen(sfEvent $event): void
  {
    $this->events[] = $event;
  }
}

$b = new TestBrowser();
$b->addListener('context.load_factories', array($b, 'listen'));

// listeners
$b->get('/');
$b->test()->is(count($b->events), 1, 'browser can connect to context.load_factories');

// exceptions
$b->get('/exception/noException')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'exception');
    $request->isParameter('action', 'noException');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->matches('/foo/');
  });

$b->get('/exception/throwsException')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'exception');
    $request->isParameter('action', 'throwsException');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(500);
  })
  ->throwsException(Exception::class);

$b->get('/exception/throwsException')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'exception');
    $request->isParameter('action', 'throwsException');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(500);
  })
  ->throwsException(Exception::class, '/Exception message/');

$b->get('/exception/throwsException')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'exception');
    $request->isParameter('action', 'throwsException');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(500);
  })
  ->throwsException(Exception::class, '/message/');

$b->get('/exception/throwsException')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'exception');
    $request->isParameter('action', 'throwsException');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(500);
  })
  ->throwsException(null, '!/sfException/');

$b->get('/exception/throwsSfException')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'exception');
    $request->isParameter('action', 'throwsSfException');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(500);
  })
  ->throwsException(sfException::class);

$b->get('/exception/throwsSfException')
  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'exception');
    $request->isParameter('action', 'throwsSfException');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(500);
  })
  ->throwsException(sfException::class, 'sfException message');

$b->get('/browser')
  ->with('response', function (sfTesterResponse $response) {
    $response->matches('/html/');
    $response->checkElement('h1', 'html');
  });

$b->get('/browser/text')
  ->with('response', function (sfTesterResponse $response) {
    $response->matches('/text/');
  })
;

try
{
  $b->with('response', function (sfTesterResponse $response) {
    $response->checkElement('h1', 'text');
  });
  $b->test()->fail('The DOM is not accessible if the response content type is not HTML');
}
catch (LogicException $e)
{
  $b->test()->pass('The DOM is not accessible if the response content type is not HTML');
}

// check response headers
$b->get('/browser/responseHeader')
  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
    $response->isHeader('content-type', 'text/plain; charset=utf-8');
    $response->isHeader('content-type', '#text/plain#');
    $response->isHeader('content-type', '!#text/html#');
    $response->isHeader('foo', 'bar');
    $response->isHeader('foo', 'foobar');
  })
;

// cookies
$b->setCookie('foo', 'bar')
  ->setCookie('bar', 'foo')
  ->setCookie('foofoo', 'foo', time() - 10);

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foofoo', false);
    $request->hasCookie('foo');
    $request->isCookie('foo', 'bar');
    $request->isCookie('foo', '/a/');
    $request->isCookie('foo', '!/z/');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', 'bar.foo-');
  });

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo');
    $request->isCookie('foo', 'bar');
    $request->isCookie('foo', '/a/');
    $request->isCookie('foo', '!/z/');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', 'bar.foo-');
  });

$b->removeCookie('foo');

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo', false);
    $request->hasCookie('bar');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', '.foo-');
  });

$b->clearCookies();

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo', false);
    $request->hasCookie('bar', false);
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', '.-');
  })
;

$b->setCookie('foo', 'bar')
  ->setCookie('bar', 'foo');

$b->get('/cookie/setCookie');

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo');
    $request->isCookie('foo', 'bar');
    $request->isCookie('foo', '/a/');
    $request->isCookie('foo', '!/z/');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', 'bar.foo-barfoo');
  });

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo');
    $request->isCookie('foo', 'bar');
    $request->isCookie('foo', '/a/');
    $request->isCookie('foo', '!/z/');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', 'bar.foo-barfoo');
  });

$b->removeCookie('foo');

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo', false);
    $request->hasCookie('bar');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', '.foo-barfoo');
  });

$b->get('/cookie/removeCookie');

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo', false);
    $request->hasCookie('bar');
  })
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', '.foo-');
  });

$b->get('/cookie/setCookie');

$b->clearCookies();

$b->get('/cookie')
  ->with('request', function (sfTesterRequest $request) {
    $request->hasCookie('foo', false);
    $request->hasCookie('bar', false);
  })

  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('p', '.-');
  });

$b->get('/browser')
  ->with('request', function (sfTesterRequest $request) {
    $request->isMethod('get');
  });

$b->post('/browser')
  ->with('request', function (sfTesterRequest $request) {
    $request->isMethod('post');
  });

$b->call('/browser', 'put')
  ->with('request', function (sfTesterRequest $request) {
    $request->isMethod('put');
  });

// sfBrowser: clean the custom view templates
$b->get('/browser/templateCustom')
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('#test', 'template');
  });

$b->get('/browser/templateCustom/custom/1')
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('#test', 'template 1');
  });

$b->get('/browser/templateCustom')
  ->with('response', function (sfTesterResponse $response) {
    $response->checkElement('#test', 'template');
  });

$b->getAndCheck('browser', 'redirect1', null, 302)

  ->followRedirect()

  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'browser');
    $request->isParameter('action', 'redirectTarget1');
  })

  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
  });

$b->getAndCheck('browser', 'redirect2', null, 302)

  ->followRedirect()

  ->with('request', function (sfTesterRequest $request) {
    $request->isParameter('module', 'browser');
    $request->isParameter('action', 'redirectTarget2');
  })

  ->with('response', function (sfTesterResponse $response) {
    $response->isStatusCode(200);
  });
