# Extending Role and Permission Models
If you are extending or replacing the role/permission models, you will need to specify your new models in this package's `config/larakey.php` file. 

First be sure that you've published the configuration file (see the Installation instructions), and edit it to update the `models.role` and `models.permission` values to point to your new models.

Note the following requirements when extending/replacing the models: 

If you need to EXTEND the existing `Role` or `Permission` models note that:

- Your `Role` model needs to extend the `Oslllo\Larakey\Models\Role` model
- Your `Permission` model needs to extend the `Oslllo\Larakey\Models\Permission` model

---
