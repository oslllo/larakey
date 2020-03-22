# <u>Using blade directives with multiple guards</u>

You can use all of the blade directives offered by this package by passing in the guard you wish to use as the second argument to the directive:

```php
@role('super-admin', 'admin')
    I am a super-admin!
@else
    I am not a super-admin...
@endrole
```
---
