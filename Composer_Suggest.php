<?php
/**
 * Composer 'plugin'. Can we find a simple way of installing Composer suggestions?
 *
 * USAGE:  composer run-script install-suggest "J.*X.*L"
 *
 * @author Nick Freear, 29 April 2015.
 */

namespace nfreear\Composer;

use Composer\Composer;


class Composer_Suggest {

  const COMPOSER = 'php ../composer.phar ';

  const RE_VERSION = '@^(?<version>[^\s]+) @';
  
  const RE_VEND_PKG = '@(?<vendor>[a-z\d\-]+)\/(?<package>[\w\-]+)@';


  public static function install() {

    self::out( __METHOD__ );

    $regex = self::get_argv_pattern();
    $composer = self::get_composer();

    self::out( 'Pattern (perl-compatible reg exp):  ' . $regex );

    $suggest_r = self::match_suggestions( $composer->suggest, $regex );

    $command = self::COMPOSER . 'require ' . implode( ' ', $suggest_r );

    self::out( 'Command:' );
    self::out( $command . PHP_EOL );

    system( $command );

    exit( 0 );
  }


  // ======================================================

  protected function get_argv_pattern() {
    global $argv, $argc;

    if ($argc < 2) {
      self::fatal( 'Insufficient arguments.' );
      var_dump( $argv );
    }
    $regex = '/' . $argv[ $argc - 1 ] . '/i';

    return $regex;
  }

  protected function get_composer() {
    $json = file_get_contents( './composer.json' );
    return (object) json_decode( $json );
  }

  protected function match_suggestions( $suggestions, $regex ) {
    $suggest_r = array();

    foreach ( $suggestions as $package => $info ) {
      if (preg_match( $regex, $info )
          && preg_match( self::RE_VEND_PKG, $package )
          && preg_match( self::RE_VERSION, $info, $matches )) {

        $version = rtrim( $matches[ 'version' ], ';,. ' );
        $suggest_r[] = $package . ':' . $version;
        self::out( "Match:  '$package' => '$info'" );
      }
      else {
        self::out( "No match (or error):  '$package' => '$info'" );
      }
    }
    return $suggest_r;
  }

  protected static function out( $msg ) {
    // Verbose option?
    echo ' > ' . $msg . PHP_EOL;
  }

  protected static function fatal( $msg ) {
    write( STDERR, 'ERROR. ' . $msg . PHP_EOL );
    exit( 1 );
  }
}


if (FALSE !== strpos( __FILE__, $argv[ 0 ])) {
  Composer_Suggest::install();
}

#End.
