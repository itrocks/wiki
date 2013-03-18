<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;
use SAF\Framework\Namespaces;

class Forum_Url_Utils {
	//------------------------------------------------------------------------------------ arrayToUri
	/**
	 * @param $array string[]
	 * @return string
	 */
	public static function arrayToUri($array)
	{
		$uri = "";
		$isGets = false;
		foreach ($array as $element) {
			if(is_object($element)){
				$element = $element->title;
			}
			if (strstr($element, "?") == true) {
				$isGets = true;
			}
			if ($isGets) {
				$uri .= $element;
			}
			else {
				$uri .= "/" . $element;
			}
		}
		return $uri;
	}

	//------------------------------------------------------------------------------ encodeUrlElement
	/**
	 * @param $element string
	 * @return string
	 */
	public static function encodeUrlElement($element){
		$element = rawurlencode(strtolower($element));
		//accents
		$pattern = array(
			"/%B0/", "/%E8/", "/%E9/", "/%EA/", "/%EB/", "/%E7/", "/%E0/", "/%E2/", "/%E4/", "/%EE/",
			"/%EF/", "/%F9/", "/%FC/", "/%FB/", "/%F4/", "/%F6/", "/%F1/", "/%E3%A9/", "/%E3%A0/",
			"/%E3%A8/", "/%E3%AB/", "/%E3%AE/", "/%E3%AA/"
		);
		$rep_pat = array(
			"-", "e", "e", "e", "e", "c", "a", "a", "a", "i", "i", "u",
			"u", "u", "o", "o", "n", "e", "a", "e", "e", "i", "e"
		);
		$element   = preg_replace($pattern, $rep_pat, $element);

		$pattern = array("/%C3%AB/", "/%C3%AE/");
		$rep_pat = array( "e", "i" );
		$element = preg_replace($pattern, $rep_pat, $element);

		return $element;
	}

	//------------------------------------------------------------------------------------ getBaseUrl
	/**
	 * Create a basic URL. If elements are passed in parameters, try to add this elements after.
	 * Can take string parameters, arrays, and object if the object have a title parameters.
	 * @return string
	 */
	public static function getBaseUrl()
	{
		$base = "http://" . $_SERVER["HTTP_HOST"]
			. str_replace(".php", "", $_SERVER["SCRIPT_NAME"])
			. "/Forum/";
		if(func_get_args()){
			foreach(func_get_args() as $arg){
				if(is_array($arg)){
					foreach($arg as $element){
						$base = self::getUrl($element, $base);
					}
				}
				else {
					$base = self::getUrl($arg, $base);
				}
			}
		}
		return $base;
	}

	//---------------------------------------------------------------------------------- getParentUrl
	/**
	 * Return the parent url.
	 * @param $url string
	 * @return string
	 */
	public static function getParentUrl($url)
	{
		$url_tab = explode("/", $url);
		for($i = count($url_tab) - 1 ; $i >= 0 ; $i--){
			if($url_tab[$i] != ""){
				unset($url_tab[$i]);
				return join("/", $url_tab);
			}
		}
		return $url;
	}

	//------------------------------------------------------------------------------- getUrl
	/**
	 * Form an url, an put in right format.
	 * @param $element string|object The element destination for the url
	 * @param $base_url null|string The base url, if not indicated or null, use getBaseUrl()
	 * @param $getters array
	 * @param $is_secure boolean For edit/delete or other operation which change the state of the object, use the id reference if is_secure is true.
	 * @return mixed The url
	 */
	public static function getUrl($element, $base_url = null, $getters = array(), $is_secure = false)
	{
		if($base_url == null)
			$base_url = self::getBaseUrl();
		$url = $base_url;
		if(is_object($element)){
			if(isset($element->title) && !$is_secure){
				$element = $element->title;
			}
			else {
				$getters[strtolower(Namespaces::shortClassName(get_class($element)))] =
					Dao::getObjectIdentifier($element);
				$element = "";
			}
		}
		if($element != null && count($element))
			$url .= self::encodeUrlElement($element) . "/";
		$is_first = true;
		foreach($getters as $key => $getter){
			$join = "&";
			if($is_first){
				$join = "?";
				$is_first = false;
			}
			$url .= $join . $key . "=" . $getter;
		}
		$utl = str_replace(" ", "%20;", $url);
		return $url;
	}

	//------------------------------------------------------------------------------------ uriToArray
	/**
	 * @param $uri string
	 * @return string[]
	 */
	public static function uriToArray($uri)
	{
		$uri = explode("/", str_replace(",", "/", $uri));
		array_shift($uri);
		if (end($uri) === "") {
			array_pop($uri);
		}
		return $uri;
	}
}
