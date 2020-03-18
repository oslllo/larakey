# Models <a id="uuid-models"></a>

You will probably want to Extend the default Role and Permission models into your own namespace, to set some specific properties (see the Extending section of the docs):

- You may want to set `protected $keyType = "string";` so Laravel doesn't cast it to integer.
- You may want to set `protected $primaryKey = 'guid';` (or `uuid`, etc) if you changed the column name in your migrations.
- Optional: Some people have reported value in setting `public $incrementing = false;`, but others have said this caused them problems. Your implementation may vary.

---
