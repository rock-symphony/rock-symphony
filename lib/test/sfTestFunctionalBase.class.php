<?php

require_once(__DIR__.'/../vendor/lime/lime.php');

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTestFunctional tests an application by using a browser simulator.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 *
 * @mixin \sfBrowser
 */
abstract class sfTestFunctionalBase
{
  /** @var \sfBrowser */
  protected $browser;
  /** @var array<string,sfTester> */
  protected $testers = [];

  /** @var \lime_test|null */
  protected static $test = null;

  /**
   * @param  sfBrowser                      $browser  A sfBrowserBase instance
   * @param  lime_test|null                 $lime     A lime instance
   * @param  array<string,string|sfTester>  $testers  Testers to use
   */
  public function __construct(sfBrowser $browser, lime_test $lime = null, array $testers = [])
  {
    $this->browser = $browser;

    if (null === self::$test)
    {
      self::$test = $lime ?: new lime_test();
    }

    $this->setTesters(array_merge([
      'request'  => sfTesterRequest::class,
      'response' => sfTesterResponse::class,
      'user'     => sfTesterUser::class,
      'mailer'   => sfTesterMailer::class,
    ], $testers));

    // register our shutdown function
    register_shutdown_function([$this, 'shutdown']);

    // register our error/exception handlers
    set_error_handler([$this, 'handlePhpError']);
    set_exception_handler([$this, 'handleException']);
  }

  /**
   * Run a callback for the tester associated with the given name.
   *
   * @param  string  $name  The tester name
   *
   * @return  $this
   */
  public function with(string $name, callable $block): self
  {
    if (!isset($this->testers[$name]))
    {
      throw new InvalidArgumentException(sprintf('The "%s" tester does not exist.', $name));
    }

    $tester = $this->testers[$name];
    $tester->initialize();

    $block($tester);

    return $this;
  }

  /**
   * Sets the testers.
   *
   * @param array<string,string|\sfTester> $testers An array of named testers
   */
  public function setTesters(array $testers): void
  {
    foreach ($testers as $name => $tester)
    {
      $this->setTester($name, $tester);
    }
  }

  /**
   * Sets a tester.
   *
   * @param string          $name   The tester name
   * @param sfTester|string $tester A sfTester instance or a tester class name
   */
  public function setTester(string $name, $tester): void
  {
    if (is_string($tester))
    {
      $tester = new $tester($this, self::$test);
    }

    if (!$tester instanceof sfTester)
    {
      throw new InvalidArgumentException(sprintf('The tester "%s" is not of class sfTester.', $name));
    }

    $this->testers[$name] = $tester;
  }

  /**
   * Shutdown function.
   *
   * @return void
   */
  public function shutdown(): void
  {
    $this->checkCurrentExceptionIsEmpty();
  }

  /**
   * Retrieves the lime_test instance.
   *
   * @return lime_test The lime_test instance
   */
  public function test(): lime_test
  {
    return self::$test;
  }

  /**
   * Gets a uri.
   *
   * @param string $uri         The URI to fetch
   * @param array  $parameters  The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return sfTestFunctionalBase
   */
  public function get(string $uri, array $parameters = [], bool $changeStack = true): self
  {
    return $this->call($uri, 'get', $parameters, $changeStack);
  }

  /**
   * Retrieves and checks an action.
   *
   * @param  string       $module  Module name
   * @param  string       $action  Action name
   * @param  string|null  $url     Url
   * @param  int          $code    The expected return status code
   *
   * @return $this The current sfTestFunctionalBase instance
   */
  public function getAndCheck(string $module, string $action, ?string $url = null, int $code = 200): self
  {
    return $this->get($url ?: "/{$module}/{$action}")

      ->with('request', function (sfTesterRequest $request) use ($module, $action) {
        $request->isParameter('module', $module);
        $request->isParameter('action', $action);
      })

      ->with('response', function (sfTesterResponse $response) use ($code) {
        $response->isStatusCode($code);
      });
  }

  /**
   * Posts a uri.
   *
   * @param string $uri         The URI to fetch
   * @param array  $parameters  The Request parameters
   * @param bool   $changeStack  Change the browser history stack?
   *
   * @return $this
   */
  public function post(string $uri, array $parameters = [], bool $changeStack = true): self
  {
    return $this->call($uri, 'post', $parameters, $changeStack);
  }

  /**
   * Calls a request.
   *
   * @param  string $uri          URI to be invoked
   * @param  string $method       HTTP method used
   * @param  array  $parameters   Additional parameters
   * @param  bool   $changeStack  If set to false ActionStack is not changed
   *
   * @return $this The current sfTestFunctionalBase instance
   */
  public function call(string $uri, string $method = 'get', array $parameters = [], bool $changeStack = true): self
  {
    $this->checkCurrentExceptionIsEmpty();

    $uri = $this->browser->fixUri($uri);

    $this->test()->comment(sprintf('%s %s', strtolower($method), $uri));

    foreach ($this->testers as $tester)
    {
      $tester->prepare();
    }

    $this->browser->call($uri, $method, $parameters, $changeStack);

    return $this;
  }

  /**
   * Simulates deselecting a checkbox or radiobutton.
   *
   * @param string  $name       The checkbox or radiobutton id, name or text
   *
   * @return $this
   */
  public function deselect(string $name): self
  {
    $this->browser->doSelect($name, false);

    return $this;
  }

  /**
   * Simulates selecting a checkbox or radiobutton.
   *
   * @param string  $name       The checkbox or radiobutton id, name or text
   *
   * @return $this
   */
  public function select(string $name): self
  {
    $this->browser->doSelect($name, true);

    return $this;
  }

