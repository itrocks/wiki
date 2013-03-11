<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;
use SAF\Framework\Button;
use SAF\Framework\View;
use SAF\Framework\Color;
use SAF\Framework\Namespaces;

class Forum_Utils
{

	//---------------------------------------------------------------------------------- addAttribute
	/**
	 * Assign attributes of an object to parameters.
	 * @param $parameters array The parameters where put
	 * @param $object     object It's a Category, Forum, Topic or Post object
	 * @param $base_url   array
	 * @param $mode       string
	 * @return array
	 */
	public static function addAttribute($parameters, $object, $base_url, $mode)
	{
		switch(get_class($object)){
			case "SAF\\Wiki\\Category" :
				$parameters["type"] = "Category";
				break;
			case "SAF\\Wiki\\Forum" :
				$parameters["type"] = "Forum";
				break;
			case "SAF\\Wiki\\Topic" :
				$parameters["author"] = $object->author;
				$parameters["type"] = "Topic";
				break;
			case "SAF\\Wiki\\Post" :
				//TODO : see why the User is not auto recovered
				$author = \SAF\Framework\Search_Object::newInstance('Saf\\Wiki\\Wiki_User');
				$author = Dao::read($object->id_author, get_class($author));
				$parameters["content"] = $object->content;
				$parameters["author_name"] = $author->login;
				$parameters["author_link"] = self::getBaseUrl("author") . $parameters["author_name"];
				$parameters["type"] = "Post";
				break;
			default:
				break;
		}
		if($object)
			$parameters["title"] = $object->title;
		$parameters = self::getAttributeCol($object, $parameters);
		$parameters["attributes_number"] = count($parameters["attribute_values"]) + 1;
		$parameters["buttons"] = self::getButtons($object, $base_url, $mode);
		return $parameters;
	}

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

	//------------------------------------------------------------------------------- generateContent
	/**
	 * Assign the parameter
	 * @param $parameters   array
	 * @param $from         null|string|object The class name if the object is on parameters, or the object.
	 * @param $path         array
	 * @param $mode         string
	 * @param $level_number int
	 * @param $level_max    int
	 * @return array The parameters
	 */
	public static function generateContent(
		$parameters,
		$from,
		$path,
		$mode,
		$level_number = 1,
		$level_max = -1
	)
	{
		$base_url = Forum_Utils::getBaseUrl($path);
		if($from != null && !is_object($from))
			$from = $parameters[$from];
		if($level_max == -1)
			$level_max = $level_number;
		if($level_number){
			$level_number--;
			$level_name = self::getLevelName($level_number, $level_max);
			$block_elements = self::getNextElements($from);

			$blocks = array();
			if(is_array($block_elements)){
				foreach($block_elements as $block_element){
					$url = self::getUrl($block_element->title, $base_url);
					$path_element = $path;
					$short_class_name = Namespaces::shortClassName(get_class($block_element));
					$path_element[$short_class_name] = $block_element;
					$block = array(
						"link" => $url
					);
					$block = self::generateContent(
						$block, $block_element, $path_element, $mode, $level_number, $level_max
					);
					$blocks[] = $block;
				}
			}
			$parameters[$level_name] = $blocks;
		}
		$parameters = self::addAttribute($parameters, $from, $base_url, $mode);
		return $parameters;
	}

