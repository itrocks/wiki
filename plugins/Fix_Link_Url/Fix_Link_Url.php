<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Wiki;
use ITRocks\Plugins;

/**
 * Fix urls build by Textile.
 * Build urls as : /url
 */
class Fix_Link_Url implements Plugins\Registerable
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
	private function formatUrl($matches)
	{
		if(filter_var($matches[1], FILTER_VALIDATE_URL)) {
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
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$register->aop->afterMethod(
			array(Wiki::class, 'textile'),
			array($this, 'afterWikiTextile')
		);
	}

}
