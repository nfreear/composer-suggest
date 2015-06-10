[![Latest Stable Version][packagist-icon]][packagist]
[![MIT License][license-icon]][MIT]

# composer-suggest

A [Composer plugin][] to install [suggestions][] simply, based on keyword patterns.


Example `composer.json`:

```json
{
    "suggest": {
      "a/b": "1.0; This package is for [LACE] only",
      "c/d": "2.*, This package is for JuxtaLearn and LACE.",
      "e/f": "3.*, This is just for [JXL].",
      "g/h": "master; Experiment-A"
    }
}
```


## Usage

```bash
echo 'NF_COMPOSER_SUGGEST="(EXP|LACE)"' > .myenv

composer require nfreear/composer-suggest:dev-master

composer -vvv install
```


## Legacy

In [Composer script][] mode, an example `composer.json` might contain:

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

Legacy usage:

```sh
>  composer -v install-lace
```

Legacy advanced usage:

```sh
>  composer -v dry-run-suggest "Ju?X(ta)?L"    # Packages suggested for 'Juxtalearn' & 'JXL'.
>  composer -v dry-run-suggest "Experiment-A"
```


See [composer-suggest][] in use in the [LACE/ OER Research Hub code][ex]-base.

Developed for the [LACE Evidence Hub][], part of the [Learning Analytics Community Exchange][] project.


---
License: [MIT][]

© 2015 The Open University. ([Institute of Educational Technology][])

[packagist]: https://packagist.org/packages/nfreear/composer-suggest
[packagist-icon]: https://img.shields.io/packagist/v/nfreear/composer-suggest.svg?style=flat
[license-icon]: https://img.shields.io/packagist/l/nfreear/composer-suggest.svg?style=flat
[Composer]: https://getcomposer.org/
[MIT]: http://nfreear.mit-license.org/ "MIT License"
[composer-suggest]: https://github.com/nfreear/composer-suggest
[Composer plugin]: https://getcomposer.org/doc/articles/plugins.md
[Composer script]: https://getcomposer.org/doc/articles/scripts.md
[suggestions]: https://getcomposer.org/doc/04-schema.md#suggest
[Institute of Educational Technology]: http://iet.open.ac.uk/
[Learning Analytics Community Exchange]: http://www.laceproject.eu "LACE project"
[LACE Evidence Hub]: http://evidence.laceproject.eu/
[ex]: https://github.com/IET-OU/oer-evidence-hub-org/blob/CR40-composer/composer-TEMPLATE.json#L34 "suggest: {..} in composer.json"
