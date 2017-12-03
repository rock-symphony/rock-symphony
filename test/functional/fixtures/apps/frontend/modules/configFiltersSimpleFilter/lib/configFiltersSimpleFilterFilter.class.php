<?php

class configFiltersSimpleFilterFilter extends sfFilter
{
  public function execute(sfFilterChain $filterChain)
  {
    $this->getContext()->getRequest()->setParameter('filter', 'in a filter');

    // execute next filter
    $filterChain->execute();
  }
}
