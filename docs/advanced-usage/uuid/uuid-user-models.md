# <u>User Models</u>

Troubleshooting tip: In the ***Prerequisites*** section of the docs we remind you that your User model must implement the `Illuminate\Contracts\Auth\Access\Authorizable` contract so that the Gate features are made available to the User object.
In the default User model provided with Laravel, this is done by extending another model (aliased to `Authenticatable`), which extends the base Eloquent model. However, your UUID implementation may need to override that in order to set some of the properties mentioned in the Models section above. If you are running into difficulties, you may want to double-check whether your User model is doing UUIDs consistent with other parts of your app.

---
