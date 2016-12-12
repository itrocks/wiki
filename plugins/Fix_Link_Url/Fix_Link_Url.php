<?php
namespace ITRocks\Wiki\Plugins;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Wiki;

/**
 * Fix urls build by Textile.
 * Build urls as : /url
 */
class Fix_Link_Url implements Registerable
{

	//------------------------------------------------------------------------------ afterWikiTextile
	/**
	 * Read the text returned, and parse url.
	 *
	 * @param $result string
	 * @return string
	 */
	public function afterWikiTextile($result)
	{
		return $this->parseUrl($result);
	}

	//------------------------------------------------------------------------------------- formatUrl
	/**
	 * Replace in write format, it's a callable for preg_replace_callback.
	 * @param $matches string[]
	 * @return string
	 */
	private function formatUrl(array $matches)
	{
		if (filter_var($matches[1], FILTER_VALIDATE_URL)) {
			return 'href="' . $matches[1] .  '"';
		}
		else {
			return 'href="/'. $matches[1] . '"';
		}
	}

	//-------------------------------------------------------------------------------------- parseUrl
	/**
	 * Parse wiki's links in a text to replace by textile's links.
	 * @param $text string The string to parse.
	 * @return string The parsed string
	 */
	private function parseUrl($text)
	{
		$callback = function($matches) {
			return $this->formatUrl($matches);
		};
		$text = preg_replace_callback('%href\="(.+)"%', $callback, $text);
		return $text;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod([Wiki::class, 'textile'], [$this, 'afterWikiTextile']);
	}

}
