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
 * sfActionStackEntry represents information relating to a single sfAction request during a single HTTP request.
 *
 * @package    symfony
 * @subpackage action
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id$
 */
class sfActionStackEntry
{
  /** @var \sfAction */
  protected sfAction $actionInstance;

  /** @var string */
  protected string $actionName;

  /** @var string */
  protected string $moduleName;

  protected string | null $presentation = null;

  /**
   * Class constructor.
   *
   * @param string   $moduleName     A module name
   * @param string   $actionName     An action name
   * @param sfAction $actionInstance An sfAction implementation instance
   */
  public function __construct(string $moduleName, string $actionName, sfAction $actionInstance)
  {
    $this->actionName     = $actionName;
    $this->actionInstance = $actionInstance;
    $this->moduleName     = $moduleName;
  }

  /**
   * Retrieves this entry's action name.
   *
   * @return string An action name
   */
  public function getActionName(): string
  {
    return $this->actionName;
  }

  /**
   * Retrieves this entry's action instance.
   *
   * @return sfAction An sfAction implementation instance
   */
  public function getActionInstance(): sfAction
  {
    return $this->actionInstance;
  }

  /**
   * Retrieves this entry's module name.
   *
   * @return string A module name
   */
  public function getModuleName(): string
  {
    return $this->moduleName;
  }

  /**
   * Retrieves this entry's rendered view presentation.
   *
   * This will only exist if the view has processed and the render mode is set to sfView::RENDER_VAR.
   *
   * @return string Rendered view presentation
   */
  public function getPresentation(): string
  {
    return $this->presentation;
  }

  /**
   * Sets the rendered presentation for this action.
   *
   * @param string $presentation A rendered presentation.
   */
  public function setPresentation(string $presentation): void
  {
    $this->presentation = $presentation;
  }
}
