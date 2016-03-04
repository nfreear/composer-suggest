[![Latest Stable Version][packagist-icon]][packagist] [![MIT License][license-icon]][MIT]
[![Build status — Travis-CI][travis-icon]][travis]

# composer-suggest

A [Composer plugin][] to install a custom group of [suggested][] packages, based on keyword patterns. _([Caution](#caution))_


Example `composer.json`:

```json
{
    "suggest": {
      "a/b": "1.0; This package is for [LACE] only",
      "c/d": "2.1, This package is for JuxtaLearn and LACE.",
      "e/f": "3.2, This is just for [JXL].",
      "g/h": "1.0-beta; Experiment-A"
    }
}
```


## Usage

1. Set an environment variable containing a pattern/keywords in a `.env` file,
    ```bash
    echo 'NF_COMPOSER_SUGGEST="(EXP|LACE)"' > .env
    ```

2. Require the plugin,
    ```
    composer require nfreear/composer-suggest
    ```

3. Install as you would normally (verbose),
    ```
    composer -vvv install
    ```


## Legacy

In [Composer script][] mode, an example `composer.json` might contain:

```json
{
    "suggest": {
      "a/b": "1.0; This package is for [LACE] only",
      "c/d": "2.1, This package is for JuxtaLearn and LACE.",
      "e/f": "3.2, This is just for [JXL].",
      "g/h": "1.0-beta; Experiment-A"
    },

    "scripts": {
      "dry-run-suggest": "\\Nfreear\\Composer\\Suggest::dryRun",
      "install-suggest": "\\Nfreear\\Composer\\Suggest::install",
      "install-lace": "./vendor/bin/suggest --dry LACE"
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


## Caution

Use of composer-suggest implies that you probably won't commit `composer.lock` to version control. [Various][] people [say][] this is [bad][], and as a general rule they are probably correct.

[Composer-suggest][] works well when all/most of the dependencies in `require` and `suggest` have precise version constraints (`1.2.3`) as opposed to loose ones (`1.*`, `>= 1.5`..). It is also useful during rapid development phases of a project. See it in use in the [LACE/ OER Research Hub code][ex]-base.

[_Caveat utilitor!_][beware]


Developed for the [LACE Evidence Hub][], part of the [Learning Analytics Community Exchange][] project.

Inspired by and based in part on the [composer-merge-plugin][] – thank you!

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
[suggested]: https://getcomposer.org/doc/04-schema.md#suggest
[Institute of Educational Technology]: http://iet.open.ac.uk/
[Learning Analytics Community Exchange]: http://www.laceproject.eu "LACE project"
[LACE Evidence Hub]: http://evidence.laceproject.eu/
[ex]: https://github.com/IET-OU/oer-evidence-hub-org/blob/9801a671d9b3/composer-TEMPLATE.json#L43-L68 "suggest: {..} in composer.json — LACE/ OER Hub code"
[Various]: https://getcomposer.org/doc/01-basic-usage.md#composer-lock-the-lock-file "Composer documentation"
[say]: https://blog.engineyard.com/2014/composer-its-all-about-the-lock-file "Engineyard blog"
[bad]: http://stackoverflow.com/questions/12896780/should-composer-lock-be-committed-to-version-c.. "Stackoverflow"
[beware]: http://en.wiktionary.org/wiki/caveat_emptor "'User beware'"
[composer-merge-plugin]: https://github.com/wikimedia/composer-merge-plugin


[travis]:  https://travis-ci.org/nfreear/composer-suggest
[travis-icon]: https://api.travis-ci.org/nfreear/composer-suggest.svg "Build status – Travis-CI"
