<?php
class I18nCustomCatalogueForm extends I18nForm
{
  public function configure(): void
  {
    parent::configure();
    $this->widgetSchema->getFormFormatter()->setTranslationCatalogue('custom');
  }
}