	//------------------------------------------------------------------------------- getAttributeCol
	/**
	 * Return attributes col.
	 * @param $object     object It's a Category, Forum, Topic or Post object
	 * @param $parameters array The parameters where put
	 * @return array Parameters
	 */
	public static function getAttributeCol($object, $parameters)
	{
		$title_parent_var_name = "attribute_titles_parent";
		$title_var_name = "attribute_titles";
		$value_var_name = "attribute_values";
		$parameters[$title_parent_var_name] = array();
		$parameters[$title_var_name] = array();
		$parameters[$value_var_name] = array();
		switch(get_class($object)){
			case "SAF\\Wiki\\Category" :
				$parameters = self::getAttributeNameCol("Category", $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol("Forum", $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => 1);
				$parameters[$value_var_name][] = array("value" => 2);
				$parameters[$value_var_name][] = array("value" => 3);
				break;
			case "SAF\\Wiki\\Forum" :
				$parameters = self::getAttributeNameCol("Forum", $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol("Topic", $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => 4);
				$parameters[$value_var_name][] = array("value" => 5);
				break;
			case "SAF\\Wiki\\Topic" :
				$parameters = self::getAttributeNameCol("Forum", $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol("Post", $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => 4);
				break;
			case "SAF\\Wiki\\Post" :
				break;
			default:
				break;
		}
		return $parameters;
	}

	//--------------------------------------------------------------------------- getAttributeNameCol
	/**
	 * Assign columns names in parameters.
	 * @param $short_class string The short class name, as Category, Forum, Topic or Post
	 * @param $var_name string The var name used for parameters.
	 * @param $parameters array The parameters where put
	 * @return mixed parameters
	 */
	public static function getAttributeNameCol($short_class, $var_name, $parameters)
	{
		switch($short_class){
			case "Category" :
				$parameters[$var_name][] = array("value" => "Number of forums");
				$parameters[$var_name][] = array("value" => "Number of topics");
				$parameters[$var_name][] = array("value" => "Number of posts");
				break;
			case "Forum" :
				$parameters[$var_name][] = array("value" => "Number of topics");
				$parameters[$var_name][] = array("value" => "Number of posts");
				break;
			case "Topic" :
				$parameters[$var_name][] = array("value" => "Number of posts");
				break;
			case "Post" :
				break;
			default:
				break;
		}
		return $parameters;
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
				if(is_object($arg)){
					if(isset($arg->title)){
						$base .= $arg->title . "/";
					}
				}
				else if(is_array($arg)){
					foreach($arg as $element){
						if(is_object($element))
							if(isset($element->title))
								$base .= $element->title . "/";
							else
								$base .= $element . "/";
					}
				}
				else {
					$base .= $arg . "/";
				}
			}
		}
		return $base;
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
		}
		return Button::newCollection($buttons);
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
				$buttons[] = array(
					"Submit",
					self::getUrl(
						"", self::getParentUrl($base_url),
						array("mode" => "write", "post" => Dao::getObjectIdentifier($object))
					),
					"write",
					array(Color::of("green"), ".submit", "#main")
				);
				$buttons[] = array(
					"Preview",
					self::getUrl(
						"", self::getParentUrl($base_url),
						array("mode" => "preview", "post" => Dao::getObjectIdentifier($object))
					),
					"preview",
					array(Color::of("green"), "#main")
				);
				$buttons[] = array(
					"Back",
					self::getParentUrl($base_url),
					"back",
					array(Color::of("green"), "#main")
				);
				break;
			case "SAF\\Wiki\\Category":
			case "SAF\\Wiki\\Forum":
			case "SAF\\Wiki\\Topic":
				$buttons[] = array(
					"Write",
					self::getUrl("", $base_url, array("mode" => "write")),
					"write",
					array(Color::of("green"), ".submit", "#main")
				);
				$buttons[] = array(
					"Back",
					self::getParentUrl($base_url),
					"back",
					array(Color::of("green"), "#main")
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
		$buttons = array();
		switch(get_class($object)){
			case "SAF\\Wiki\\Post":
				$parent_url = self::getParentUrl($base_url);
				$identifier = Dao::getObjectIdentifier($object);
				$buttons[] = array(
					"Quote",
					self::getUrl("", $parent_url, array("mode" => "quote", "post" => $identifier)),
					"edit",
					array(Color::of("green"), "#main")
				);
				$buttons[] = array(
					"Report",
					self::getUrl("", $parent_url, array("mode" => "report", "post" => $identifier)),
					"edit",
					array(Color::of("green"), "#main")
				);
				$buttons[] = array(
					"Delete",
					self::getUrl("", $parent_url, array("mode" => "delete", "post" => $identifier)),
					"edit",
					array(Color::of("green"), ".need_confirm", "#main")
				);
				$buttons[] = array(
					"Edit",
					self::getUrl("", $parent_url,	array("mode" => "edit", "post" => $identifier)),
					"edit",
					array(Color::of("green"), "#main")
				);
				break;
			case "SAF\\Wiki\\Category":
			case "SAF\\Wiki\\Forum":
				break;
			case "SAF\\Wiki\\Topic":
				$buttons[] = array(
					"New post",
					self::getUrl($object->title, $base_url, array("mode" => "new")),
					"new",
					array(Color::of("green"), "#main")
				);
				$buttons[] = array(
					"Edit",
					self::getUrl("", $base_url, array("mode" => "edit")),
					"edit",
					array(Color::of("green"), "#main")
				);
				break;
			default:
		}
		return $buttons;
	}

	//--------------------------------------------------------------------------------- getCategories
	/**
	 * Return all Categories
	 * @return Category
	 */
	public static function getCategories()
	{
		return Dao::readAll("SAF\\Wiki\\Category");
	}

	//------------------------------------------------------------------------------- getClassInLevel
	/**
	 * Return a short class name in function of level.
	 * @param $level int Current level
	 * @return string Class name
	 */
	public static function getClassInLevel($level)
	{
		switch($level){
			case 0:
				return "Category";
			case 1:
				return "Forum";
			case 2:
				return "Topic";
			case 3:
				return "Post";
			default:
				return "undefined";
		}
	}

	//------------------------------------------------------------------------------------ getElement
	/**
	 * Return an element with his title in function of a parent element.
	 * @param $parent object|null Parent element, as Category, Forum, Topic.
	 * If the parent is null, the children element is a Category.
	 * @param $title  string Title of the object
	 * @return object Return the element.
	 */
	public static function getElement($parent, $title)
	{
		$item = null;
		switch(get_class($parent)){
			case "SAF\\Wiki\\Category" :
				$item = new Forum();
				$item->category = $parent;
				$item->title = $title;
				break;
			case "SAF\\Wiki\\Forum" :
				$item = new Topic();
				$item->forum = $parent;
				$item->title = $title;
				break;
			case "SAF\\Wiki\\Topic" :
				$item = new Post();
				$item->topic = $parent;
				$item->title = $title;
				break;
			case "SAF\\Wiki\\Post" :
				$item = $parent;
				break;
			default:
				$item = new Category();
				$item->title = $title;
				break;
		}
		$element = Dao::searchOne($item);
		return (isset($element) ? $element : $item);
	}

	//--------------------------------------------------------------------------- getElementOnGetters
	/**
	 * Return an element put in getters.
	 * @param $getters
	 * @return object
	 */
	public static function getElementOnGetters($getters)
	{
		foreach($getters as $key => $getter){
			switch(strtolower($key)){
				case "post":
					$post = new Post();
					$post = Dao::read($getter, get_class($post));
					return $post;
			}
		}
	}

	//--------------------------------------------------------------------------- getElementsRequired
	/**
	 * Return the element required, found with parameters (strings, objects or array)
	 * @return array An array with the answer, with two keys : element for the element found,
	 * and path for an array width all element of the path.
	 */
	public static function getElementsRequired()
	{
		$parent = null;
		$answer = array("element" => null, "path" => array());
		$level = 0;
		if(func_get_args()){
			foreach(func_get_args() as $arg){
				if(is_array($arg)){
					foreach($arg as $item){
						$element = self::getElement($parent, $item);
						$answer["element"] = $element;
						$answer["path"][self::getClassInLevel($level)] = $element;
						$parent = $element;
						$level++;
					}
				}
				else if(is_string($arg)){
					$element = self::getElement($parent, $arg);
					if(!$element)
						break;
					$answer["element"] = $element;
					$answer["path"][self::getClassInLevel($level)] = $element;
					$parent = $element;
					$level++;
				}
				else if(is_object($arg)){
					$answer["element"] = $arg;
					$answer["path"][self::getClassInLevel($level)] = $arg;
					$parent = $arg;
					$level++;
				}
			}
		}
		return $answer;
	}

	//------------------------------------------------------------------------------------- getForums
	/**
	 * Return all forums of a category.
	 * @param $category
	 * @return Forum[]
	 */
	public static function getForums($category)
	{
		$search = new Forum();
		$search->category = $category;
		/** @var $forums Forum[] */
		$forums = Dao::search($search);
		return $forums;
	}

	//---------------------------------------------------------------------------------- getLevelName
	/**
	 * Return a name for a block's level
	 * @param $level_now string current level
	 * @param $level_max string level max
	 * @return string A level name
	 */
	public static function getLevelName($level_now, $level_max)
	{
		$level =  $level_max - $level_now;
		switch($level){
			case 0:
				return "racine";
			case 1:
				return "blocks";
			case 2:
				return "items";
			case 3:
				return "sub-item";
			default:
				return "level" . $level;
		}
	}

	//------------------------------------------------------------------------------- getNextElements
	/**
	 * Return next elements of an object (the forums of a category, topic of forum, etc.)
	 * @param $object
	 * @return null|Category[]|Forum[]|Topic[]|Post[]
	 */
	public static function getNextElements($object)
	{
		switch(get_class($object)){
			case "SAF\\Wiki\\Category" :
				return self::getForums($object);
			case "SAF\\Wiki\\Forum" :
				return self::getTopics($object);
			case "SAF\\Wiki\\Topic" :
				return self::getPosts($object);
			case "SAF\\Wiki\\Post" :
				return null;
			default:
				return self::getCategories();
		}
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

	//--------------------------------------------------------------------------------------- getPath
	/**
	 * Return the current path
	 * @return array
	 */
	public static function getPath()
	{
		$forum_path = \SAF\Framework\Session::current()->get("SAF\\Wiki\\Forum_Path");
		if($forum_path != null && is_object($forum_path)){
			return $forum_path->path;
		}
		return array();
	}

	//-------------------------------------------------------------------------------------- getPosts
	/**
	 * Return all posts in a topic.
	 * @param $topic Topic
	 * @return Post[]
	 */
	public static function getPosts($topic)
	{
		$search = new Post();
		$search->topic = $topic;
		/** @var $forums Post[] */
		$forums = Dao::search($search);
		return $forums;
	}

	//------------------------------------------------------------------------------------- getTopics
	/**
	 * Return all topics in a forum.
	 * @param $forum
	 * @return Topic[]
	 */
	public static function getTopics($forum)
	{
		$search = new Topic();
		$search->forum = $forum;
		/** @var $forums Topic[] */
		$forums = Dao::search($search);
		return $forums;
	}

	//------------------------------------------------------------------------------- getUrl
	/**
	 * Form an url, an put in right format.
	 * @param $element string The element destination for the url
	 * @param $base_url null|string The base url, if not indicated or null, use getBaseUrl()
	 * @param $getters array
	 * @return mixed The url
	 */
	public static function getUrl($element, $base_url = null, $getters = array())
	{
		if($base_url == null)
			$base_url = self::getBaseUrl();
		$url = $base_url;
		if($element != null && count($element))
			$url .= $element . "/";
		$is_first = true;
		foreach($getters as $key => $getter){
			$join = "&";
			if($is_first){
				$join = "?";
				$is_first = false;
			}
			$url .= $join . $key . "=" . $getter;
		}
		$url = str_replace(" ", "%20", $url);
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
