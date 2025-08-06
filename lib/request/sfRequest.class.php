<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRequest provides methods for manipulating client request information such
 * as attributes, and parameters. It is also possible to manipulate the
 * request method originally sent by the user.
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
abstract class sfRequest
{
  public const GET    = 'GET';
  public const POST   = 'POST';
  public const PUT    = 'PUT';
  public const PATCH  = 'PATCH';
  public const DELETE = 'DELETE';
  public const HEAD   = 'HEAD';
  public const OPTIONS = 'OPTIONS';

  public const METHODS = [
    self::GET,
    self::POST,
    self::PUT,
    self::PATCH,
    self::DELETE,
    self::HEAD,
    self::OPTIONS
  ];

  protected sfEventDispatcher $dispatcher;
  protected ?string $content = null;
  protected ?string $method = null;
  protected array $options = [];
  protected sfParameterHolder $parameterHolder;
  protected sfParameterHolder $attributeHolder;

  /**
   * Class constructor.
   *
   * Available options:
   *
   *  * logging: Whether to enable logging or not (false by default)
   *
   * @param  sfEventDispatcher $dispatcher  An sfEventDispatcher instance
   * @param  array             $parameters  An associative array of initialization parameters
   * @param  array             $attributes  An associative array of initialization attributes
   * @param  array             $options     An associative array of options
   */
  public function __construct(sfEventDispatcher $dispatcher, array $parameters = [], array $attributes = [], array $options = [])
  {
    $this->dispatcher = $dispatcher;
    $this->options = $options;

    if (!isset($this->options['logging']))
    {
      $this->options['logging'] = false;
    }

    // initialize parameter and attribute holders
    $this->parameterHolder = new sfParameterHolder($parameters);
    $this->attributeHolder = new sfParameterHolder($attributes);
  }

  /**
   * Return an option value or null if option does not exist
   *
   * @param string $name The option name.
   *
   * @return mixed The option value
   */
  public function getOption(string $name)
  {
    return $this->options[$name] ?? null;
  }

  /**
   * Returns the options.
   *
   * @return array The options.
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * Extracts parameter values from the request.
   *
   * @param  array $names  An indexed array of parameter names to extract
   *
   * @return array An associative array of parameters and their values. If
   *               a specified parameter doesn't exist an empty string will
   *               be returned for its value
   */
  public function extractParameters(array $names): array
  {
    $array = array();

    $parameters = $this->parameterHolder->getAll();
    foreach ($parameters as $key => $value)
    {
      if (in_array($key, $names))
      {
        $array[$key] = $value;
      }
    }

    return $array;
  }

  /**
   * Gets the request method.
   *
   * @return string The request method
   */
  public function getMethod(): string
  {
    return $this->method;
  }

  /**
   * Sets the request method.
   *
   * @param string $method  The request method
   *
   * @throws <b>sfException</b> - If the specified request method is invalid
   */
  public function setMethod(string $method): void
  {
    if (!in_array(strtoupper($method), self::METHODS)) {
      throw new sfException(sprintf('Invalid request method: %s.', $method));
    }

    $this->method = strtoupper($method);
  }

  /**
   * Retrieves the parameters for the current request.
   *
   * @return sfParameterHolder The parameter holder
   */
  public function getParameterHolder(): sfParameterHolder
  {
    return $this->parameterHolder;
  }

  /**
   * Retrieves the attributes holder.
   *
   * @return sfParameterHolder The attribute holder
   */
  public function getAttributeHolder(): sfParameterHolder
  {
    return $this->attributeHolder;
  }

  /**
   * Retrieves an attribute from the current request.
   *
   * @param string     $name     Attribute name
   * @param mixed|null $default  Default attribute value
   *
   * @return mixed An attribute value
   */
  public function getAttribute(string $name, mixed $default = null): mixed
  {
    return $this->attributeHolder->get($name, $default);
  }

  /**
   * Indicates whether or not an attribute exist for the current request.
   *
   * @param  string $name  Attribute name
   *
   * @return bool true, if the attribute exists otherwise false
   */
  public function hasAttribute(string $name): bool
  {
    return $this->attributeHolder->has($name);
  }

  /**
   * Sets an attribute for the request.
   *
   * @param string $name   Attribute name
   * @param mixed $value  Value for the attribute
   *
   */
  public function setAttribute(string $name, $value): void
  {
    $this->attributeHolder->set($name, $value);
  }

  /**
   * Retrieves a parameter for the current request.
   *
   * @param string $name    Parameter name
   * @param mixed $default Parameter default value
   *
   * @return mixed
   */
  public function getParameter(string $name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }

  /**
   * Indicates whether or not a parameter exist for the current request.
   *
   * @param  string $name  Parameter name
   *
   * @return bool true, if the parameter exists otherwise false
   */
  public function hasParameter(string $name): bool
  {
    return $this->parameterHolder->has($name);
  }

  /**
   * Sets a parameter for the current request.
   *
   * @param string $name   Parameter name
   * @param mixed $value  Parameter value
   *
   */
  public function setParameter(string $name, $value): void
  {
    $this->parameterHolder->set($name, $value);
  }

  /**
   * Returns the content of the current request.
   *
   * @return string|false The content or false if none is available
   */
  public function getContent()
  {
    if (null === $this->content && '' === trim($this->content = file_get_contents('php://input')))
    {
      $this->content = false;
    }

    return $this->content;
  }

  public function __clone()
  {
    $this->parameterHolder = clone $this->parameterHolder;
    $this->attributeHolder = clone $this->attributeHolder;
  }
}