  /**
   * Simulates a click on a link or button.
   *
   * @param string  $name       The link or button text
   * @param array   $arguments  The arguments to pass to the link
   * @param array   $options    An array of options
   *
   * @return $this
   */
  public function click(string $name, array $arguments = [], array $options = []): self
  {
    if ($name instanceof DOMElement) {
      [$uri, $method, $parameters] = $this->doClickElement($name, $arguments, $options);
    } else {
      try {
        [$uri, $method, $parameters] = $this->doClick($name, $arguments, $options);
      } catch (InvalidArgumentException $e) {
        [$uri, $method, $parameters] = $this->doClickCssSelector($name, $arguments, $options);
      }
    }

    return $this->call($uri, $method, $parameters);
  }

  /**
   * Simulates the browser back button.
   *
   * @return $this The current sfTestFunctionalBase instance
   */
  public function back(): self
  {
    $this->test()->comment('back');

    $this->browser->back();

    return $this;
  }

  /**
   * Simulates the browser forward button.
   *
   * @return $this The current sfTestFunctionalBase instance
   */
  public function forward(): self
  {
    $this->test()->comment('forward');

    $this->browser->forward();

    return $this;
  }

  /**
   * Outputs an information message.
   *
   * @param string $message A message
   *
   * @return $this The current sfTestFunctionalBase instance
   */
  public function info(string $message): self
  {
    $this->test()->info($message);

    return $this;
  }

  /**
   * Checks that the current response contains a given text.
   *
   * @param  string  $uri  Uniform resource identifier
   *
   * @return $this The current sfTestFunctionalBase instance
   */
  public function check(string $uri): self
  {
    $this->get($uri)->with('response', function (sfTesterResponse $response) {
      $response->isStatusCode(200);
    });

    return $this;
  }

  /**
   * Tests if an exception is thrown by the latest request.
   *
   * @param  string|null  $class    Class name
   * @param  string|null  $message  Message name
   *
   * @return $this The current sfTestFunctionalBase instance
   */
  public function throwsException(string $class = null, string $message = null): self
  {
    $e = $this->browser->getCurrentException();

    if (null === $e)
    {
      $this->test()->fail('response returns an exception');
    }
    else
    {
      if (null !== $class)
      {
        $this->test()->ok($e instanceof $class, sprintf('response returns an exception of class "%s"', $class));
      }

      if (null !== $message && preg_match('/^(!)?([^a-zA-Z0-9\\\\]).+?\\2[ims]?$/', $message, $match))
      {
        if ($match[1] == '!')
        {
          $this->test()->unlike($e->getMessage(), substr($message, 1), sprintf('response exception message does not match regex "%s"', $message));
        }
        else
        {
          $this->test()->like($e->getMessage(), $message, sprintf('response exception message matches regex "%s"', $message));
        }
      }
      else if (null !== $message)
      {
        $this->test()->is($e->getMessage(), $message, sprintf('response exception message is "%s"', $message));
      }
    }

    $this->resetCurrentException();

    return $this;
  }

  /**
   * Triggers a test failure if an uncaught exception is present.
   *
   * @return  bool
   */
  public function checkCurrentExceptionIsEmpty(): bool
  {
    $empty = $this->browser->checkCurrentExceptionIsEmpty();

    if (false === $empty) {
      $this->test()->fail(sprintf(
        'last request threw an uncaught exception "%s: %s"',
        get_class($this->browser->getCurrentException()),
        $this->browser->getCurrentException()->getMessage()
      ));
    }

    return $empty;
  }

  public function __call($method, $arguments)
  {
    $retval = call_user_func_array([$this->browser, $method], $arguments);

    // fix the fluent interface
    return $retval === $this->browser ? $this : $retval;
  }

  /**
   * Error handler for the current test browser instance.
   *
   * @param mixed  $errno    Error number
   * @param string $errstr   Error message
   * @param string $errfile  Error file
   * @param mixed  $errline  Error line
   */
  static public function handlePhpError($errno, $errstr, $errfile, $errline)
  {
    if (($errno & error_reporting()) == 0)
    {
      return false;
    }

    $msg = sprintf('PHP sent a "%%s" error at %s line %s (%s)', $errfile, $errline, $errstr);
    switch ($errno)
    {
      case E_WARNING:
        $msg = sprintf($msg, 'warning');
        throw new RuntimeException($msg);

      case E_NOTICE:
        $msg = sprintf($msg, 'notice');
        throw new RuntimeException($msg);

      case E_STRICT:
        $msg = sprintf($msg, 'strict');
        throw new RuntimeException($msg);

      case E_RECOVERABLE_ERROR:
        $msg = sprintf($msg, 'catchable');
        throw new RuntimeException($msg);
    }

    return false;
  }

  /**
   * Exception handler for the current test browser instance.
   *
   * @param Throwable $exception The exception
   */
  function handleException(Throwable $exception): void
  {
    $this->test()->error(sprintf('%s: %s', get_class($exception), $exception->getMessage()));

    $traceData = $exception->getTrace();
    array_unshift($traceData, [
      'function' => '',
      'file'     => $exception->getFile() ?: 'n/a',
      'line'     => $exception->getLine() ?: 'n/a',
      'args'     => [],
    ]);

    for ($i = 0, $count = count($traceData); $i < $count; $i++) {
      $line = $traceData[$i]['line'] ?? 'n/a';
      $file = $traceData[$i]['file'] ?? 'n/a';
      $args = $traceData[$i]['args'] ?? [];
      $this->test()->error(sprintf(
        '  at %s%s%s() in %s line %s',
        $traceData[$i]['class'] ?? '',
        $traceData[$i]['type'] ?? '',
        $traceData[$i]['function'],
        $file,
        $line
      ));
    }

    $this->test()->fail('An uncaught exception has been thrown.');
  }
}
