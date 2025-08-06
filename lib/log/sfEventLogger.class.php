<?php

/**
 * sfEventLogger sends log messages to the event dispatcher to be processed
 * by registered loggers.
 *
 * @package    symfony
 * @subpackage log
 * @author     Jérôme Tamarelle <jtamarelle@groupe-exp.com>
 */
class sfEventLogger extends sfAbstractLogger implements sfLoggerInterface
{
  /** * @var \sfEventDispatcher */
  protected sfEventDispatcher $dispatcher;

  /** @var array<string,mixed> */
  protected array $options;

  /** * @var int */
  protected int $level;

  /**
   * {@inheritDoc}
   */
  public function __construct(sfEventDispatcher $dispatcher, array $options = [])
  {
    $this->dispatcher = $dispatcher;
    $this->options = $options;

    if (isset($this->options['level']))
    {
      $this->level = sfLogger::parseLogLevel($this->options['level']);
    }

    // Use the default "command.log" event if not overriden
    if (!isset($this->options['event_name'])) {
      $this->options['event_name'] = 'command.log';
    }
  }

  /**
   * @inheritDoc
   */
  public function log(string $message, int $priority = self::INFO): void
  {
    $this->dispatcher->notify(
      new sfEvent($this, $this->options['event_name'], [$message, 'priority' => $priority])
    );
  }
}
