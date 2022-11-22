<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfViewParameterHolder stores all variables that will be available to the template.
 *
 * It can also escape variables with an escaping method.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfViewParameterHolder extends sfParameterHolder
{
  protected sfEventDispatcher $dispatcher;
  protected bool $escaping;
  protected string $escapingMethod;

  /**
   * Initializes this view parameter holder.
   *
   * @param  sfEventDispatcher $dispatcher  An sfEventDispatcher instance.
   * @param  array             $parameters  An associative array of initialization parameters.
   * @param  array             $options     An associative array of options.
   *
   * <b>Options:</b>
   *
   * # <b>escaping_strategy</b> - [off]              - The escaping strategy (on or off)
   * # <b>escaping_method</b>   - [ESC_SPECIALCHARS] - The escaping method (ESC_RAW, ESC_ENTITIES, ESC_JS, ESC_JS_NO_ENTITIES, or ESC_SPECIALCHARS)
   *
   * @throws sfInitializationException If an error occurs while initializing this view parameter holder.
   */
  public function __construct(sfEventDispatcher $dispatcher, array $parameters = [], array $options = [])
  {
    $escaping_strategy = $options['escaping_strategy'] ?? false;
    $escaping_method = $options['escaping_method'] ?? 'ESC_SPECIALCHARS';

    if (in_array($escaping_strategy, [true, 'on', 'true'], $strict = true)) {
      $this->setEscaping(true);
    } elseif (in_array($escaping_strategy, [false, 'off', 'false'], $strict = true)) {
      $this->setEscaping(false);
    } else {
      throw new InvalidArgumentException("Invalid `escaping_strategy` option value: `{$escaping_strategy}`.");
    }

    parent::__construct($parameters);

    $this->dispatcher = $dispatcher;

    $this->setEscaping(in_array($escaping_strategy, [true, 'on', 'true'], $strict = true));
    $this->setEscapingMethod($escaping_method);
  }

  /**
   * Returns true if the current object acts as an escaper.
   *
   * @return bool true if the current object acts as an escaper, false otherwise
   */
  public function isEscaped(): bool
  {
    return $this->getEscaping() === true;
  }

  /**
   * Returns an array representation of the view parameters.
   *
   * @return array An array of view parameters
   *
   * @throws InvalidArgumentException
   */
  public function toArray(): array
  {
    $event = $this->dispatcher->filter(new sfEvent($this, 'template.filter_parameters'), $this->getAll());
    $parameters = $event->getReturnValue();
    $attributes = array();

    if ($this->isEscaped())
    {
      $attributes['sf_data'] = sfOutputEscaper::escape($this->getEscapingMethod(), $parameters);
      foreach ($attributes['sf_data'] as $key => $value)
      {
        $attributes[$key] = $value;
      }
    }
    else
    {
      $attributes = $parameters;
      $attributes['sf_data'] = sfOutputEscaper::escape(ESC_RAW, $parameters);
    }

    return $attributes;
  }

  /**
   * @return bool true if escaping is enabled, false otherwise
   */
  public function getEscaping(): bool
  {
    return $this->escaping;
  }

  /**
   * Enable or disable the escape character strategy.
   *
   * @param bool $escaping
   */
  public function setEscaping(bool $escaping): void
  {
    $this->escaping = $escaping;
  }

  /**
   * Returns the name of the function that is to be used as the escaping method.
   *
   * If the escaping method is empty, then that is returned. The default value
   * specified by the sub-class will be used. If the method does not exist (in
   * the sense there is no define associated with the method), an exception is
   * thrown.
   *
   * @return string The escaping method as the name of the function to use
   *
   * @throws InvalidArgumentException If the method does not exist
   */
  public function getEscapingMethod(): string
  {
    return $this->escapingMethod ? constant($this->escapingMethod) : '';
  }

  /**
   * Sets the escaping method for the current view.
   *
   * @param string $method  Method for escaping
   */
  public function setEscapingMethod(string $method): void
  {
    if (empty($method))
    {
      $this->escapingMethod = $method;
      return;
    }

    if (!defined($method))
    {
      throw new InvalidArgumentException(sprintf('The escaping method "%s" is not available.', $method));
    }
    $this->escapingMethod = $method;
  }

  /**
   * Serializes the current instance.
   */
  public function serialize()
  {
    throw new LogicException(get_class($this) . ' is not serializable.');
  }
}
