<?php

/**
 * sfServiceParameterStringExpression represents a string expression that had sfServiceParameter in it.
 *
 * Example:
 * services.yml:
 *
 *    message: "Hello World from %sf_application% app."
 *
 * will be parsed to:
 *
 *    new sfServiceParameterStringExpression([
 *        "Hello world from ",
 *        sfServiceParameter("sf_application"),
 *        " app.",
 *    ])
 *
 * @package    symfony
 * @subpackage service
 */
class sfServiceParameterStringExpression
{
  /** @var array */
  private $parts;

  /**
   * @param array $parts An array of parts. Each part may be either string or sfServiceParameter instance.
   */
  public function __construct(array $parts)
  {
    $this->parts = $parts;
  }

  public function getParts()
  {
    return $this->parts;
  }

  /**
   * Stringify parameter referencing string expression back to "...%...%..." form.
   *
   * @return string
   */
  public function __toString()
  {
    return implode('', $this->parts);
  }
}
