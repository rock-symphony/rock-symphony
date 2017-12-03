<?php

class myFilter extends sfFilter
{
  public function execute(sfFilterChain $filterChain)
  {
    // only activate if we are in the filter module
    if ('filter' != $this->getContext()->getModuleName())
    {
      $filterChain->execute();
      return;
    }

    $response = $this->getContext()->getResponse();

    $response->setContent($response->getContent().'<div class="before" />');

    $filterChain->execute();

    $response->setContent($response->getContent().'<div class="after" />');
  }
}
