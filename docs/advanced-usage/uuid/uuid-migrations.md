# Migrations <a id="uuid-migrations"></a>

You will probably want to update the `create_permission_tables.php` migration:

- Replace `$table->unsignedBigInteger($columnNames['model_morph_key'])` with `$table->uuid($columnNames['model_morph_key'])`.

---
