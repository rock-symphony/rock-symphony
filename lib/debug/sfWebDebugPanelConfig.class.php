<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Yaml\Yaml;

/**
 * sfWebDebugPanelConfig adds a panel to the web debug toolbar with the current configuration.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebugPanelConfig extends sfWebDebugPanel
{
  public function getTitle(): string
  {
    return '<img src="'.$this->webDebug->getOption('image_root_path').'/config.png" alt="Config" /> config';
  }

  public function getPanelTitle(): string
  {
    return 'Configuration';
  }

  public function getPanelContent(): string
  {
    $config = array(
      'debug'        => sfConfig::get('sf_debug')           ? 'on' : 'off',
      'xdebug'       => extension_loaded('xdebug')          ? 'on' : 'off',
      'logging'      => sfConfig::get('sf_logging_enabled') ? 'on' : 'off',
      'tokenizer'    => function_exists('token_get_all')    ? 'on' : 'off',
    );

    $html = '<ul id="sfWebDebugConfigSummary">';
    foreach ($config as $key => $value)
    {
      $html .= '<li class="is'.$value.($key == 'xcache' ? ' last' : '').'">'.$key.'</li>';
    }
    $html .= '</ul>';

    $context = sfContext::getInstance();
    $html .= $this->formatArrayAsHtml('request',  sfDebug::requestAsArray($context->getRequest()));
    $html .= $this->formatArrayAsHtml('response', sfDebug::responseAsArray($context->getResponse()));
    $html .= $this->formatArrayAsHtml('user',     sfDebug::userAsArray($context->getUser()));
    $html .= $this->formatArrayAsHtml('settings', sfDebug::settingsAsArray());
    $html .= $this->formatArrayAsHtml('globals',  sfDebug::globalsAsArray());
    $html .= $this->formatArrayAsHtml('php',      sfDebug::phpInfoAsArray());
    $html .= $this->formatArrayAsHtml('symfony',  sfDebug::symfonyInfoAsArray());

    return $html;
  }

  /**
   * Converts an array to HTML.
   *
   * @param string $id     The identifier to use
   * @param array  $values The array of values
   *
   * @return string An HTML string
   */
  protected function formatArrayAsHtml(string $id, array $values): string
  {
    $id = ucfirst(strtolower($id));

    return '
    <h2>'.$id.' '.$this->getToggler('sfWebDebug'.$id).'</h2>
    <div id="sfWebDebug'.$id.'" style="display: none"><pre>'.htmlspecialchars(Yaml::dump(sfDebug::removeObjects($values)), ENT_QUOTES, sfConfig::get('sf_charset')).'</pre></div>
    ';
  }
}
