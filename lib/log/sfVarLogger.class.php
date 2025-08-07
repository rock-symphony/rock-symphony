<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfVarLogger logs messages within its instance for later use.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfVarLogger extends sfLogger
{
  /** @var array[] */
  protected array $logs = [];

  /** @var bool */
  protected bool $xdebugLogging = false;

  /**
   * Class constructor.
   *
   * Available options:
   *
   * - xdebug_logging: Whether to add xdebug trace to the logs (false by default).
   *
   * @param sfEventDispatcher $dispatcher  A sfEventDispatcher instance
   * @param array             $options     An array of options.
   *
   * @throws sfInitializationException
   */
  public function __construct(sfEventDispatcher $dispatcher, array $options = [])
  {
    $this->xdebugLogging = $options['xdebug_logging'] ?? false;

    // disable xdebug when an HTTP debug session exists (crashes Apache, see #2438)
    if (isset($_GET['XDEBUG_SESSION_START']) || isset($_COOKIE['XDEBUG_SESSION'])) {
      $this->xdebugLogging = false;
    }

    parent::__construct($dispatcher, $options);
  }

  /**
   * Gets the logs.
   *
   * Each log entry has the following attributes:
   *
   *  * priority
   *  * priority_name
   *  * time
   *  * message
   *  * type
   *  * debug_backtrace
   *
   * @return array<{"priority":int,'priority_name':string,"time":int,"message":string,"type":string,"debug_backtrace":array}> An array of logs
   */
  public function getLogs(): array
  {
    return $this->logs;
  }

  /**
   * Returns all the types in the logs.
   *
   * @return string[] An array of types
   */
  public function getTypes(): array
  {
    $types = [];

    foreach ($this->logs as $log) {
      if ( ! in_array($log['type'], $types)) {
        $types[] = $log['type'];
      }
    }

    sort($types);

    return $types;
  }

  /**
   * Returns all the priorities in the logs.
   *
   * @return string[] An array of priorities
   */
  public function getPriorities(): array
  {
    $priorities = [];
    foreach ($this->logs as $log) {
      if ( ! in_array($log['priority'], $priorities)) {
        $priorities[] = $log['priority'];
      }
    }

    sort($priorities);

    return $priorities;
  }

  /**
   * Returns the highest priority in the logs.
   *
   * @return int The highest priority
   */
  public function getHighestPriority(): int
  {
    $priority = 1000;
    foreach ($this->logs as $log) {
      if ($log['priority'] < $priority) {
        $priority = $log['priority'];
      }
    }

    return $priority;
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param int    $priority  Message priority
   */
  protected function doLog(string $message, int $priority): void
  {
    // get log type in {}
    $type = 'sfOther';
    if (preg_match('/^\s*{([^}]+)}\s*(.+?)$/s', $message, $matches)) {
      $type    = $matches[1];
      $message = $matches[2];
    }

    $this->logs[] = [
      'priority'        => $priority,
      'priority_name'   => $this->getPriorityName($priority),
      'time'            => time(),
      'message'         => $message,
      'type'            => $type,
      'debug_backtrace' => $this->getDebugBacktrace(),
    ];
  }

  /**
   * Returns the debug stack.
   *
   * @return array
   *
   * @see debug_backtrace()
   */
  protected function getDebugBacktrace(): array
  {
    // if we have xdebug and dev has not disabled the feature, add some stack information
    if ( ! $this->xdebugLogging || ! function_exists('debug_backtrace')) {
      return [];
    }

    $traces = debug_backtrace();

    // remove sfLogger and sfEventDispatcher from the top of the trace
    foreach ($traces as $i => $trace) {
      $class = $trace['class'] ?? substr($file = basename($trace['file']), 0, strpos($file, '.'));

      if (
        ! class_exists($class)
        || (
          ! in_array($class, [sfLogger::class, sfEventDispatcher::class])
          && ! is_subclass_of($class, sfLogger::class)
          && ! is_subclass_of($class, sfEventDispatcher::class)
        )
      ) {
        return array_slice($traces, $i);
      }
    }

    return $traces;
  }
}
