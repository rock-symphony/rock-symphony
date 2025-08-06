<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfAggregateLogger logs messages through several loggers.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAggregateLogger extends sfLogger
{
  /** @var sfLoggerInterface[] */
  protected array $loggers = [];

  /**
   * Class constructor.
   *
   * Available options:
   *
   * - loggers: Logger objects that extends sfLogger.
   *
   * @param sfEventDispatcher $dispatcher  A sfEventDispatcher instance
   * @param array             $options     An array of options.
   *
   * @throws sfInitializationException If an error occurs while initializing this sfLogger.
   */
  public function __construct(sfEventDispatcher $dispatcher, array $options = [])
  {
    $this->dispatcher = $dispatcher;

    if (isset($options['loggers'])) {
      if ( ! is_array($options['loggers'])) {
        $options['loggers'] = [$options['loggers']];
      }

      $this->addLoggers($options['loggers']);
    }

    parent::__construct($dispatcher, $options);
  }

  /**
   * Retrieves current loggers.
   *
   * @return sfLoggerInterface[] List of loggers
   */
  public function getLoggers(): array
  {
    return $this->loggers;
  }

  /**
   * Adds an array of loggers.
   *
   * @param sfLoggerInterface[] $loggers  An array of Logger objects
   */
  public function addLoggers(array $loggers): void
  {
    foreach ($loggers as $logger) {
      $this->addLogger($logger);
    }
  }

  /**
   * Adds a logger.
   *
   * @param sfLoggerInterface $logger  The Logger object
   */
  public function addLogger(sfLoggerInterface $logger): void
  {
    $this->loggers[] = $logger;

    $this->dispatcher->disconnect('application.log', fn () => $logger->listenToLogEven());
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param int    $priority  Message priority
   */
  protected function doLog(string $message, int $priority): void
  {
    foreach ($this->loggers as $logger) {
      $logger->log($message, $priority);
    }
  }

  /**
   * Executes the shutdown method.
   */
  public function shutdown(): void
  {
    foreach ($this->loggers as $logger) {
      if ($logger instanceof sfLogger) {
        $logger->shutdown();
      }
    }

    $this->loggers = [];
  }
}
