<?php
namespace SAF\Wiki\markup;

use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Tools\Wiki;
use SAF\Framework\Tools\Wiki\Textile;

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

	//-------------------------------------------------------------------------------- parseWikiLinks
	/**
	 * Parse [Wiki links] and replace then with 'Wiki links':/Wiki_links
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
				// parse link : replace [A link] with 'A link':/A_link
				elseif (($string[$i] != ']') && ($j = strpos($string, ']', $i)) !== false) {
					$uri = substr($string, $i, $j - $i);
					$length -= (strlen($uri) + 2);
					$uri = DQ . $uri . DQ . ':' . strUri($uri);
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
