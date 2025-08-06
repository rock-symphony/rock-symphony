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
abstract class sfResponse
{
  protected array $options = [];

  protected sfEventDispatcher $dispatcher;

  protected string $content = '';

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

    $this->options['logging'] = $this->options['logging'] ?? false;
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

  public function __sleep()
  {
    throw new LogicException(get_class($this) . ' is not serializable.');
  }
}
