<?php
namespace ITRocks\Wiki\markup;

use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Wiki;
use ITRocks\Framework\Tools\Wiki\Textile;
use ITRocks\Framework\View\Html\Dom\Anchor;

/**
 * Class Links
 */
class Links implements Registerable
{

	//----------------------------------------------------------------------------------- absoluteUri
	/**
	 * When textile parse a link, each non / nor protocol:// links must be absolute instead of relative
	 *
	 * @param $uri string
	 */
	public function absoluteUri(&$uri)
	{
		if ((substr($uri, 0, 1) !== SL) && !strpos($uri, '://')) {
			$uri = SL . $uri;
		}
	}

	//------------------------------------------------------------------------------------ htmlAnchor
	/**
	 * @param $uri  string
	 * @param $text string
	 * @return string The <a href="..." target="#main">...</a>
	 */
	public function htmlAnchor($uri, $text)
	{
		// TODO textile should be more intelligent and detect those @ are not code
		$anchor = new Anchor(SL . static::strUri($uri), str_replace('@', '&#64;', $text));
		$anchor->setAttribute('target', Target::MAIN);
		return strval($anchor);
	}

	//-------------------------------------------------------------------------------- parseWikiLinks
	/**
	 * Parse [Wiki links] and replace then with "Wiki links":/wiki-links
	 *
	 * @param $string string The string where to found and parse wiki links
	 */
	public function parseWikiLinks(&$string)
	{
		$i      = 0;
		$length = strlen($string);
		while (($i < $length) && (($i = strpos($string, '[', $i)) !== false)) {
			$i ++;
			if (($i < $length)) {
				// escape with [[ => [ without parsing
				if (($string[$i] === '[') && ($string[$i + 1] !== '[')) {
					$string = substr($string, 0, $i) . substr($string, $i + 1);
					$length --;
				}
				// parse link : replace [A link] with "A link":/a-link
				elseif (($string[$i] !== ']') && ($j = strpos($string, ']', $i)) !== false) {
					$uri = substr($string, $i, $j - $i);
					if (strpos($uri, '>') !== false) {
						list($text, $uri) = explode('>', $uri, 2);
					}
					else {
						$text = $uri;
					}
					$uri = ((strpos($uri, 'http://') === 0) || (strpos($uri, 'https://') === 0))
						? (DQ . $text . DQ . ':' . $uri)
						: $this->htmlAnchor($uri, $text);
					$uri_length  = strlen($uri);
					$string      = substr($string, 0, $i - 1) . $uri . substr($string, $j + 1);
					$i          += $uri_length;
					$length      = strlen($string);
				}
			}
		}
		$string = str_replace(['[[', ']]'], ['[', ']'], $string);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Textile::class, 'parseURI'], [$this, 'absoluteUri']);
		$aop->beforeMethod([Wiki::class,    'textile'],  [$this, 'parseWikiLinks']);
	}

	//---------------------------------------------------------------------------------------- strUri
	/**
	 * @param $link string
	 * @return string
	 */
	public static function strUri($link)
	{
		return strUri(preg_replace('%([a-z])([A-Z])%', '$1-$2', $link));
	}

}
