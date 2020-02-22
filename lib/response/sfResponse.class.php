<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfResponse provides methods for manipulating client response information such
 * as headers, cookies and content.
 *
 * @package    symfony
 * @subpackage response
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfResponse implements Serializable
{
  /** @var array */
  protected $options = [];
  /** @var sfEventDispatcher */
  protected $dispatcher;
  /** @var string */
  protected $content = '';

  /**
   * Class constructor.
   *
   * Available options:
   *
   *  * logging: Whether to enable logging or not (false by default)
   *
   * @param  sfEventDispatcher  $dispatcher  An sfEventDispatcher instance
   * @param  array              $options     An array of options
   */
  public function __construct(sfEventDispatcher $dispatcher, array $options = [])
  {
    $this->dispatcher = $dispatcher;
    $this->options = $options;

    if (!isset($this->options['logging']))
    {
      $this->options['logging'] = false;
    }
  }

  /**
   * Sets the event dispatcher.
   *
   * @param sfEventDispatcher $dispatcher  An sfEventDispatcher instance
   */
  public function setEventDispatcher(sfEventDispatcher $dispatcher): void
  {
    $this->dispatcher = $dispatcher;
  }

  /**
   * Sets the response content
   *
   * @param string $content
   */
  public function setContent(string $content): void
  {
    $this->content = $content;
  }

  /**
   * Gets the current response content
   *
   * @return string Content
   */
  public function getContent(): string
  {
    return $this->content;
  }

  /**
   * Outputs the response content
   */
  public function sendContent(): void
  {
    $event = $this->dispatcher->filter(new sfEvent($this, 'response.filter_content'), $this->getContent());
    $content = $event->getReturnValue();

    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Send content (%s o)', strlen($content)))));
    }

    echo $content;
  }

  /**
   * Sends the content.
   */
  public function send(): void
  {
    $this->sendContent();
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
   * Serializes the current instance.
   */
  public function serialize()
  {
    return serialize($this->content);
  }

  /**
   * Unserializes a sfResponse instance.
   *
   * You need to inject a dispatcher after unserializing a sfResponse instance.
   *
   * @param string $serialized  A serialized sfResponse instance
   *
   */
  public function unserialize($serialized)
  {
    $this->content = unserialize($serialized);
  }
}
