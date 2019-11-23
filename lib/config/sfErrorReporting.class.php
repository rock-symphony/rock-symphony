<?php

/**
 * sfErrorReporting parses error reporting config entry value.
 *
 * This is needed, as the new symfony/yaml component does not perform in-yaml PHP code execution.
 * So we need to replace it with another human-readable way to define "error_reporting".
 *
 * This class converts human-readable error_reporting value to PHP bitmask int.
 * And sets.
 *
 * Support syntax:
 *
 * 1. int (bitmask):
 *    error_reporting: 2056
 *
 * 2. string expression:
 *     error_reporting: (E_ALL | E_STRICT) ^ E_DEPRECATED
 *
 * @internal Do not use this class in your project. It's internal and can be removed/modified at any time.
 *
 * @param int|string|array $errorReporting config entry value
 * @return void
 */
class sfErrorReporting
{
  /**
   * @param null|string|int $error_reporting_config
   * @return void
   */
  public function set($error_reporting_config): void
  {
    $error_reporting = $this->parse($error_reporting_config);
    if ($error_reporting !== null) {
      error_reporting($error_reporting);
    }
  }

  /**
   * @param array|null|string|int $error_reporting_config
   * @return int|null Error reporting level as INTEGER, or NULL to keep current level
   */
  public function parse($error_reporting_config): ?int
  {
    if (is_null($error_reporting_config) || $error_reporting_config === '') {
      // NULL === Keep current error reporting
      return null;
    }

    if (is_int($error_reporting_config)) {
      // No need to do conversions
      return $error_reporting_config;
    }

    if (is_string($error_reporting_config)) {
      // Leave it to parseArray()
      return $this->parseString($error_reporting_config);
    }

    throw new InvalidArgumentException('Invalid value defined for "error_reporting" config entry.');
  }

  /**
   * @param string $error_reporting_config
   * @return int
   */
  private function parseString(string $error_reporting_config): int
  {
    $level = 'E_\w+';
    $number = "\d+";
    $operator = "[~&|^]";
    $parenthesis = "[()]";
    $space = "\s";

    $regex = "/^ 
        (?:{$operator}|{$parenthesis}|{$space}|{$level}|{$number})+
        $ /x";

    if (! preg_match($regex, $error_reporting_config)) {
      throw new InvalidArgumentException('Error reporting level configuration does not match format expectations');
    }

    return eval("return {$error_reporting_config};");
  }
}
