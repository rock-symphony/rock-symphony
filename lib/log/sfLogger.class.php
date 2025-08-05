<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLogger is the abstract class for all logging classes.
 *
 * This level list is ordered by highest priority (self::EMERG) to lowest priority (self::DEBUG):
 * - EMERG:   System is unusable
 * - ALERT:   Immediate action required
 * - CRIT:    Critical conditions
 * - ERR:     Error conditions
 * - WARNING: Warning conditions
 * - NOTICE:  Normal but significant
 * - INFO:    Informational
 * - DEBUG:   Debug-level messages
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfLogger extends sfAbstractLogger implements sfLoggerInterface
{
  private const LEVELS = [
    self::EMERG   => 'emerg',
    self::ALERT   => 'alert',
    self::CRIT    => 'crit',
    self::ERR     => 'err',
    self::WARNING => 'warning',
    self::NOTICE  => 'notice',
    self::INFO    => 'info',
    self::DEBUG   => 'debug',
  ];

  /** @var sfEventDispatcher */
  protected $dispatcher;
  /** @var array */
  protected $options;
  /** @var int */
  protected $level = self::INFO;

  /**
   * Class constructor.
   *
   * Available options:
   *
   * - level: The log level.
   *
   * @param  sfEventDispatcher $dispatcher  A sfEventDispatcher instance
   * @param  array             $options     An array of options.
   *
   * @throws sfInitializationException If an error occurs while initializing this sfLogger.
   */
  public function __construct(sfEventDispatcher $dispatcher, array $options = [])
  {
    $this->dispatcher = $dispatcher;
    $this->options = $options;

    if (isset($this->options['level']))
    {
      try {
        $this->level = self::parseLogLevel($this->options['level']);
      } catch (sfException $exception) {
        throw new sfInitializationException("Invalid `level` option: {$exception->getMessage()}", 0, $exception);
      }
    }

    $dispatcher->connect('application.log', array($this, 'listenToLogEvent'));

    if (!isset($options['auto_shutdown']) || $options['auto_shutdown'])
    {
      register_shutdown_function(array($this, 'shutdown'));
    }
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param int    $priority  Message priority
   * @return void|bool
   */
  public function log(string $message, int $priority = self::INFO): void
  {
    if ($this->level >= $priority)
    {
      $this->doLog($message, $priority);
    }
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param int    $priority  Message priority
   */
  abstract protected function doLog(string $message, int $priority): void;

  /**
   * Listens to application.log events.
   *
   * @param sfEvent $event An sfEvent instance
   */
  public function listenToLogEvent(sfEvent $event)
  {
    $priority = $event->getParameter('priority') ??  self::INFO;

    $subject  = $event->getSubject();
    $subject  = is_object($subject) ? get_class($subject) : (is_string($subject) ? $subject : 'main');
    foreach ($event->getParameters() as $key => $message)
    {
      if ('priority' === $key)
      {
        continue;
      }

      $this->log(sprintf('{%s} %s', $subject, $message), $priority);
    }
  }

  /**
   * Executes the shutdown procedure.
   *
   * Cleans up the current logger instance.
   */
  public function shutdown()
  {
  }

  /**
   * Coverts a given priority name, or level, to a known log level.
   *
   * @param  int|string $priority Priority name or log level
   *
   * @return int        The priority constant value
   *
   * @throws sfException if the priority level does not exist
   */
  static public function parseLogLevel($priority): int
  {
    foreach (self::LEVELS as $level => $name) {
      if ($level === $priority || $name === $priority) {
        return $level;
      }
    }
    throw new sfException(sprintf('The priority level "%s" does not exist.', $priority));
  }

  /**
   * Returns the priority name given a priority class constant
   *
   * @param  integer $priority A priority class constant
   *
   * @return string  The priority name
   *
   * @throws sfException if the priority level does not exist
   */
  static public function getPriorityName($priority)
  {
    if (!isset(self::LEVELS[$priority]))
    {
      throw new sfException(sprintf('The priority level "%s" does not exist.', $priority));
    }

    return self::LEVELS[$priority];
  }
}
