<?php
/**
 * Pretty much the only file that isn't the other file in this folder
 *
 * @package inline_stylesheets
 */

namespace PlaidPowered;

/**
 * The class that does all the work
 */
class InlineCSS {

	private $inline_stack;
	private $home_url;

	public function __construct() {
		$this->inline_stack = [];
		$this->home_url     = trailingslashit( get_bloginfo( 'url' ) );
	}

	public function setup_hooks() {

		if ( is_admin() ) {
			return;
		}

		add_action( 'style_loader_tag', array( $this, 'remove_style' ), 9999, 4 );
		add_action( 'wp_head', array( $this, 'inline_css_stack' ), 9999 );
	}

	public function remove_style( $tag, $handle, $href, $media ) {

		if ( $media !== 'all' ) {
			return $tag;
		}

		$url = strpos( $href, $this->home_url );
		
		$path = str_replace( $this->home_url, ABSPATH, $href );
		if ( $qspos = strrpos( $path, '?' ) ) {
			$path = substr( $path, 0, $qspos );
		}

		$this->inline_stack[] = [
			'key'  => $handle,
			'url'  => $href,
			'path' => $path
		];

		return '';

	}

	public function inline_css_stack() {

		$this->inline_stack = apply_filters( 'basetheme__inline_stylesheets', $this->inline_stack );

		if ( empty( $this->inline_stack ) ) {
			return;
		}

		$css = '';

		foreach ( $this->inline_stack as $stylesheet ) {

			if ( ! file_exists( $stylesheet['path'] ) ) {
				continue;
			}

			$file = file_get_contents( $stylesheet['path'] );

			$absfolder = substr( $stylesheet['url'], 0, strrpos( $stylesheet['url'], '/' ) );

			$file = self::replace_relative_urls( $file, $absfolder );

			$css .= $file;

		}

		// TODO: uglify because why wouldn't we at this point?

		// clean out all the comments:
		$css = preg_replace( '/\/\*.*?\*\//ms', '', $css );

		echo '<style id="global-style">' . $css . '</style>';

	}

	public static function replace_relative_urls( $file, $new_url ) {

		$urls = preg_match_all( '/url\((.*?)\)/ms', $file, $matches, PREG_SET_ORDER );

		if ( ! $urls ) {
			return $file;
		}

		$replacements = [];

		foreach ( $matches as $match ) {
			$url    = trim( $match[1], " '\"" );
			$format = substr( $url, 0, 4 );

			if ( $url[0] === '/' || in_array( $format, [ 'http', 'data' ] ) ) {
				continue;
			}
			$url = $new_url . '/' . $url;

			if ( ! isset( $replacements[ $match[1] ] ) ) {
				$replacements[ $match[1] ] = $url;
			}

		}

		foreach ( $replacements as $was => $now ) {
			$file = str_replace( $was, $now, $file );
		}

		return $file;

	}
}
