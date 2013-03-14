<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;
use SAF\Framework\Button;
use SAF\Framework\Color;

class Forum_Buttons_Utils{

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
			case "SAF\\Wiki\\Post":
				$identifier = Dao::getObjectIdentifier($object);
				$buttons[] = array(
					"Confirm",
					Forum_Utils::getUrl(
						"", Forum_Utils::getParentUrl($base_url),
						array("mode" => "delete", "post" => $identifier)
					),
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
			case "SAF\\Wiki\\Category":
			case "SAF\\Wiki\\Forum":
			case "SAF\\Wiki\\Topic":
				$buttons[] = array(
					"Confirm",
					Forum_Utils::getUrl("", $base_url, array("mode" => "delete")),
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
			case "SAF\\Wiki\\Post":
				$identifier = Dao::getObjectIdentifier($object);
				if(!isset($identifier)){
					$identifier = 0;
				}
				$buttons[] = array(
					"Submit",
					Forum_Utils::getUrl(
						"", Forum_Utils::getParentUrl($base_url),
						array("mode" => "write", "post" => $identifier)
					),
					"write",
					array(Color::of("green"), ".submit", "#main")
				);
				$buttons[] = array(
					"Preview",
					Forum_Utils::getUrl(
						"", Forum_Utils::getParentUrl($base_url),
						array("mode" => "preview", "post" => $identifier)
					),
					"preview",
					array(Color::of("orange"), "#main")
				);
				$buttons[] = array(
					"Back",
					Forum_Utils::getParentUrl($base_url),
					"back",
					array(Color::of("red"), "#main")
				);
				break;
			case "SAF\\Wiki\\Category":
			case "SAF\\Wiki\\Forum":
			case "SAF\\Wiki\\Topic":
				$buttons[] = array(
					"Write",
					Forum_Utils::getUrl("", $base_url, array("mode" => "write")),
					"write",
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
			case "SAF\\Wiki\\Post":
				$buttons = self::getButtonsPostModeOutput($object, $base_url);
				break;
			case "SAF\\Wiki\\Category":
				$buttons = self::getButtonsCategoryModeOutput($object, $base_url);
				break;
			case "SAF\\Wiki\\Forum":
			$buttons = self::getButtonsForumModeOutput($object, $base_url);
				break;
			case "SAF\\Wiki\\Topic":
				$buttons = self::getButtonsTopicModeOutput($object, $base_url);
				break;
			default:
				$buttons = self::getButtonsDefaultModeOutput($base_url);
		}
		return $buttons;
	}

	public static function getButtonsDefaultModeOutput($base_url)
	{
		$buttons[] = array(
			"New category",
			Forum_Utils::getUrl(
				"New category", $base_url, array("mode" => "new")
			),
			"new",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	public static function getButtonsForumModeOutput($object, $base_url)
	{
		$buttons = array();
		$buttons[] = array(
			"New topic",
			Forum_Utils::getUrl("New topic", $base_url, array("mode" => "new")),
			"new",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Edit",
			Forum_Utils::getUrl("", $base_url, array("mode" => "edit")),
			"edit",
			array(Color::of("orange"), "#main")
		);
		$buttons[] = array(
			"Delete",
			Forum_Utils::getUrl("", $base_url, array("mode" => "delete")),
			"delete",
			array(Color::of("red"), "#main")
		);
		return $buttons;
	}

	public static function getButtonsCategoryModeOutput($object, $base_url)
	{
		$buttons = array();
		$buttons[] = array(
			"New forum",
			Forum_Utils::getUrl("New forum", $base_url, array("mode" => "new")),
			"new",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Edit",
			Forum_Utils::getUrl("", $base_url, array("mode" => "edit")),
			"edit",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Delete",
			Forum_Utils::getUrl("", $base_url, array("mode" => "delete")),
			"delete",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	public static function getButtonsTopicModeOutput($object, $base_url)
	{
		$buttons[] = array(
			"Reply",
			Forum_Utils::getUrl("New post", $base_url, array("mode" => "new")),
			"new",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Edit",
			Forum_Utils::getUrl("", $base_url, array("mode" => "edit")),
			"edit",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Delete",
			Forum_Utils::getUrl("", $base_url, array("mode" => "delete")),
			"delete",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}

	public static function getButtonsPostModeOutput($object, $base_url)
	{
		$buttons = array();
		$parent_url = Forum_Utils::getParentUrl($base_url);
		$identifier = Dao::getObjectIdentifier($object);
		$buttons[] = array(
			"Quote",
			Forum_Utils::getUrl("", $parent_url, array("mode" => "quote", "post" => $identifier)),
			"edit",
			array(Color::of("green"), "#main")
		);
		$buttons[] = array(
			"Report",
			Forum_Utils::getUrl("", $parent_url, array("mode" => "report", "post" => $identifier)),
			"edit",
			array(Color::of("green"), "#main")
		);
		$context_edit = array("mode" => "edit");
		$context_delete = array("mode" => "delete");
		if(isset($object->topic) && $object->topic != null || isset($object->id_topic) && $object->id_topic != 0){
			$context_edit["post"] = $identifier;
			$context_delete["post"] = $identifier;
		}
		$buttons[] = array(
			"Delete",
			Forum_Utils::getUrl("", $parent_url, $context_delete),
			"edit",
			array(Color::of("green"), ".need_confirm", "#main")
		);
		$buttons[] = array(
			"Edit",
			Forum_Utils::getUrl("", $parent_url,	$context_edit),
			"edit",
			array(Color::of("green"), "#main")
		);
		return $buttons;
	}
}
