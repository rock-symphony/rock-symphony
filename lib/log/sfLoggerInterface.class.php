<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfLoggerInterface is the interface all symfony loggers must implement.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
interface sfLoggerInterface
{
  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param int    $priority  Message priority
   * @return void
   */
  public function log(string $message, int $priority = null): void;

  /**
   * Logs an emerg message.
   *
   * @param string $message Message
   */
  public function emerg(string $message): void;

  /**
   * Logs an alert message.
   *
   * @param string $message Message
   */
  public function alert(string $message): void;

  /**
   * Logs a critical message.
   *
   * @param string $message Message
   */
  public function crit(string $message): void;

  /**
   * Logs an error message.
   *
   * @param string $message Message
   */
  public function err(string $message): void;

  /**
   * Logs a warning message.
   *
   * @param string $message Message
   */
  public function warning(string $message): void;

  /**
   * Logs a notice message.
   *
   * @param string $message Message
   */
  public function notice(string $message): void;

  /**
   * Logs an info message.
   *
   * @param string $message Message
   */
  public function info(string $message): void;

  /**
   * Logs a debug message.
   *
   * @param string $message Message
   */
  public function debug(string $message): void;
}
