<?php

class configFiltersSimpleFilterFilter extends sfFilter
{
  public function execute(sfFilterChain $filterChain): void
  {
    $this->getContext()->getRequest()->setParameter('filter', 'in a filter');

    // execute next filter
    $filterChain->execute();
  }
}
