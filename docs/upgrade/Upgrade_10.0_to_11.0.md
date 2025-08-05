Upgrade guide 10.0 to 11.0
=========================

1. Upgrade rock-symphony dependency to `11.0`:

   ```bash
   composer require rock-symphony/rock-symphony:^11.0
   ```

2. Upgrade your server environment to run at least PHP 7.4.
   Preferably PHP 8.0, 8.1, or 8.2.

3. If you override any of the `Serializable`, `ArrayAccess` or `Iterator`
   interface methods of the core Symfony classes, make sure their argument 
   and return types are compatible with the parent method.

   This includes:
   - `sfPager`
   - `sfOutputEscaperArrayDecorator`
   - `sfOutputEscaperIteratorDecorator`
   - `sfOutputEscaperObjectDecorator`
   - `sfEvent`
   - `sfForm`
   - `sfFormFieldSchema`
   - `sfRequest`
   - `sfRoute`
   - `sfRouteCollection`
   - `sfUser`
   - `sfContext`
   - `sfDomCssSelector` (test utils)
   - `sfNamespacedParameterHolder`
   - `sfParameterHolder`
   - `sfValidatorError`
   - `sfValidatorErrorSchema`
   - `sfValidatorFile`
   - `sfValidatorSchema`
   - `sfViewParameterHolder`
   - `sfWidgetFormSchema`
   - `sfWidgetFormSchemaDecorator`
   - `sfWidgetFormTime`
