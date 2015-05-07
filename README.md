# composer-suggest

A [Composer script][].

Can we find a simple way of installing Composer suggestions?


Example `composer.json`:

```json
{
    "suggest": {
      "a/b": "1.0; This package is for [LACE] only",
      "c/d": "2.*, This package is for JuxtaLearn and LACE.",
      "e/f": "3.*, This is just for [JXL].",
      "g/h": "master; Experiment-A"
    },

    "scripts": {
      "dry-run-suggest": "\\Nfreear\\Composer\\Suggest::dry_run",
      "install-suggest": "\\Nfreear\\Composer\\Suggest::install",
      "install-lace":
        "php -f vendor/nfreear/composer-suggest/src/Suggest.php -- LACE"
    }
}
```

Example usage:

```bash
>  composer -v run-script install-lace
>  composer -v dry-run-suggest -- "Ju?X(ta)?L"   # Install packages suggested for 'Juxtalearn' & 'JXL'.
>  composer -v dry-run-suggest -- "EXP\w*-A"     # Experiment-A only.
```

[Composer script]: https://getcomposer.org/doc/articles/scripts.md

