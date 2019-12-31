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
 * sfFilter provides a way for you to intercept incoming requests or outgoing responses.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
abstract class sfFilter
{
  /** @var sfParameterHolder */
  protected $parameterHolder = null;
  /** @var sfContext */
  protected $context = null;

  /** @var bool[] */
  public static $filterCalled = [];

  /**
   * Class constructor.
   *
   * @see initialize()
   *
   * @param sfContext $context
   * @param array     $parameters
   */
  public function __construct(sfContext $context, array $parameters = [])
  {
    $this->initialize($context, $parameters);
  }

  abstract function execute(sfFilterChain $chain): void;

  /**
   * Initializes this Filter.
   *
   * @param sfContext $context    The current application context
   * @param array     $parameters An associative array of initialization parameters
   *
   * @return void
   */
  public function initialize(sfContext $context, array $parameters = []): void
  {
    $this->context = $context;

    $this->parameterHolder = new sfParameterHolder();
    $this->parameterHolder->add($parameters);
  }

  /**
   * Returns true if this is the first call to the sfFilter instance.
   *
   * @return boolean true if this is the first call to the sfFilter instance, false otherwise
   */
  protected function isFirstCall(): bool
  {
    $class = get_class($this);
    if (isset(self::$filterCalled[$class]))
    {
      return false;
    }
    else
    {
      self::$filterCalled[$class] = true;

      return true;
    }
  }

  /**
   * Retrieves the current application context.
   *
   * @return sfContext The current sfContext instance
   */
  public final function getContext(): sfContext
  {
    return $this->context;
  }

  /**
   * Gets the parameter holder for this object.
   *
   * @return sfParameterHolder A sfParameterHolder instance
   */
  public function getParameterHolder(): sfParameterHolder
  {
    return $this->parameterHolder;
  }

  /**
   * Gets the parameter associated with the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->get()</code>
   *
   * @param string $name    The key name
   * @param mixed  $default The default value
   *
   * @return mixed The value associated with the key
   *
   * @see sfParameterHolder
   */
  public function getParameter(string $name, $default = null)
  {
    return $this->parameterHolder->get($name, $default);
  }

  /**
   * Returns true if the given key exists in the parameter holder.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->has()</code>
   *
   * @param string $name The key name
   *
   * @return boolean true if the given key exists, false otherwise
   *
   * @see sfParameterHolder
   */
  public function hasParameter(string $name): bool
  {
    return $this->parameterHolder->has($name);
  }

  /**
   * Sets the value for the given key.
   *
   * This is a shortcut for:
   *
   * <code>$this->getParameterHolder()->set()</code>
   *
   * @param string $name  The key name
   * @param mixed  $value The value
   *
   * @see sfParameterHolder
   */
  public function setParameter(string $name, $value): void
  {
    $this->parameterHolder->set($name, $value);
  }
}
