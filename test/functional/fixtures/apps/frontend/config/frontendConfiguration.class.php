<?php

class frontendConfiguration extends sfApplicationConfiguration
{
  public function configure(): void
  {
    $this->dispatcher->connect('view.configure_format', array($this, 'configure_format_foo'));
    $this->dispatcher->connect('request.filter_parameters', array($this, 'filter_parameters'));
    $this->dispatcher->connect('view.configure_format', array($this, 'configure_iphone_format'));
  }

  public function filter_parameters(sfEvent $event, array $parameters): array
  {
    if (false !== stripos($event->getSubject()->getHttpHeader('user-agent') ?? '', 'iPhone'))
    {
      $event->getSubject()->setRequestFormat('iphone');
    }

    return $parameters;
  }

  public function configure_iphone_format(sfEvent $event): void
  {
    if ('iphone' == $event['format'])
    {
      $event['response']->addStylesheet('iphone.css');

      $event->getSubject()->setDecorator(true);
    }
  }

  public function configure_format_foo(sfEvent $event): void
  {
    if ('foo' != $event['format'])
    {
      return;
    }

    $event['response']->setHttpHeader('x-foo', 'true');
    $event->getSubject()->setExtension('.php');
  }
}
