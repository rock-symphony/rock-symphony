<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebResponse class.
 *
 * This class manages web responses. It supports cookies and headers management.
 *
 * @package    symfony
 * @subpackage response
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebResponse extends sfResponse
{
  public const FIRST = 'first';
  public const MIDDLE = '';
  public const LAST = 'last';
  public const ALL = 'ALL';
  public const RAW = 'RAW';

  /** @var array<string,array{name:string,value:string|null,options:array}> */
  protected $cookies = [];
  /** @var int */
  protected $statusCode = 200;
  /** @var string */
  protected $statusText = 'OK';
  /** @var bool */
  protected $headerOnly = false;
  /** @var string[] */
  protected $headers = [];
  /** @var string[] */
  protected $metas = [];
  /** @var string[] */
  protected $httpMetas = [];
  /** @var string[] */
  protected $positions = [self::FIRST, self::MIDDLE, self::LAST];
  /** @var array[][] [ string $position => [ string $file => array $options, ... ], ... ] */
  protected $stylesheets = [];
  /** @var array[][] [ string $position => [ string $file => array $options, ... ], ... ] */
  protected $javascripts = [];
  /** @var string[] */
  protected $slots = [];

  static protected $statusTexts = [
    '100' => 'Continue',
    '101' => 'Switching Protocols',
    '200' => 'OK',
    '201' => 'Created',
    '202' => 'Accepted',
    '203' => 'Non-Authoritative Information',
    '204' => 'No Content',
    '205' => 'Reset Content',
    '206' => 'Partial Content',
    '300' => 'Multiple Choices',
    '301' => 'Moved Permanently',
    '302' => 'Found',
    '303' => 'See Other',
    '304' => 'Not Modified',
    '305' => 'Use Proxy',
    '306' => '(Unused)',
    '307' => 'Temporary Redirect',
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '402' => 'Payment Required',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '405' => 'Method Not Allowed',
    '406' => 'Not Acceptable',
    '407' => 'Proxy Authentication Required',
    '408' => 'Request Timeout',
    '409' => 'Conflict',
    '410' => 'Gone',
    '411' => 'Length Required',
    '412' => 'Precondition Failed',
    '413' => 'Request Entity Too Large',
    '414' => 'Request-URI Too Long',
    '415' => 'Unsupported Media Type',
    '416' => 'Requested Range Not Satisfiable',
    '417' => 'Expectation Failed',
    '500' => 'Internal Server Error',
    '501' => 'Not Implemented',
    '502' => 'Bad Gateway',
    '503' => 'Service Unavailable',
    '504' => 'Gateway Timeout',
    '505' => 'HTTP Version Not Supported',
  ];

  private const DEFAULT_COOKIE_OPTIONS = [
    'expires'  => null,
    'path'     => '/',
    'domain'   => null,
    'secure'   => false,
    'httponly' => false,
    'samesite' => 'Lax',
  ];

  /**
   * Class constructor.
   *
   * Available options:
   *
   *  * charset:           The charset to use (utf-8 by default)
   *  * content_type:      The content type (text/html by default)
   *  * send_http_headers: Whether to send HTTP headers or not (true by default)
   *  * http_protocol:     The HTTP protocol to use for the response (HTTP/1.0 by default)
   *
   * @param  sfEventDispatcher $dispatcher  An sfEventDispatcher instance
   * @param  array             $options     An array of options
   *
   * @return void
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfResponse
   *
   * @see sfResponse
   */
  public function __construct(sfEventDispatcher $dispatcher, array $options = [])
  {
    parent::__construct($dispatcher, $options);

    $this->javascripts = array_combine($this->positions, array_fill(0, count($this->positions), array()));
    $this->stylesheets = array_combine($this->positions, array_fill(0, count($this->positions), array()));

    $this->options['charset'] = $this->options['charset'] ?? 'utf-8';
    $this->options['send_http_headers'] = $this->options['send_http_headers'] ?? true;
    $this->options['http_protocol'] = $this->options['http_protocol'] ?? 'HTTP/1.0';
    $this->options['content_type'] = $this->fixContentType(isset($this->options['content_type']) ? $this->options['content_type'] : 'text/html');
  }

  /**
   * Sets if the response consist of just HTTP headers.
   *
   * @param bool $value
   */
  public function setHeaderOnly(bool $value = true): void
  {
    $this->headerOnly = (boolean) $value;
  }

  /**
   * Returns if the response must only consist of HTTP headers.
   *
   * @return bool returns true if, false otherwise
   */
  public function isHeaderOnly(): bool
  {
    return $this->headerOnly;
  }

  /**
   * Sets a cookie.
   *
   * @param  string       $name      HTTP header name
   * @param  string|null  $value     Value for the cookie
   * @param  array        $options   Cookie options
   *
   * Available options:
   *
   *  (same as PHP native `setcookie()` function)
   *  @see https://www.php.net/manual/en/function.setcookie.php
  *
   *  * expire:   string|int|null  [null]   Cookie expiration period
   *  * path:     string           ["/"]    Cookie path
   *  * domain:   string|null      [null]   Cookie domain name
   *  * secure    bool             [false]  If cookie is `Secure`
   *  * httponly  bool             [false]  If cookie is `HttpOnly`
   *  * samesite  string           ["Lax"]  Cookie `SameSite` property
   *
   * @throws sfException If fails to set the cookie
   */
  public function setCookie(
    string $name,
    ?string $value,
    ...$options
  ): void {
    if (count($options) === 1 && isset($options[0]) && is_array($options[0])) {
      // Options passed as an assoc array.
      $options = $options[0];
    } elseif (count($options) > 1 && array_keys($options) === array_keys(array_keys($options))) {
      // Options is a numeric array -- ordered arguments passed as before.
      // (..., string $expires = null, string $path = '/', string $domain = null, bool $secure = false, bool $httponly = false, string $samesite = 'Lax')

      // Rebuild default options array to guarantee keys order matches previous function arguments order.
      // We cannot rely on DEFAULT_COOKIE_OPTIONS array order, as it's too far from this code and
      $defaultOptions = [
        'expires'  => self::DEFAULT_COOKIE_OPTIONS['expires'],
        'path'     => self::DEFAULT_COOKIE_OPTIONS['path'],
        'domain'   => self::DEFAULT_COOKIE_OPTIONS['domain'],
        'secure'   => self::DEFAULT_COOKIE_OPTIONS['secure'],
        'httponly' => self::DEFAULT_COOKIE_OPTIONS['httponly'],
        'samesite' => self::DEFAULT_COOKIE_OPTIONS['samesite'],
      ];
      $options = array_combine(
        array_keys($defaultOptions),
        $options + array_values($defaultOptions),
      );
    }
    // Otherwise Options is an empty array or an assoc array. Do nothing.

    /** @var array{expires:string|int|null,path:string,domain:string|null,secure:bool,httponly:bool,samesite:string} */
    $options = array_merge(self::DEFAULT_COOKIE_OPTIONS, $options);

    if ($options['expires'] !== null)
    {
      if (is_numeric($options['expires']))
      {
        $expire = (int) $options['expires'];
      }
      else
      {
        $expire = strtotime($options['expires']);
        if ($expire === false || $expire == -1)
        {
          throw new sfException('Your expire parameter is not valid.');
        }
      }

      $options['expires'] = $expire;
    }

    $this->cookies[$name] = ['name' => $name, 'value' => $value, 'options' => $options];
  }

  /**
   * Sets response status code.
   *
   * @param string $code  HTTP status code
   * @param string $name  HTTP status text
   *
   */
  public function setStatusCode(string $code, string $name = null): void
  {
    $this->statusCode = $code;
    $this->statusText = null !== $name ? $name : self::$statusTexts[$code];
  }

  /**
   * Retrieves status text for the current web response.
   *
   * @return string Status text
   */
  public function getStatusText(): string
  {
    return $this->statusText;
  }

  /**
   * Retrieves status code for the current web response.
   *
   * @return integer Status code
   */
  public function getStatusCode(): int
  {
    return $this->statusCode;
  }

  /**
   * Sets a HTTP header.
   *
   * @param string  $name     HTTP header name
   * @param string  $value    Value (if null, remove the HTTP header)
   * @param bool    $replace  Replace for the value
   *
   */
  public function setHttpHeader(string $name, string $value, bool $replace = true): void
  {
    $name = $this->normalizeHeaderName($name);

    if (null === $value)
    {
      unset($this->headers[$name]);

      return;
    }

    if ('Content-Type' == $name)
    {
      if ($replace || !$this->getHttpHeader('Content-Type', null))
      {
        $this->setContentType($value);
      }

      return;
    }

    if (!$replace)
    {
      $current = isset($this->headers[$name]) ? $this->headers[$name] : '';
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->headers[$name] = $value;
  }

  /**
   * Gets HTTP header current value.
   *
   * @param  string $name     HTTP header name
   * @param  string|null $default  Default value returned if named HTTP header is not found
   *
   * @return string|null
   */
  public function getHttpHeader(string $name, string $default = null): ?string
  {
    $name = $this->normalizeHeaderName($name);

    return isset($this->headers[$name]) ? $this->headers[$name] : $default;
  }

  /**
   * Checks if response has given HTTP header.
   *
   * @param  string $name  HTTP header name
   *
   * @return bool
   */
  public function hasHttpHeader(string $name): bool
  {
    return array_key_exists($this->normalizeHeaderName($name), $this->headers);
  }

  /**
   * Sets response content type.
   *
   * @param string $value  Content type
   *
   */
  public function setContentType(string $value): void
  {
    $this->headers['Content-Type'] = $this->fixContentType($value);
  }

  /**
   * Gets the current charset as defined by the content type.
   *
   * @return string The current charset
   */
  public function getCharset(): string
  {
    return $this->options['charset'];
  }

  /**
   * Gets response content type.
   *
   * @return string
   */
  public function getContentType(): string
  {
    return $this->getHttpHeader('Content-Type', $this->options['content_type']);
  }

  /**
   * Sends HTTP headers and cookies. Only the first invocation of this method will send the headers.
   * Subsequent invocations will silently do nothing. This allows certain actions to send headers early,
   * while still using the standard controller.
   */
  public function sendHttpHeaders(): void
  {
    if (!$this->options['send_http_headers'])
    {
      return;
    }

    // status
    $status = $this->options['http_protocol'].' '.$this->statusCode.' '.$this->statusText;
    header($status);

    if (substr(php_sapi_name(), 0, 3) == 'cgi')
    {
      // fastcgi servers cannot send this status information because it was sent by them already due to the HTT/1.0 line
      // so we can safely unset them. see ticket #3191
      unset($this->headers['Status']);
    }

    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Send status "%s"', $status))));
    }

    // headers
    if (!$this->getHttpHeader('Content-Type'))
    {
      $this->setContentType($this->options['content_type']);
    }
    foreach ($this->headers as $name => $value)
    {
      header($name.': '.$value);

      if ($value != '' && $this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Send header "%s: %s"', $name, $value))));
      }
    }

    // cookies
    foreach ($this->cookies as $cookie)
    {
      setrawcookie($cookie['name'], $cookie['value'], $cookie['options']);

      if ($this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Send cookie "%s": "%s"', $cookie['name'], $cookie['value']))));
      }
    }
    // prevent resending the headers
    $this->options['send_http_headers'] = false;
  }

  /**
   * Send content for the current web response.
   *
   */
  public function sendContent(): void
  {
    if (!$this->headerOnly)
    {
      parent::sendContent();
    }
  }

  /**
   * Sends the HTTP headers and the content.
   */
  public function send(): void
  {
    $this->sendHttpHeaders();
    $this->sendContent();

    if (function_exists('fastcgi_finish_request'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'response.fastcgi_finish_request'));
      fastcgi_finish_request();
    }
  }

  /**
   * Retrieves a normalized Header.
   *
   * @param  string $name  Header name
   *
   * @return string Normalized header
   */
  protected function normalizeHeaderName(string $name): string
  {
    return strtr(ucwords(strtr(strtolower($name), ['_' => ' ', '-' => ' '])), [' ' => '-']);
  }

  /**
   * Retrieves a formatted date.
   *
   * @param  string $timestamp  Timestamp
   * @param  string $type       Format type
   *
   * @return string Formatted date
   */
  static public function getDate(string $timestamp, string $type = 'rfc1123'): string
  {
    $type = strtolower($type);

    if ($type == 'rfc1123')
    {
      return substr(gmdate('r', $timestamp), 0, -5).'GMT';
    }
    else if ($type == 'rfc1036')
    {
      return gmdate('l, d-M-y H:i:s ', $timestamp).'GMT';
    }
    else if ($type == 'asctime')
    {
      return gmdate('D M j H:i:s', $timestamp);
    }
    else
    {
      throw new InvalidArgumentException('The second getDate() method parameter must be one of: rfc1123, rfc1036 or asctime.');
    }
  }

  /**
   * Adds vary to a http header.
   *
   * @param string $header  HTTP header
   */
  public function addVaryHttpHeader(string $header): void
  {
    $vary = $this->getHttpHeader('Vary');
    $currentHeaders = array();
    if ($vary)
    {
      $currentHeaders = preg_split('/\s*,\s*/', $vary);
    }
    $header = $this->normalizeHeaderName($header);

    if (!in_array($header, $currentHeaders))
    {
      $currentHeaders[] = $header;
      $this->setHttpHeader('Vary', implode(', ', $currentHeaders));
    }
  }

  /**
   * Adds an control cache http header.
   *
   * @param string $name   HTTP header
   * @param string $value  Value for the http header
   */
  public function addCacheControlHttpHeader(string $name, string $value = null): void
  {
    $cacheControl = $this->getHttpHeader('Cache-Control');
    $currentHeaders = array();
    if ($cacheControl)
    {
      foreach (preg_split('/\s*,\s*/', $cacheControl) as $tmp)
      {
        $tmp = explode('=', $tmp);
        $currentHeaders[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
      }
    }
    $currentHeaders[str_replace('_', '-', strtolower($name))] = $value;

    $headers = array();
    foreach ($currentHeaders as $key => $value)
    {
      $headers[] = $key.(null !== $value ? '='.$value : '');
    }

    $this->setHttpHeader('Cache-Control', implode(', ', $headers));
  }

  /**
   * Retrieves meta headers for the current web response.
   *
   * @return string[] Meta headers
   */
  public function getHttpMetas(): array
  {
    return $this->httpMetas;
  }

  /**
   * Adds a HTTP meta header.
   *
   * @param string  $key      Key to replace
   * @param string  $value    HTTP meta header value (if null, remove the HTTP meta)
   * @param bool    $replace  Replace or not
   */
  public function addHttpMeta(string $key, string $value, bool $replace = true): void
  {
    $key = $this->normalizeHeaderName($key);

    // set HTTP header
    $this->setHttpHeader($key, $value, $replace);

    if (null === $value)
    {
      unset($this->httpMetas[$key]);

      return;
    }

    if ('Content-Type' == $key)
    {
      $value = $this->getContentType();
    }
    elseif (!$replace)
    {
      $current = isset($this->httpMetas[$key]) ? $this->httpMetas[$key] : '';
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->httpMetas[$key] = $value;
  }

  /**
   * Retrieves all meta headers.
   *
   * @return array List of meta headers
   */
  public function getMetas(): array
  {
    return $this->metas;
  }

  /**
   * Adds a meta header.
   *
   * @param string  $key      Name of the header
   * @param string  $value    Meta header value (if null, remove the meta)
   * @param bool    $replace  true if it's replaceable
   * @param bool    $escape   true for escaping the header
   */
  public function addMeta(string $key, string $value, bool $replace = true, bool $escape = true): void
  {
    $key = strtolower($key);

    if (null === $value)
    {
      unset($this->metas[$key]);

      return;
    }

    // FIXME: If you use the i18n layer and escape the data here, it won't work
    // see include_metas() in AssetHelper
    if ($escape)
    {
      $value = htmlspecialchars($value, ENT_QUOTES, $this->options['charset']);
    }

    $current = isset($this->metas[$key]) ? $this->metas[$key] : null;
    if ($replace || !$current)
    {
      $this->metas[$key] = $value;
    }
  }

  /**
   * Retrieves title for the current web response.
   *
   * @return string Title
   */
  public function getTitle(): string
  {
    return isset($this->metas['title']) ? $this->metas['title'] : '';
  }

  /**
   * Preprend title
   *
   * @param string  $title      Title name
   * @param string  $separator  Separator string (default: " - ")
   * @param boolean $escape     true, for escaping the title
   */
  public function prependTitle(string $title, string $separator = ' - ', bool $escape = true): void
  {
    if (empty($this->metas['title']))
    {
      $this->setTitle($title);

      return;
    }

    // FIXME: If you use the i18n layer and escape the data here, it won't work
    // see include_metas() in AssetHelper
    if ($escape)
    {
      $title = htmlspecialchars($title, ENT_QUOTES, $this->options['charset']);
    }

    $this->metas['title'] = $title.$separator.$this->metas['title'];
  }

  /**
   * Sets title for the current web response.
   *
   * @param string  $title   Title name
   * @param bool    $escape  true, for escaping the title
   */
  public function setTitle(string $title, bool $escape = true): void
  {
    $this->addMeta('title', $title, true, $escape);
  }

  /**
   * Returns the available position names for stylesheets and javascripts in order.
   *
   * @return array An array of position names
   */
  public function getPositions(): array
  {
    return $this->positions;
  }

  /**
   * Retrieves stylesheets for the current web response.
   *
   * By default, the position is sfWebResponse::ALL,
   * and the method returns all stylesheets ordered by position.
   *
   * @param  string  $position The position
   *
   * @return array   An associative array of stylesheet files as keys and options as values
   */
  public function getStylesheets(string $position = self::ALL): array
  {
    if (self::ALL === $position)
    {
      $stylesheets = array();
      foreach ($this->getPositions() as $position)
      {
        foreach ($this->stylesheets[$position] as $file => $options)
        {
          $stylesheets[$file] = $options;
        }
      }

      return $stylesheets;
    }
    else if (self::RAW === $position)
    {
      return $this->stylesheets;
    }

    $this->validatePosition($position);

    return $this->stylesheets[$position];
  }

  /**
   * Adds a stylesheet to the current web response.
   *
   * @param string $file      The stylesheet file
   * @param string $position  Position
   * @param array  $options   Stylesheet options
   */
  public function addStylesheet(string $file, string $position = self::MIDDLE, array $options = []): void
  {
    $this->validatePosition($position);

    $this->stylesheets[$position][$file] = $options;
  }

  /**
   * Removes a stylesheet from the current web response.
   *
   * @param string $file The stylesheet file to remove
   */
  public function removeStylesheet(string $file): void
  {
    foreach ($this->getPositions() as $position)
    {
      unset($this->stylesheets[$position][$file]);
    }
  }

  /**
   * Clear all previously added stylesheets
   */
  public function clearStylesheets(): void
  {
    foreach (array_keys($this->getStylesheets()) as $file)
    {
      $this->removeStylesheet($file);
    }
  }

  /**
   * Retrieves javascript files from the current web response.
   *
   * By default, the position is sfWebResponse::ALL,
   * and the method returns all javascripts ordered by position.
   *
   * @param  string $position  The position
   *
   * @return array An associative array of javascript files as keys and options as values
   */
  public function getJavascripts(string $position = self::ALL): array
  {
    if (self::ALL === $position)
    {
      $javascripts = array();
      foreach ($this->getPositions() as $position)
      {
        foreach ($this->javascripts[$position] as $file => $options)
        {
          $javascripts[$file] = $options;
        }
      }

      return $javascripts;
    }
    else if (self::RAW === $position)
    {
      return $this->javascripts;
    }

    $this->validatePosition($position);

    return $this->javascripts[$position];
  }

  /**
   * Adds javascript code to the current web response.
   *
   * @param string $file      The JavaScript file
   * @param string $position  Position
   * @param array  $options   Javascript options
   */
  public function addJavascript(string $file, string $position = self::MIDDLE, array $options = []): void
  {
    $this->validatePosition($position);

    $this->javascripts[$position][$file] = $options;
  }

  /**
   * Removes a JavaScript file from the current web response.
   *
   * @param string $file The Javascript file to remove
   */
  public function removeJavascript(string $file): void
  {
    foreach ($this->getPositions() as $position)
    {
      unset($this->javascripts[$position][$file]);
    }
  }

  /**
   * Clear all previously added javascripts
   */
  public function clearJavascripts(): void
  {
    foreach (array_keys($this->getJavascripts()) as $file)
    {
      $this->removeJavascript($file);
    }
  }

  /**
   * Retrieves slots from the current web response.
   *
   * @return string[]
   */
  public function getSlots(): array
  {
    return $this->slots;
  }

  /**
   * Sets a slot content.
   *
   * @param string $name     Slot name
   * @param string $content  Content
   */
  public function setSlot(string $name, string $content): void
  {
    $this->slots[$name] = $content;
  }

  /**
   * Retrieves cookies from the current web response.
   *
   * @return array Cookies
   */
  public function getCookies(): array
  {
    return $this->cookies;
  }

  /**
   * Retrieves HTTP headers from the current web response.
   *
   * @return string[] HTTP headers
   */
  public function getHttpHeaders(): array
  {
    return $this->headers;
  }

  /**
   * Cleans HTTP headers from the current web response.
   */
  public function clearHttpHeaders(): void
  {
    $this->headers = [];
  }

  /**
   * Copies all properties from a given sfWebResponse object to the current one.
   *
   * @param sfWebResponse $response  An sfWebResponse instance
   */
  public function copyProperties(sfWebResponse $response): void
  {
    $this->options     = $response->getOptions();
    $this->headers     = $response->getHttpHeaders();
    $this->metas       = $response->getMetas();
    $this->httpMetas   = $response->getHttpMetas();
    $this->stylesheets = $response->getStylesheets(self::RAW);
    $this->javascripts = $response->getJavascripts(self::RAW);
    $this->slots       = $response->getSlots();

    // HTTP protocol must be from the current request
    // this fix is not nice but that's the only way to fix it and keep BC (see #9254)
    $this->options['http_protocol'] = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
  }

  /**
   * Merges all properties from a given sfWebResponse object to the current one.
   *
   * @param sfWebResponse $response  An sfWebResponse instance
   */
  public function merge(sfWebResponse $response): void
  {
    foreach ($this->getPositions() as $position)
    {
      $this->javascripts[$position] = array_merge($this->getJavascripts($position), $response->getJavascripts($position));
      $this->stylesheets[$position] = array_merge($this->getStylesheets($position), $response->getStylesheets($position));
    }

    $this->slots = array_merge($this->getSlots(), $response->getSlots());
  }

  /**
   * Validate a position name.
   *
   * @param  string $position
   *
   * @throws InvalidArgumentException if the position is not available
   */
  protected function validatePosition(string $position): void
  {
    if (!in_array($position, $this->positions, true))
    {
      throw new InvalidArgumentException(sprintf('The position "%s" does not exist (available positions: %s).', $position, implode(', ', $this->positions)));
    }
  }

  /**
   * Fixes the content type by adding the charset for text content types.
   *
   * @param  string $contentType  The content type
   *
   * @return string The content type with the charset if needed
   */
  protected function fixContentType(string $contentType): string
  {
    // add charset if needed (only on text content)
    if (false === stripos($contentType, 'charset') && (0 === stripos($contentType, 'text/') || strlen($contentType) - 3 === strripos($contentType, 'xml')))
    {
      $contentType .= '; charset='.$this->options['charset'];
    }

    // change the charset for the response
    if (preg_match('/charset\s*=\s*(.+)\s*$/', $contentType, $match))
    {
      $this->options['charset'] = $match[1];
    }

    return $contentType;
  }
}
