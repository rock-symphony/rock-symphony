<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(__DIR__.'/../../bootstrap/unit.php');

$t = new lime_test(133);

class myLogger extends sfLogger
{
  public $log = '';

  protected function doLog(string $message, int $priority): void
  {
    $this->log .= $message;
  }

  public function getLogLevel()
  {
    return $this->level;
  }

  public function getOptions()
  {
    return $this->options;
  }
}

class notaLogger
{
}

$dispatcher = new sfEventDispatcher();
$logger = new myLogger($dispatcher, array('log_dir_name' => '/tmp', 'level' => sfLogger::ERR));

$options = $logger->getOptions();
$t->is($options['log_dir_name'], '/tmp', '->getOptions() returns the options for the logger instance');
$t->is($logger->getLogLevel(), sfLogger::ERR, '->__construct() takes an array of options as its second argument');

// ::getPriorityName()
$t->diag('::getPriorityName()');
$t->is(sfLogger::getPriorityName(sfLogger::INFO), 'info', '::getPriorityName() returns the name of a priority class constant');
try
{
  sfLogger::getPriorityName(100);
  $t->fail('::getPriorityName() throws an sfException if the priority constant does not exist');
}
catch (sfException $e)
{
  $t->pass('::getPriorityName() throws an sfException if the priority constant does not exist');
}

$logger = new myLogger($dispatcher, ['level' => sfLogger::DEBUG]);

// ->log()
$t->diag('->log()');
$logger->log('message');
$t->is($logger->log, 'message', '->log() logs a message');

// log level
$t->diag('log levels');
foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level)
{
  $levelConstant = 'sfLogger::'.strtoupper($level);

  foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel)
  {
    $logLevelConstant = 'sfLogger::'.strtoupper($logLevel);

    $logger = new myLogger($dispatcher, ['level' => $logLevel]);

    $logger->log('foo', constant($levelConstant));

    $t->is($logger->log, constant($logLevelConstant) >= constant($levelConstant) ? 'foo' : '', sprintf('->log() only logs if the level is >= to the defined log level (%s >= %s)', $logLevelConstant, $levelConstant));
  }
}

// shortcuts
$t->diag('log shortcuts');
foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level)
{
  $levelConstant = 'sfLogger::'.strtoupper($level);

  foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel)
  {
    $logger  = new myLogger($dispatcher, ['level' => $logLevel]);

    $log = uniqid();

    $logger->log($log, constant($levelConstant));
    $log1 = $logger->log;

    $logger->log = '';
    $logger->$level($log);
    $log2 = $logger->log;

    $t->is($log1, $log2, sprintf('->%s($msg) is a shortcut for ->log($msg, %s)', $level, $levelConstant));
  }
}
