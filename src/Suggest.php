<?php
/**
 * Composer plugin. Can we find a simple way of installing Composer suggestions?
 *
 * USAGE:  composer run-script install-suggest "Ju?X(ta)?L"
 *
 * @author    Nick Freear, 29 April 2015.
 * @copyright 2015 The Open University.
 * @license   MIT
 */

namespace Nfreear\Composer;

use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Script\CommandEvent;
use Composer\Script\ScriptEvents;

class Suggest implements PluginInterface, EventSubscriberInterface
{

    const ENV = 'NF_COMPOSER_SUGGEST';

    const COMPOSER = 'php ../composer.phar ';

    const RE_VERSION = '@^(?<version>[^\s]+) @';

    const RE_VEND_PKG = '@(?<vendor>[a-z\d\-]+)\/(?<package>[\w\-]+)@';

    protected static $composer;
    protected static $io;
    protected static $event;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        self::$composer = $composer;
        self::$io = $io;

        self::debug(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        self::debug(__METHOD__);

        return array(
            ScriptEvents::PRE_INSTALL_CMD => 'onInstallOrUpdate',
            ScriptEvents::PRE_UPDATE_CMD  => 'onInstallOrUpdate',
        );
    }


    public function onInstallOrUpdate(Event $event)
    {
        self::debug(__METHOD__);

        $root = self::$composer->getPackage();

        $suggest_r = self::composeSuggestionsCommand($do_command = false);

        if (!$suggest_r) {
            self::debug('No matches');
            return;
        }

        $dups = array();
        $requires = $this->mergeLinks(
            $root->getRequires(),
            $suggest_r,
            $root->getName(),
            $dups
        );

        if (getenv(self::ENV . '_DISABLE')) {
            self::debug('Disabled (dry run)');
        } else {
            $root->setRequires($requires);
        }
    }

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

    public static function dotEnvTemplate()
    {
        $env_var = self::ENV;
        echo <<<EOF
#
# File:  .env  (same directory as project's composer.json)
#
# Keyword or pattern
#
$env_var = "(LACE|another)"


EOF;
    }

    // ======================================================

    protected function mergeLinks(array $origin, array $merge, $source, array &$dups)
    {
        $parser = new \Composer\Package\Version\VersionParser();

        foreach ($merge as $name => $constraint) {
            if (!isset($origin[$name])) {
                self::debug("Merging <comment>{$name}</comment>");
                $origin[$name] = new \Composer\Package\Link(
                    $source,
                    $name,
                    $parser->parseConstraints($constraint)
                );
            } else {
                // Defer to solver. TODO: ?
                self::debug("Deferring duplicate <comment>{$name}</comment>");
                $dups[] = $link;
            }
        }
        return $origin;
    }

    /** Main worker method.
    * @return string
    */
    protected static function composeSuggestionsCommand($do_command = true)
    {
        self::loadDotEnv();

        $regex = self::getArgvEnvPattern();
        $composer_data = self::getComposerData();

        self::debug("Pattern/regular expression: <comment>$regex</comment>");

        if (!isset($composer_data->suggest)) {
            self::out("No 'suggest' section found in './composer.json'");
            exit(0);
        }

        $suggest_r = self::matchSuggestions($composer_data->suggest, $regex);

        if ($do_command && $suggest_r) {
            $command = self::COMPOSER . 'require ' . self::join($suggest_r);
            return $command;
        }
        return $suggest_r;
    }

    protected static function join($array, $glue = ':', $sep = ' ')
    {
        $result = array();
        foreach ($array as $key => $val) {
            $result[] = $key . $glue . $val;
        }
        return implode($sep, $result);
    }

    protected static function loadDotEnv()
    {
        try {
            \Dotenv::load('./');
            self::debug('Loaded <comment>.env</comment> file');
        } catch (\Exception $ex) {
            self::debug('Not loaded <comment>.env</comment> file. '. $ex->getMessage());
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
        } elseif (getenv(self::ENV)) {
            $pattern = getenv(self::ENV);
        } elseif (isset($argv)) {
            $pattern = ($argc > 1) ? $argv[$argc - 1] : null;
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

                $suggest_r[ $package ] = $version;
                self::debug("Match: <comment>$package => $info</comment>");
            } else {
                self::debug("No match: <comment>$package => $info</comment>");
            }
        }
        return count($suggest_r) > 0 ? $suggest_r : null;
    }

    protected static function regexErrorHandler($errno, $str, $file, $line, $context)
    {
        throw new \ErrorException($str, 0, $errno, $file, $line);
    }

    protected static function getIO()
    {
        if (self::$io) {
            return self::$io;
        }
        return self::$event ? self::$event->getIO() : null;
    }

    /** Utilities.
    */

    protected static function out($message)
    {
        $io = self::getIO();
        if ($io) {
            $io->write("  <warning>[suggest]</warning> $message");
        } else {
            fwrite(STDERR, ' > ' . $message . PHP_EOL);
        }
    }

    protected static function debug($message)
    {
        $io = self::getIO();
        if (!$io) {
            fwrite(STDERR, ' > ' . $message . PHP_EOL);
        } elseif ($io->isVerbose()) {
            $io->write("  <info>[suggest]</info> $message");
        }
    }

    protected static function fatal($message)
    {
        $io = self::getIO();
        if ($io) {
            $io->writeError("  <error>[suggest]</error> $message");
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
