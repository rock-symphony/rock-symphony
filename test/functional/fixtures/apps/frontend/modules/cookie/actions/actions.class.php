<?php

final class cookieActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $cookies = [
      'foo'    => $request->getCookie('foo'),
      'bar'    => $request->getCookie('bar'),
      'foobar' => $request->getCookie('foobar'),
    ];

    // Remove nulls
    $cookies = array_filter($cookies, function ($value) {
      return $value !== null;
    });

    return $this->renderJson((object) $cookies);
  }

  public function executeSetCookie(sfWebRequest $request)
  {
    $this->getResponse()->setCookie('foobar', 'barfoo');

    return sfView::NONE;
  }

  public function executeRemoveCookie(sfWebRequest $request)
  {
    $this->getResponse()->setCookie($request->getParameter('cookie'), 'whatever', time() - 10);

    return sfView::NONE;
  }
}
