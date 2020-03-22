# <u>Assigning permissions and roles to guard users</u>

You can use the same core methods to assign permissions and roles to users; just make sure the `guard_name` on the permission or role matches the guard of the user, otherwise a `GuardDoesNotMatch` or `Role/PermissionDoesNotExist` exception will be thrown.
