<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A View to render partials.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPartialView extends sfPHPView
{
  /**
   * Executes any presentation logic for this view.
   */
  public function execute()
  {
  }

  /**
   * @param array $partialVars
   */
  public function setPartialVars(array $partialVars)
  {
    $this->getAttributeHolder()->add($partialVars);
  }

  /**
   * Configures template for this view.
   */
  public function configure()
  {
    $this->setDecorator(false);
    $this->setTemplate($this->actionName.$this->getExtension());
    if ('global' == $this->moduleName)
    {
      $this->setDirectory($this->context->getConfiguration()->getDecoratorDir($this->getTemplate()));
    }
    else
    {
      $this->setDirectory($this->context->getConfiguration()->getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * Renders the presentation.
   *
   * @return string Current template content
   */
  public function render()
  {
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer = sfTimerManager::getTimer(sprintf('Partial "%s/%s"', $this->moduleName, $this->actionName));
    }

    try
    {
      // execute pre-render check
      $this->preRenderCheck();

      $this->getAttributeHolder()->set('sf_type', 'partial');

      // render template
      $retval = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());
    }
    catch (Exception $e)
    {
      throw $e;
    }

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
    {
      $timer->addTime();
    }

    return $retval;
  }
}
