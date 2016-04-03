<?php
namespace SAF\Wiki\markup;

use SAF\Framework\Controller\Target;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Tools\Wiki;
use SAF\Framework\Tools\Wiki\Textile;
use SAF\Framework\View\Html\Dom\Anchor;

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
	 * @param $uri string
	 * @return string The <a href="..." target="#main">...</a>
	 */
	public function htmlAnchor($uri)
	{
		// TODO textile should be more intelligent and detect those @ are not code
		$anchor = new Anchor(SL . strUri($uri), str_replace('@', '&#64;', $uri));
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
		$i = 0;
		$length = strlen($string);
		while (($i < $length) && (($i = strpos($string, '[', $i)) !== false)) {
			$i++;
			if (($i < $length)) {
				// escape with [[ => [ without parsing
				if ($string[$i] == '[') {
					$string = substr($string, 0, $i) . substr($string, $i + 1);
					$length --;
				}
				// parse link : replace [A link] with "A link":/a-link
				elseif (($string[$i] != ']') && ($j = strpos($string, ']', $i)) !== false) {
					$uri = substr($string, $i, $j - $i);
					$length -= (strlen($uri) + 2);
					$uri = ((strpos($uri, 'http://') === 0) || (strpos($uri, 'https://') === 0))
						? (DQ . rParse($uri, '//') . DQ . ':' . $uri)
						: $this->htmlAnchor($uri);
					$uri_length = strlen($uri);
					$length += $uri_length;
					$string = substr($string, 0, $i - 1) . $uri . substr($string, $j + 1);
					$i += $uri_length;
				}
			}
		}
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

}
