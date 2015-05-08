<?php
/**
 * Composer 'script'. Can we find a simple way of installing Composer suggestions?
 *
 * USAGE:  composer run-script install-suggest "Ju?X(ta)?L"
 *
 * @author    Nick Freear, 29 April 2015.
 * @copyright 2015 The Open University.
 * @license   MIT
 */

namespace Nfreear\Composer;

use Composer\Script\CommandEvent;

class Suggest
{

    const ENV = 'NF_COMPOSER_SUGGEST';

    const COMPOSER = 'php ../composer.phar ';

    const RE_VERSION = '@^(?<version>[^\s]+) @';

    const RE_VEND_PKG = '@(?<vendor>[a-z\d\-]+)\/(?<package>[\w\-]+)@';

    protected static $event;


    /** Main install method.
    */
    public static function install(CommandEvent $event = null)
    {
        self::$event = $event;

        self::out(__METHOD__);

        $command = self::composeSuggestionsCommand();

        if ($command) {
            self::debug('Command:');
            self::debug($command . PHP_EOL);

            system($command, $result);
        } else {
            self::out('No matches, no composer-require triggered.');
            $result = 0;
        }

        exit($result);
    }

    /** Dry run method.
    */
    public static function dry_run(CommandEvent $event = null)
    {
        self::$event = $event;

        self::out(__METHOD__);

        $command = self::composeSuggestionsCommand();

        if ($command) {
            self::debug('Command (dry-run):');
            self::debug($command . PHP_EOL);
        } else {
            self::out('No matches, no composer-require triggered (dry-run).');
        }

        exit(0);
    }


    // ======================================================

    /** Main worker method.
    * @return string
    */
    protected static function composeSuggestionsCommand()
    {

        $regex = self::getArgvEnvPattern();
        $composer = self::getComposerData();

        self::debug('Pattern (perl-compatible reg exp):    ' . $regex);

        $suggest_r = self::matchSuggestions($composer->suggest, $regex);

        if ($suggest_r) {
            $command = self::COMPOSER . 'require ' . implode(' ', $suggest_r);
            return $command;
        }
    }

    /** Get the `pattern` from command-line or environment.
    * @return string
    */
    protected static function getArgvEnvPattern()
    {
        global $argv, $argc;

        $arguments = self::$event ? self::$event->getArguments() : null;

        if ($arguments) {
            $pattern = $arguments[count($arguments) - 1];
        } elseif (isset($argv)) {
            $pattern = ($argc > 1) ? $argv[$argc - 1] : getenv(self::ENV);
        }

        if (!$pattern) {
            var_dump($argv);
            self::fatal('Insufficient arguments/ no environment variable set; '. self::ENV);
        }
        $regex = '/' . $pattern . '/i';

        return $regex;
    }

    /** Get object representation of `composer.json`
    * @return object
    */
    protected static function getComposerData()
    {
        $json = file_get_contents('./composer.json');
        return (object) json_decode($json);
    }

    /** Loop through suggestions, finding matches
    * @return array Array of matches.
    */
    protected static function matchSuggestions($suggestions, $regex)
    {
        $suggest_r = array();

        // http://stackoverflow.com/questions/3535765/capturing-regex-comp--errors
        try {
            set_error_handler('self::regexErrorHandler');
            preg_match($regex, 'dummy');
        } catch (\Exception $ex) {
            self::fatal('Error in pattern. '. $ex->getMessage());
        }
        restore_error_handler();

        foreach ($suggestions as $package => $info) {
            if (preg_match($regex, $info)
                    && preg_match(self::RE_VEND_PKG, $package)
                    && preg_match(self::RE_VERSION, $info, $matches)) {
                $version = rtrim($matches[ 'version' ], ';, ');

                $suggest_r[] = $package . ':' . $version;
                self::debug("Match:    '$package' => '$info'");
            } else {
                self::debug("No match (or error):    '$package' => '$info'");
            }
        }
        return count($suggest_r) > 0 ? $suggest_r : null;
    }

    protected static function regexErrorHandler($errno, $str, $file, $line, $context)
    {
        throw new \ErrorException($str, 0, $errno, $file, $line);
    }


    /** Utilities.
    */

    protected static function out($message)
    {
        if (self::$event) {
            self::$event->getIO()->write("    <info>[suggest]</info> $message");
        } else {
            fwrite(STDERR, ' > ' . $message . PHP_EOL);
        }
    }

    protected static function debug($message)
    {
        if (!self::$event) {
            fwrite(STDERR, ' > ' . $message . PHP_EOL);
        } elseif (self::$event->getIO()->isVerbose()) {
            self::$event->getIO()->write("    <info>[suggest]</info> $message");
        }
    }

    protected static function fatal($message)
    {
        if (self::$event) {
            self::$event->getIO()->writeError("    <error>[suggest]</error> $message");
        } else {
            fwrite(STDERR, 'ERROR. ' . $message . PHP_EOL);
        }
        exit(1);
    }
}


// ======================================================


call_user_func(function () {
    global $argv;

    if (isset($argv) && false !== strpos(__FILE__, $argv[ 0 ])) {
        Suggest::install();
    }
});


#End.
