<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormatter provides methods to format text to be displayed on a console.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFormatter
{
  private const DEFAULT_SIZE = 78;

  protected int | null $size = null;

  function __construct(int | null $maxLineSize = null)
  {
    if (null === $maxLineSize) {
      if (function_exists('shell_exec')) {
        // this is tricky because "tput cols 2>&1" is not accurate
        $maxLineSize = ctype_digit(trim(shell_exec('tput cols 2>&1'))) ? (integer)shell_exec('tput cols') : 78;
      } else {
        $maxLineSize = self::DEFAULT_SIZE;
      }
    }

    $this->size = $maxLineSize;
  }

  /**
   * Sets a new style.
   *
   * @param string $name     The style name
   * @param array  $options  An array of options
   */
  public function setStyle(string $name, array $options = []): void
  {
  }

  /**
   * Formats a text according to the given parameters.
   *
   * @param string         $text        The test to style
   * @param string | array $parameters  An array of parameters
   *
   * @return string The formatted text
   */
  public function format(string $text = '', string | array $parameters = []): string
  {
    return $text;
  }

  /**
   * Formats a message within a section.
   *
   * @param string $section  The section name
   * @param string $text     The text message
   * @param int    $size     The maximum size allowed for a line
   *
   * @return string
   */
  public function formatSection(string $section, string $text, int | null $size = null): string
  {
    $size = $size ?: $this->size;

    $section = sprintf('>> %-9s ', $section);

    return $section . $this->excerpt($text, $size - strlen($section));
  }

  /**
   * Truncates a line.
   *
   * @param string $text  The text
   * @param int    $size  The maximum size of the returned string
   *
   * @return string The truncated string
   */
  public function excerpt(string $text, int | null $size = null): string
  {
    if ( ! $size) {
      $size = $this->size;
    }

    if (strlen($text) < $size) {
      return $text;
    }

    $subsize = floor(($size - 3) / 2);

    return substr($text, 0, $subsize) . '...' . substr($text, -$subsize);
  }

  /**
   * Sets the maximum line size.
   *
   * @param int $size  The maximum line size for a message
   */
  public function setMaxLineSize(int $size): void
  {
    $this->size = $size;
  }
}
