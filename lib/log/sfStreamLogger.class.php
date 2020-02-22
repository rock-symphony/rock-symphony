<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfStreamLogger logs messages to a PHP stream.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfStreamLogger extends sfLogger
{
  /** @var resource */
  protected $stream;

  /**
   * Class constructor.
   *
   * Available options:
   *
   * - stream: A PHP stream
   *
   * @param sfEventDispatcher $dispatcher A sfEventDispatcher instance
   * @param array             $options    An array of options.
   *
   * @return void
   *
   * @throws sfInitializationException If an error occurs while initializing this sfLogger.
   * @throws sfConfigurationException If invalid configuration provided.
   */
  public function __construct(sfEventDispatcher $dispatcher, array $options = [])
  {
    if (!isset($options['stream']))
    {
      throw new sfConfigurationException('You must provide a "stream" option for this logger.');
    }

    if (!is_resource($options['stream']) || 'stream' !== get_resource_type($options['stream']))
    {
      throw new sfConfigurationException('The provided "stream" option is not a stream.');
    }

    $this->stream = $options['stream'];

    parent::__construct($dispatcher, $options);
  }

  /**
   * Sets the PHP stream to use for this logger.
   *
   * @param resource $stream A php stream
   */
  public function setStream($stream)
  {
    $this->stream = $stream;
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param int    $priority  Message priority
   */
  protected function doLog(string $message, int $priority): void
  {
    fwrite($this->stream, $message.PHP_EOL);
    flush();
  }
}
