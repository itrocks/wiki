<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;
use SAF\Framework\Button;
use SAF\Framework\Color;

class Forum_Buttons_Utils
{


	//------------------------------------------------------------------------------------ getButtons
	/**
	 * Return the bottom buttons corresponding of the type object and the mode
	 * @param $object   object
	 * @param $base_url string
	 * @param $mode     string
	 * @return Button[]
	 */
	public static function getBottomButtons($object, $base_url, $mode)
	{
		$buttons = array();
		switch($mode){
			case "output":
			case "" :
				$buttons = self::getBottomButtonsModeOutput($object, $base_url);
				break;
			case "edit":
			case "new":
			case "delete":
				break;
		}
		return Button::newCollection($buttons);
	}

	//-------------------------------------------------------------------- getBottomButtonsModeOutput
	public static function getBottomButtonsModeOutput($object, $base_url)
	{
		switch(get_class($object)){
			case Forum_Utils::$namespace . "Post":
				$buttons = self::getButtonsPostModeOutputPublic($object, $base_url);
				break;
			case Forum_Utils::$namespace . "Category":
				$buttons = self::getButtonsCategoryModeOutputPublic($object, $base_url);
				break;
			case Forum_Utils::$namespace . "Forum":
				$buttons = self::getButtonsForumModeOutputPublic($object, $base_url);
				break;
			case Forum_Utils::$namespace . "Topic":
				$buttons = self::getButtonsTopicModeOutputPublic($object, $base_url);
				break;
			default:
				$buttons = self::getButtonsDefaultModeOutput($base_url);
		}
		return $buttons;
	}

	//------------------------------------------------------------------------------------ getButtons
	/**
	 * Return the buttons corresponding of the type object and the mode
	 * @param $object   object
	 * @param $base_url string
	 * @param $mode     string
	 * @return Button[]
	 */
	public static function getButtons($object, $base_url, $mode)
	{
		$buttons = array();
		switch($mode){
			case "output":
			case "" :
				$buttons = self::getButtonsModeOutput($object, $base_url);
				break;
			case "edit":
			case "new":
				$buttons = self::getButtonsModeEdit($object, $base_url);
				break;
			case "delete":
				$buttons = self::getButtonsModeDelete($object, $base_url);
				break;
		}
		return Button::newCollection($buttons);
	}

	//------------------------------------------------------------------ getButtonsCategoryModeOutput
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsCategoryModeOutput($object, $base_url)
	{
		$buttons_public = self::getButtonsCategoryModeOutputPublic($object, $base_url);
		$buttons_private = self::getButtonsCategoryModeOutputPrivate($object, $base_url);
		if(!is_array($buttons_public))	$buttons_public = array();
		if(!is_array($buttons_private))	$buttons_private = array();
		return array_merge($buttons_public, $buttons_private);
	}

