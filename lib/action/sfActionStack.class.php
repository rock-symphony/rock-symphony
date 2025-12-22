<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfActionStack keeps a list of all requested actions and provides accessor
 * methods for retrieving individual entries.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfActionStack
{
  /** @var sfActionStackEntry[] */
  protected array $stack = [];

  /**
   * Adds an entry to the action stack.
   *
   * @param string   $moduleName      A module name
   * @param string   $actionName      An action name
   * @param sfAction $actionInstance  An sfAction implementation instance
   *
   * @return sfActionStackEntry sfActionStackEntry instance
   */
  public function addEntry(string $moduleName, string $actionName, sfAction $actionInstance): sfActionStackEntry
  {
    // create our action stack entry and add it to our stack
    $actionEntry = new sfActionStackEntry($moduleName, $actionName, $actionInstance);

    $this->stack[] = $actionEntry;

    return $actionEntry;
  }

  /**
   * Retrieves the entry at a specific index.
   *
   * @param int $index  An entry index
   *
   * @return sfActionStackEntry|null An action stack entry implementation.
   */
  public function getEntry(int $index): ?sfActionStackEntry
  {
    return $this->stack[$index] ?? null;
  }

  /**
   * Removes the entry at a specific index.
   *
   * @return sfActionStackEntry|null An action stack entry implementation.
   */
  public function popEntry(): ?sfActionStackEntry
  {
    return array_pop($this->stack);
  }

  /**
   * Retrieves the first entry.
   *
   * @return sfActionStackEntry|null An action stack entry implementation or null if there is no sfAction instance in the stack
   */
  public function getFirstEntry(): ?sfActionStackEntry
  {
    return $this->stack[0] ?? null;
  }

  /**
   * Retrieves the last entry.
   *
   * @return sfActionStackEntry|null An action stack entry implementation or null if there is no sfAction instance in the stack
   */
  public function getLastEntry(): ?sfActionStackEntry
  {
    $count  = count($this->stack);
    return $this->stack[$count - 1] ?? null;
  }

  /**
   * Retrieves the size of this stack.
   *
   * @return int The size of this stack.
   */
  public function getSize(): int
  {
    return count($this->stack);
  }
}
