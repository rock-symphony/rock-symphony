<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Abstract class for all tasks.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfTask
{
  /** @var string */
  protected string $namespace = '';

  /** @var string */
  protected string $name;

  /** @var string[] */
  protected array $aliases = [];

  /** @var string */
  protected string $briefDescription = '';

  /** @var string */
  protected string $detailedDescription = '';

  /** @var \sfCommandArgument[] */
  protected array $arguments = [];

  /** @var \sfCommandOption[] */
  protected array $options = [];

  /** @var \sfEventDispatcher */
  protected sfEventDispatcher $dispatcher;

  /** @var \sfFormatter */
  protected sfFormatter $formatter;

  /**
   * Constructor.
   *
   * @param sfEventDispatcher $dispatcher  An sfEventDispatcher instance
   * @param sfFormatter       $formatter   An sfFormatter instance
   */
  public function __construct(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->dispatcher = $dispatcher;
    $this->formatter  = $formatter;

    $this->configure();
  }

  /**
   * Configures the current task.
   */
  protected function configure(): void
  {
    // Point of extension for sub-classes
  }

  /**
   * Returns the formatter instance.
   *
   * @return sfFormatter The formatter instance
   */
  public function getFormatter(): sfFormatter
  {
    return $this->formatter;
  }

  /**
   * Sets the formatter instance.
   *
   * @param sfFormatter $formatter  The formatter instance
   */
  public function setFormatter(sfFormatter $formatter): void
  {
    $this->formatter = $formatter;
  }

  /**
   * Runs the task from the CLI.
   *
   * @param sfCommandManager $commandManager  An sfCommandManager instance
   * @param mixed            $options         The command line options
   *
   * @return int 0 if everything went fine, or an error code
   */
  public function runFromCLI(sfCommandManager $commandManager, array | string | null $options = null)
  {
    $commandManager->getArgumentSet()->addArguments($this->getArguments());
    $commandManager->getOptionSet()->addOptions($this->getOptions());

    return $this->doRun($commandManager, $options);
  }

  /**
   * Runs the task.
   *
   * @param array|string $arguments  An array of arguments or a string representing the CLI arguments and options
   * @param array        $options    An array of options
   *
   * @return int 0 if everything went fine, or an error code
   */
  public function run(array | string $arguments = [], array $options = []): int
  {
    $commandManager = new sfCommandManager(
      new sfCommandArgumentSet($this->getArguments()),
      new sfCommandOptionSet($this->getOptions()),
    );

    if (is_array($arguments) && is_string(key($arguments))) {
      // index arguments by name for ordering and reference

      /** @var array<string,sfCommandArgument> $indexArguments */
      $indexArguments = [];
      foreach ($this->arguments as $argument) {
        $indexArguments[$argument->getName()] = $argument;
      }

      foreach ($arguments as $name => $value) {
        if (false !== $pos = array_search($name, array_keys($indexArguments))) {
          if ($indexArguments[$name]->isArray()) {
            $value           = implode(' ', (array)$value);
            $arguments[$pos] = isset($arguments[$pos]) ? $arguments[$pos] . ' ' . $value : $value;
          } else {
            $arguments[$pos] = $value;
          }

          unset($arguments[$name]);
        }
      }

      ksort($arguments);
    }

    // index options by name for reference
    /** @var array<string,sfCommandOption> $indexedOptions */
    $indexedOptions = [];
    foreach ($this->options as $option) {
      $indexedOptions[$option->getName()] = $option;
    }

    foreach ($options as $name => $value) {
      if (is_string($name)) {
        if (false === $value || null === $value || (isset($indexedOptions[$name]) && $indexedOptions[$name]->isArray() && ! $value)) {
          unset($options[$name]);
          continue;
        }

        // convert associative array
        $value = true === $value ? $name : sprintf(
          '%s=%s',
          $name,
          isset($indexedOptions[$name]) && $indexedOptions[$name]->isArray() ? implode(' --' . $name . '=', (array)$value) : $value
        );
      }

      // add -- before each option if needed
      if (strpos($value, '--') !== 0) {
        $value = '--' . $value;
      }

      $options[] = $value;
      unset($options[$name]);
    }

    return $this->doRun($commandManager, is_string($arguments) ? $arguments : implode(' ', array_merge($arguments, $options)));
  }

  /**
   * Returns the argument objects.
   *
   * @return sfCommandArgument[] An array of sfCommandArgument objects.
   */
  public function getArguments(): array
  {
    return $this->arguments;
  }

  /**
   * Adds an array of argument objects.
   *
   * @param sfCommandArgument[] $arguments  An array of arguments
   */
  public function addArguments(array $arguments): void
  {
    $this->arguments = array_merge($this->arguments, $arguments);
  }

  /**
   * Add an argument.
   *
   * Construct an sfCommandArgument instance and add it to the arguments list.
   *
   * @see sfCommandArgument::__construct()
   * @param string $name
   * @param int    $mode
   * @param string $help
   * @param mixed  $default
   */
  public function addArgument(string $name, int | null $mode = null, string $help = '', mixed $default = null): void
  {
    $this->arguments[] = new sfCommandArgument($name, $mode, $help, $default);
  }

  /**
   * Returns the options objects.
   *
   * @return sfCommandOption[] An array of sfCommandOption objects.
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * Adds an array of option objects.
   *
   * @param sfCommandOption[] $options  An array of options
   */
  public function addOptions(array $options): void
  {
    $this->options = array_merge($this->options, $options);
  }

  /**
   * Add an option.
   *
   * Construct an sfCommandOption instance and add it to the options list.
   *
   * @see sfCommandOption::__construct()
   *
   * @param string      $name
   * @param string|null $shortcut
   * @param int|null    $mode
   * @param string      $help
   * @param mixed       $default
   */
  public function addOption(string $name, string | null $shortcut = null, int | null $mode = null, string $help = '', mixed $default = null): void
  {
    $this->options[] = new sfCommandOption($name, $shortcut, $mode, $help, $default);
  }

  /**
   * Returns the task namespace.
   *
   * @return string The task namespace
   */
  public function getNamespace(): string
  {
    return $this->namespace;
  }

  /**
   * Returns the task name
   *
   * @return string The task name
   */
  public function getName(): string
  {
    if (! empty($this->name)) {
      return $this->name;
    }

    $name = get_class($this);

    if ('sf' == substr($name, 0, 2)) {
      $name = substr($name, 2);
    }

    if ('Task' == substr($name, -4)) {
      $name = substr($name, 0, -4);
    }

    return str_replace('_', '-', sfInflector::underscore($name));
  }

  /**
   * Returns the fully qualified task name.
   *
   * @return string The fully qualified task name
   */
  final function getFullName(): string
  {
    return $this->getNamespace() ? $this->getNamespace() . ':' . $this->getName() : $this->getName();
  }

  /**
   * Returns the brief description for the task.
   *
   * @return string The brief description for the task
   */
  public function getBriefDescription(): string
  {
    return $this->briefDescription;
  }

  /**
   * Returns the detailed description for the task.
   *
   * It also formats special string like [...|COMMENT]
   * depending on the current formatter.
   *
   * @return string The detailed description for the task
   */
  public function getDetailedDescription(): string
  {
    $formatter = $this->getFormatter();

    return preg_replace_callback('/\[(.+?)\|(\w+)\]/s', function ($match) use ($formatter) {
      return $formatter->format($match['1'], $match['2']);
    }, $this->detailedDescription);
  }

  /**
   * Returns the aliases for the task.
   *
   * @return string[] An array of aliases for the task
   */
  public function getAliases(): array
  {
    return $this->aliases;
  }

  /**
   * Returns the synopsis for the task.
   *
   * @return string The synopsis
   */
  public function getSynopsis(): string
  {
    $options = [];

    foreach ($this->getOptions() as $option) {
      $shortcut  = $option->getShortcut() ? sprintf('-%s|', $option->getShortcut()) : '';
      $options[] = sprintf(
        '[' . ($option->isParameterRequired() ? '%s--%s="..."' : ($option->isParameterOptional() ? '%s--%s[="..."]' : '%s--%s')) . ']',
        $shortcut,
        $option->getName()
      );
    }

    $arguments = [];
    foreach ($this->getArguments() as $argument) {
      $arguments[] = sprintf($argument->isRequired() ? '%s' : '[%s]', $argument->getName() . ($argument->isArray() ? '1' : ''));

      if ($argument->isArray()) {
        $arguments[] = sprintf('... [%sN]', $argument->getName());
      }
    }

    return sprintf('%%s %s %s %s', $this->getFullName(), implode(' ', $options), implode(' ', $arguments));
  }

  protected function process(sfCommandManager $commandManager, array | string | null $options): void
  {
    $commandManager->process($options);
    if ( ! $commandManager->isValid()) {
      throw new sfCommandArgumentsException(sprintf("The execution of task \"%s\" failed.\n- %s", $this->getFullName(), implode("\n- ", $commandManager->getErrors())));
    }
  }

  protected function doRun(sfCommandManager $commandManager, array | string | null $options): int
  {
    $event   = $this->dispatcher->filter(
      new sfEvent($this, 'command.filter_options', ['command_manager' => $commandManager]),
      $options,
    );
    $options = $event->getReturnValue();

    $this->process($commandManager, $options);

    $event = new sfEvent($this, 'command.pre_command', ['arguments' => $commandManager->getArgumentValues(), 'options' => $commandManager->getOptionValues()]);
    $this->dispatcher->notifyUntil($event);
    if ($event->isProcessed()) {
      return $event->getReturnValue();
    }

    $ret = $this->execute($commandManager->getArgumentValues(), $commandManager->getOptionValues());

    $this->dispatcher->notify(new sfEvent($this, 'command.post_command'));

    return $ret;
  }

  /**
   * Logs a message.
   *
   * @param string[]|string $messages  The message as an array of lines of a single string
   */
  public function log(array | string $messages): void
  {
    $messages = is_array($messages) ? $messages : [$messages];

    $this->dispatcher->notify(new sfEvent($this, 'command.log', $messages));
  }

  /**
   * Logs a message in a section.
   *
   * @param string   $section  The section name
   * @param string   $message  The message
   * @param int|null $size     The maximum size of a line
   * @param string   $style    The color scheme to apply to the section string (INFO, ERROR, or COMMAND)
   */
  public function logSection(string $section, string $message, int | null $size = null, string $style = 'INFO'): void
  {
    $this->dispatcher->notify(new sfEvent($this, 'command.log', [$this->formatter->formatSection($section, $message, $size, $style)]));
  }

  /**
   * Logs a message as a block of text.
   *
   * @param string[]|string $messages  The message to display in the block
   * @param string          $style     The style to use
   */
  public function logBlock(array | string $messages, string $style): void
  {
    $messages = is_array($messages) ? $messages : [$messages];

    $style = str_replace('_LARGE', '', $style, $count);
    $large = (boolean)$count;

    $len   = 0;
    $lines = [];
    foreach ($messages as $message) {
      $lines[] = sprintf($large ? '  %s  ' : ' %s ', $message);
      $len     = max($this->strlen($message) + ($large ? 4 : 2), $len);
    }

    $messages = $large ? [str_repeat(' ', $len)] : [];
    foreach ($lines as $line) {
      $messages[] = $line . str_repeat(' ', $len - $this->strlen($line));
    }
    if ($large) {
      $messages[] = str_repeat(' ', $len);
    }

    foreach ($messages as $message) {
      $this->log($this->formatter->format($message, $style));
    }
  }

  /**
   * Asks the user a question.
   *
   * @param string[]|string   $question  The question to ask
   * @param string|null|false $style     The style to use (QUESTION by default)
   * @param string            $default   The default answer if none is given by the user
   *
   * @return string      The user answer
   */
  public function ask(array | string $question, string | false | null $style = 'QUESTION', mixed $default = null): string
  {
    if (false === $style) {
      $this->log($question);
    } else {
      $this->logBlock($question, null === $style ? 'QUESTION' : $style);
    }

    $ret = trim(fgets(STDIN));

    return $ret ?: $default;
  }

  /**
   * Asks the user a confirmation.
   *
   * The question will be asked until the user answer by nothing, yes, or no.
   *
   * @param string[]|string $question  The question to ask
   * @param string          $style     The style to use (QUESTION by default)
   * @param Boolean         $default   The default answer if the user enters nothing
   *
   * @return Boolean     true if the user has confirmed, false otherwise
   */
  public function askConfirmation(array | string $question, string $style = 'QUESTION', mixed $default = true): bool
  {
    do {
      $answer = $this->ask($question, $style);
    } while ($answer && ! in_array(strtolower($answer[0]), ['y', 'n']));

    if ($default === false) {
      return $answer && 'y' == strtolower($answer[0]);
    }

    return ! $answer || 'y' == strtolower($answer[0]);
  }

  /**
   * Asks for a value and validates the response.
   *
   * Available options:
   *
   *  * value:    A value to try against the validator before asking the user
   *  * attempts: Max number of times to ask before giving up (false by default, which means infinite)
   *  * style:    Style for question output (QUESTION by default)
   *
   * @param string[]|string                                          $question
   * @param sfValidatorBase                                          $validator
   * @param array{'value':mixed,'attempts':int|false,'style':string} $options
   *
   * @return mixed
   *
   * @throws sfValidatorError
   */
  public function askAndValidate(array | string $question, sfValidatorBase $validator, array $options = []): mixed
  {
    $question = is_array($question) ? $question : [$question];

    $options = array_merge([
      'value'    => null,
      'attempts' => false,
      'style'    => 'QUESTION',
    ], $options);

    // does the provided value passes the validator?
    if ($options['value']) {
      try {
        return $validator->clean($options['value']);
      } catch (sfValidatorError $error) {
        // ignore
      }
    }

    // no, ask the user for a valid user
    /** @var sfValidatorError|null $error */
    $error = null;
    while (false === $options['attempts'] || $options['attempts']--) {
      if (null !== $error) {
        $this->logBlock($error->getMessage(), 'ERROR');
      }

      $value = $this->ask($question, $options['style'], null);

      try {
        return $validator->clean($value);
      } catch (sfValidatorError $error) {
      }
    }

    throw $error;
  }

  /**
   * Returns an XML representation of a task.
   *
   * @return string An XML string representing the task
   */
  public function asXml(): string
  {
    $dom               = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $dom->appendChild($taskXML = $dom->createElement('task'));
    $taskXML->setAttribute('id', $this->getFullName());
    $taskXML->setAttribute('namespace', $this->getNamespace() ?: '_global');
    $taskXML->setAttribute('name', $this->getName());

    $taskXML->appendChild($usageXML = $dom->createElement('usage'));
    $usageXML->appendChild($dom->createTextNode(sprintf($this->getSynopsis(), '')));

    $taskXML->appendChild($descriptionXML = $dom->createElement('description'));
    $descriptionXML->appendChild($dom->createTextNode(implode("\n ", explode("\n", $this->getBriefDescription()))));

    $taskXML->appendChild($helpXML = $dom->createElement('help'));
    $help = $this->detailedDescription;
    $help = str_replace(['|COMMENT', '|INFO'], ['|strong', '|em'], $help);
    $help = preg_replace('/\[(.+?)\|(\w+)\]/s', '<$2>$1</$2>', $help);
    $helpXML->appendChild($dom->createTextNode(implode("\n ", explode("\n", $help))));

    $taskXML->appendChild($aliasesXML = $dom->createElement('aliases'));
    foreach ($this->getAliases() as $alias) {
      $aliasesXML->appendChild($aliasXML = $dom->createElement('alias'));
      $aliasXML->appendChild($dom->createTextNode($alias));
    }

    $taskXML->appendChild($argumentsXML = $dom->createElement('arguments'));
    foreach ($this->getArguments() as $argument) {
      $argumentsXML->appendChild($argumentXML = $dom->createElement('argument'));
      $argumentXML->setAttribute('name', $argument->getName());
      $argumentXML->setAttribute('is_required', $argument->isRequired() ? 1 : 0);
      $argumentXML->setAttribute('is_array', $argument->isArray() ? 1 : 0);
      $argumentXML->appendChild($helpXML = $dom->createElement('description'));
      $helpXML->appendChild($dom->createTextNode($argument->getHelp()));

      $argumentXML->appendChild($defaultsXML = $dom->createElement('defaults'));
      $defaults = is_array($argument->getDefault()) ? $argument->getDefault() : ($argument->getDefault() ? [$argument->getDefault()] : []);
      foreach ($defaults as $default) {
        $defaultsXML->appendChild($defaultXML = $dom->createElement('default'));
        $defaultXML->appendChild($dom->createTextNode($default));
      }
    }

    $taskXML->appendChild($optionsXML = $dom->createElement('options'));
    foreach ($this->getOptions() as $option) {
      $optionsXML->appendChild($optionXML = $dom->createElement('option'));
      $optionXML->setAttribute('name', '--' . $option->getName());
      $optionXML->setAttribute('shortcut', $option->getShortcut() ? '-' . $option->getShortcut() : '');
      $optionXML->setAttribute('accept_parameter', $option->acceptParameter() ? 1 : 0);
      $optionXML->setAttribute('is_parameter_required', $option->isParameterRequired() ? 1 : 0);
      $optionXML->setAttribute('is_multiple', $option->isArray() ? 1 : 0);
      $optionXML->appendChild($helpXML = $dom->createElement('description'));
      $helpXML->appendChild($dom->createTextNode($option->getHelp()));

      if ($option->acceptParameter()) {
        $optionXML->appendChild($defaultsXML = $dom->createElement('defaults'));
        $defaults = is_array($option->getDefault()) ? $option->getDefault() : ($option->getDefault() ? [$option->getDefault()] : []);
        foreach ($defaults as $default) {
          $defaultsXML->appendChild($defaultXML = $dom->createElement('default'));
          $defaultXML->appendChild($dom->createTextNode($default));
        }
      }
    }

    return $dom->saveXML();
  }

  /**
   * Executes the current task.
   *
   * @param array $arguments  An array of arguments
   * @param array $options    An array of options
   *
   * @return int 0 if everything went fine, or an error code
   */
  abstract protected function execute(array $arguments = [], array $options = []): int;

  protected function strlen(string $string): int
  {
    if ( ! function_exists('mb_strlen')) {
      return strlen($string);
    }

    $encoding = mb_detect_encoding($string);

    if ($encoding === false) {
      return strlen($string);
    }

    return mb_strlen($string, $encoding);
  }
}
