Upgrade guide 9.0 to 10.0
=========================

1. Upgrade rock-symphony dependency to `10.0`:

   ```bash
   composer require rock-symphony/rock-symphony:^10.0
   ```

2. Make sure you don't have a global class named `sfCookie` in your codebase.
   
3. Make sure that all calls to `sfWebResponse::getCookies()` are now expecting it 
   to return `sfCookie[]` array.

4. Update your test suite if you rely on symfony test classes. 
