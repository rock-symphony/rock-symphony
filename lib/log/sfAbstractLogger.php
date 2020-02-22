<?php

/**
 * sfAbstractLogger implements all log-level-shortcuts methods from sfLoggerInterface.
 *
 * @package    symfony
 * @subpackage log
 * @author     Ivan Voskoboinyk <ivan.voskoboinyk@gmail.com>
 */
abstract class sfAbstractLogger implements sfLoggerInterface
{
  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param int    $priority  Message priority
   * @return void
   */
  abstract public function log(string $message, int $priority = self::INFO): void;

  /**
   * Logs an emergency message.
   *
   * @param string $message Message
   */
  public function emerg(string $message): void
  {
    $this->log($message, sfLogger::EMERG);
  }

  /**
   * Logs an alert message.
   *
   * @param string $message Message
   */
  public function alert(string $message): void
  {
    $this->log($message, sfLogger::ALERT);
  }

  /**
   * Logs a critical message.
   *
   * @param string $message Message
   */
  public function crit(string $message): void
  {
    $this->log($message, sfLogger::CRIT);
  }

  /**
   * Logs an error message.
   *
   * @param string $message Message
   */
  public function err(string $message): void
  {
    $this->log($message, sfLogger::ERR);
  }

  /**
   * Logs a warning message.
   *
   * @param string $message Message
   */
  public function warning(string $message): void
  {
    $this->log($message, sfLogger::WARNING);
  }

  /**
   * Logs a notice message.
   *
   * @param string $message Message
   */
  public function notice(string $message): void
  {
    $this->log($message, sfLogger::NOTICE);
  }

  /**
   * Logs an info message.
   *
   * @param string $message Message
   */
  public function info(string $message): void
  {
    $this->log($message, sfLogger::INFO);
  }

  /**
   * Logs a debug message.
   *
   * @param string $message Message
   */
  public function debug(string $message): void
  {
    $this->log($message, sfLogger::DEBUG);
  }
}