	//------------------------------------------------------------ getButtonsCategoryModeOutputPublic
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsCategoryModeOutputPublic($object, $base_url)
	{
		$buttons[] = array(
			"New forum",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "new")),
			"new",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	//----------------------------------------------------------- getButtonsCategoryModeOutputPrivate
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsCategoryModeOutputPrivate($object, $base_url)
	{
		$buttons[] = array(
			"Edit",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "edit"), true),
			"edit",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Delete",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "delete"), true),
			"delete",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	//------------------------------------------------------------------- getButtonsDefaultModeOutput
	/**
	 * @param $base_url string
	 * @return array The list of buttons
	 */
	public static function getButtonsDefaultModeOutput($base_url)
	{
		$buttons[] = array(
			"New category",
			Forum_Url_Utils::getUrl(
				"", $base_url, array("mode" => "new")
			),
			"new",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	//--------------------------------------------------------------------- getButtonsForumModeOutput
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsForumModeOutput($object, $base_url)
	{
		$buttons_public = self::getButtonsForumModeOutputPublic($object, $base_url);
		$buttons_private = self::getButtonsForumModeOutputPrivate($object, $base_url);
		if(!is_array($buttons_public))	$buttons_public = array();
		if(!is_array($buttons_private))	$buttons_private = array();
		return array_merge($buttons_public, $buttons_private);
	}

	//--------------------------------------------------------------- getButtonsForumModeOutputPublic
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsForumModeOutputPublic($object, $base_url)
	{
		$buttons[] = array(
			"New topic",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "new")),
			"new",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	//-------------------------------------------------------------- getButtonsForumModeOutputPrivate
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsForumModeOutputPrivate($object, $base_url)
	{
		$buttons[] = array(
			"Edit",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "edit"), true),
			"edit",
			array(Color::of("orange"), "#main")
		);
		$buttons[] = array(
			"Delete",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "delete"), true),
			"delete",
			array(Color::of("red"), "#main")
		);
		return $buttons;
	}

	//---------------------------------------------------------------------------- getButtonsModeEdit
	/**
	 * Return a list of buttons for the mode delete.
	 * @param $object Object concerned (Post, Topic, Forum or Category)
	 * @param $base_url string The basic url
	 * @return array A list of buttons.
	 */
	public static function getButtonsModeDelete($object, $base_url)
	{
		$buttons = array();
		switch(get_class($object)){
			case Forum_Utils::$namespace . "Post":
				$buttons[] = array(
					"Confirm",
					Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "delete"), true),
					"delete",
					array(Color::of("red"), ".submit", "#main")
				);
				$buttons[] = array(
					"Back",
					$base_url,
					"back",
					array(Color::of("red"), "#main")
				);
				break;
			case Forum_Utils::$namespace . "Category":
			case Forum_Utils::$namespace . "Forum":
			case Forum_Utils::$namespace . "Topic":
				$buttons[] = array(
					"Confirm",
					Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "delete"), true),
					"delete",
					array(Color::of("green"), ".submit", "#main")
				);
				$buttons[] = array(
					"Back",
					$base_url,
					"back",
					array(Color::of("red"), "#main")
				);
			default:
		}
		return $buttons;
	}

	//---------------------------------------------------------------------------- getButtonsModeEdit
	/**
	 * Return a list of buttons for the mode edit.
	 * @param $object Object concerned (Post, Topic, Forum or Category)
	 * @param $base_url string The basic url
	 * @return array A list of buttons.
	 */
	public static function getButtonsModeEdit($object, $base_url)
	{
		$buttons = array();
		switch(get_class($object)){
			case Forum_Utils::$namespace . "Post":
				$buttons[] = array(
					"Submit",
					Forum_Url_Utils::getUrl($object, $base_url,	array("mode" => "write"),	true),
					"write",
					array(Color::of("green"), ".submit", "#main")
				);
				$buttons[] = array(
					"Preview",
					Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "preview"), true),
					"preview",
					array(Color::of("orange"), ".submit", "#main")
				);
				$buttons[] = array(
					"Back",
					$base_url,
					"back",
					array(Color::of("red"), "#main")
				);
				break;
			case Forum_Utils::$namespace . "Category":
			case Forum_Utils::$namespace . "Forum":
			case Forum_Utils::$namespace . "Topic":
				$buttons[] = array(
					"Write",
					Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "write"), true),
					"write",
					array(Color::of("green"), ".submit", "#main")
				);
			$buttons[] = array(
				"Preview",
				Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "preview"), true),
				"preview",
				array(Color::of("orange"), ".submit", "#main")
			);
				$buttons[] = array(
					"Back",
					$base_url,
					"back",
					array(Color::of("red"), "#main")
				);
			default:
		}
		return $buttons;
	}

	//-------------------------------------------------------------------------- getButtonsModeOutput
	/**
	 * Return a list of buttons for the mode output.
	 * @param $object Object concerned (Post, Topic, Forum or Category)
	 * @param $base_url string The basic url
	 * @return array A list of buttons.
	 */
	public static function getButtonsModeOutput($object, $base_url)
	{
		switch(get_class($object)){
			case Forum_Utils::$namespace . "Post":
				$buttons = self::getButtonsPostModeOutput($object, $base_url);
				break;
			case Forum_Utils::$namespace . "Category":
				$buttons = self::getButtonsCategoryModeOutput($object, $base_url);
				break;
			case Forum_Utils::$namespace . "Forum":
			$buttons = self::getButtonsForumModeOutput($object, $base_url);
				break;
			case Forum_Utils::$namespace . "Topic":
				$buttons = self::getButtonsTopicModeOutput($object, $base_url);
				break;
			default:
				$buttons = self::getButtonsDefaultModeOutput($base_url);
		}
		return $buttons;
	}

	//---------------------------------------------------------------------- getButtonsPostModeOutput
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsPostModeOutput($object, $base_url)
	{
		$buttons_public = self::getButtonsPostModeOutputPublic($object, $base_url);
		$buttons_private = self::getButtonsPostModeOutputPrivate($object, $base_url);
		if(!is_array($buttons_public))	$buttons_public = array();
		if(!is_array($buttons_private))	$buttons_private = array();
		return array_merge($buttons_public, $buttons_private);
	}

	//---------------------------------------------------------------- getButtonsPostModeOutputPublic
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsPostModeOutputPublic($object, $base_url)
	{
		$buttons[] = array(
			"Quote",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "quote")),
			"edit",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Report",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "report"), true),
			"edit",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	//--------------------------------------------------------------- getButtonsPostModeOutputPrivate
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsPostModeOutputPrivate($object, $base_url)
	{
		if(!Forum_Utils::hasElementAtAttribute($object, "topic"))
			$object = "";
		$buttons[] = array(
			"Delete",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "delete"), true),
			"edit",
			array(Color::of("green"), ".need_confirm", "#main")
		);
		$buttons[] = array(
			"Edit",
			Forum_Url_Utils::getUrl($object, $base_url,	array("mode" => "edit"), true),
			"edit",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	//--------------------------------------------------------------------- getButtonsTopicModeOutput
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsTopicModeOutput($object, $base_url)
	{
		$buttons_public = self::getButtonsTopicModeOutputPublic($object, $base_url);
		$buttons_private = self::getButtonsTopicModeOutputPrivate($object, $base_url);
		if(!is_array($buttons_public))	$buttons_public = array();
		if(!is_array($buttons_private))	$buttons_private = array();
		return array_merge($buttons_public, $buttons_private);
	}

	//--------------------------------------------------------------------- getButtonsTopicModeOutput
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsTopicModeOutputPublic($object, $base_url)
	{
		$buttons[] = array(
			"Reply",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "new")),
			"new",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	//--------------------------------------------------------------------- getButtonsTopicModeOutput
	/**
	 * @param $object   object
	 * @param $base_url string
	 * @return array
	 */
	public static function getButtonsTopicModeOutputPrivate($object, $base_url)
	{
		$buttons[] = array(
			"Edit",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "edit"), true),
			"edit",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Delete",
			Forum_Url_Utils::getUrl($object, $base_url, array("mode" => "delete"), true),
			"delete",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}
}
