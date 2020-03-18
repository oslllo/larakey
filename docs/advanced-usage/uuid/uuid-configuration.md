# Configuration (morph key) <a id="uuid-configuration"></a>

You will probably also want to update the configuration `column_names.model_morph_key`:

- Change to `model_uuid` instead of the default `model_id`. (The default of `model_id` is shown in this snippet below. Change it to match your needs.)

        'column_names' => [    
            /*
             * Change this if you want to name the related model primary key other than
             * `model_id`.
             *
             * For example, this would be nice if your primary keys are all UUIDs. In
             * that case, name this `model_uuid`.
             */
            'model_morph_key' => 'model_id',
        ],

---
