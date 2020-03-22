### <u>⚠️ Note about using permission names in policies</u>

When calling `authorize()` for a policy method, if you have a permission named the same as one of those policy methods, your permission "name" will take precedence and not fire the policy. For this reason it may be wise to avoid naming your permissions the same as the methods in your policy. While you can define your own method names, you can read more about the defaults Laravel offers in Laravel's documentation at https://laravel.com/docs/authorization#writing-policies

---

---

> ❕ The `givePermissionTo` and `revokePermissionTo` functions can accept a
string or a `Oslllo\Larakey\Models\Permission` object.

---
