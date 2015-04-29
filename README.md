# composer-suggest

A [Composer script][].

Can we find a simple way of installing Composer suggestions?


Example `composer.json`:

```json
{
    "suggest": {
      "a/b": "1.0; This package is [LACE] only",
      "c/d": "2.*. This package is for JuxtaLearn and LACE.",
      "e/f": "3.*, This is just for [JXL]."
    },

    "scripts": {
      "install-lace":
        "php -f vendor/nfreear/composer-suggest/src/Composer_Suggest.php -- LACE"
    }
}
```

Example usage:

    composer run-script install-lace


[Composer script]: https://getcomposer.org/doc/articles/scripts.md

