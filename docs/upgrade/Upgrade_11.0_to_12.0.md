Upgrade guide 11.0 to 12.0
=========================

1. Upgrade rock-symphony dependency to `12.0`:

   ```bash
   composer require rock-symphony/rock-symphony:^12.0
   ```

2. Upgrade your server environment to run at least PHP 8.0, 8.1, or 8.2.

3. Make sure you do not rely on any `ArrayAccess` or `Iterator` interfaces
   in the Symfony core classes:

   This includes dropping of `ArrayAccess` from:
   - `sfEvent` - use `sfEvent::getParameter()` instead of array access
   - `sfRequest` - use `sfRequest::getParameter()` instead of array access
   - `sfUser` - use `sfUser::getAttribute()` instead of array access
   - `sfContext` - use `sfContext::get()` instead of array access

   and dropping of `Serializable` interface in favor of new `__serialize()` 
   magic method from the following classes:
   - `sfParameterHolder`
   - `sfRoute`
   - `sfValidatorError`
   - `sfValidatorErrorSchema`

  and replacing `Iterator` interface implementations with `IteratorAggreate` 
  in the following classes:
  - `sfPager`
  - `sfRouteCollection`
  - `sfValidatorErrorSchema`
  - `sfOutputEscaper`
  - `sfFormFieldSchema`
  - `sfDomCssSelector` (test utils)
  - `sfForm`
  
