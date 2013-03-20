<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;
use SAF\Framework\Names;
use SAF\Framework\View;
use SAF\Framework\Namespaces;
use SAF\Framework\Wiki;
use SAF\Framework\User;

class Forum_Utils
{

	public static $namespace = "SAF\\Wiki\\";

	//---------------------------------------------------------------------------------- addAttribute
	/**
	 * Assign attributes of an object to parameters.
	 * @param $parameters array The parameters where put
	 * @param $object     Category|Forum|Topic|Post It's a Category, Forum, Topic or Post object
	 * @param $base_url   array
	 * @param $mode       string
	 * @return array
	 */
	public static function addAttribute($parameters, $object, $base_url, $mode)
	{
		$class_name = get_class($object);
		switch($class_name){
			case self::$namespace . "Category" :
				break;
			case self::$namespace . "Forum" :
				break;
			case self::$namespace . "Topic" :
				$object = self::assignTopicFirstPost($object);
				$url = Forum_Url_Utils::getUrl($object, $base_url);
				$parameters["main_post"]
					= array(self::addAttribute($parameters, $object->first_post, $url, $mode));
				break;
			case self::$namespace . "Post" :
				self::assignAuthorInPost($object);
				if(isset($object->author)){
					$author = $object->author;
					$author_name = "undefined";
					if(isset($author)){
						$author_name = $author->login;
					}
					$parameters["author_name"] = $author_name;
					$parameters["author_link"]
						= Forum_Url_Utils::getUrl($author_name, Forum_Url_Utils::getBaseUrl("author"));
				}
				$parameters["content"] = $object->content;
				$parameters["date_post"] = date('H:i:s l j F', $object->date_post);
				if(strtolower($mode) == "output")
					$parameters["content"] = Forum_Utils::contentFormatting($parameters["content"]);
				if($object->nb_edited){
					$parameters["nb_edited"] = $object->nb_edited;
					$parameters["last_edited_by"] = $object->last_edited_by;
					$parameters["last_edited"] = date('l j F \|\o\n\| H:i:s', $object->last_edited);
				}
				break;
			default:
				$parameters["title"] = "Index";
				break;
		}
		if($object && isset($object->title))
			$parameters["title"] = $object->title;
		$parameters = self::getAttributeCol($object, $parameters);
		$parameters["attributes_number"] = count($parameters["attribute_values"]) + 1;
		$parameters["buttons"] = Forum_Buttons_Utils::getButtons($object, $base_url, $mode);
		$parameters["type"] = Namespaces::shortClassName($class_name);
		$parameters["type_child"] = Namespaces::shortClassName(Forum_Names_Utils::getNextClass($class_name));
		$parameters["id"] = Dao::getObjectIdentifier($object);
		return $parameters;
	}

	public static function assignAuthorInPost($post)
	{
		return self::assignAttributeObjectInElement($post, "author", get_class(User::current()));
	}

	//---------------------------------------------------------------- assignAttributeObjectInElement
	/**
	 * Search and assign to the element his object attribute. Put a new object if the object not found.
	 * If has already an object, no change.
	 * @param $element
	 * @param $attribute
	 * @param $class_name
	 * @return mixed
	 */
	public static function assignAttributeObjectInElement($element, $attribute, $class_name)
	{
		$attribute_id = "id_" . $attribute;
		if($element->$attribute == null){
			$object = null;
			if(isset($element->$attribute_id))
				$object = Dao::read($element->$attribute_id, $class_name);
			if($object == null){
				$object = new $class_name();
				unset($element->$attribute_id);
			}
			$element->$attribute = $object;
		}
		return $element;
	}

	//-------------------------------------------------------------------------- assignTopicFirstPost
	/**
	 * Search and assign to the topic his first post value. Put a new object if the post not found.
	 * If has already a first post, no change.
	 * @param $topic object A topic.
	 * @return Topic Return the topic with his first post
	 */
	public static function assignTopicFirstPost($topic)
	{
		return self::assignAttributeObjectInElement($topic, "first_post", "SAF\\Wiki\\Post");
	}

	//----------------------------------------------------------------------------- contentFormatting
	/**
	 * Format a string for visualization, with textile.
	 * @param $content string
	 * @return string
	 */
	public static function contentFormatting($content){
		return Wiki::textile($content);
	}

	//------------------------------------------------------------------------------- generateContent
	/**
	 * Assign the parameter
	 * @param $parameters   array
	 * @param $from         null|string|object The class name if it's on parameters, or the object.
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
		if($from != null && !is_object($from)){
			if(isset($parameters[$from]))
				$from = $parameters[$from];
			elseif(isset($path[$from]))
				$from = $path[$from];
		}
		$path = Forum_Path_Utils::stopInLevel($path, $from);
		$base_url = Forum_Url_Utils::getBaseUrl($path);
		if($level_max == -1)
			$level_max = $level_number;
		if($level_number){
			$level_number--;
			$level_name = self::getLevelName($level_number, $level_max);
			$block_elements = self::getNextElements($from);

			$blocks = array();
			if(is_array($block_elements)){
				foreach($block_elements as $block_element){
					$url = Forum_Url_Utils::getUrl($block_element, $base_url);
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
		/** @var $from Category|Forum|Topic|Post */
		$parameters = self::addAttribute($parameters, $from, $base_url, $mode);
		$parameters = Forum_Path_Utils::addPathAttribute($parameters, $path);
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
		$class_name = get_class($object);
		$next_class = Forum_Names_Utils::getNextClass($class_name);
		switch(get_class($object)){
			case self::$namespace . "Category" :
				$parameters = self::getAttributeNameCol($class_name, $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol($next_class, $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => self::getNbForums($object));
				$parameters[$value_var_name][] = array("value" => self::getNbTopics($object));
				$parameters[$value_var_name][] = array("value" => self::getNbPosts($object));
				break;
			case self::$namespace . "Forum" :
				$parameters = self::getAttributeNameCol($class_name, $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol($next_class, $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => self::getNbTopics($object));
				$parameters[$value_var_name][] = array("value" => self::getNbPosts($object));
				break;
			case self::$namespace . "Topic" :
				$parameters = self::getAttributeNameCol($class_name, $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol($next_class, $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => self::getNbPosts($object));
				break;
			case self::$namespace . "Post" :
				break;
			default:
				break;
		}
		return $parameters;
	}

	//----------------------------------------------------------------------------------- getNbForums
	/**
	 * @param $object object
	 * @return int
	 */
	public static function getNbForums($object)
	{
		if(isset($object->nb_forums))
			return $object->nb_forums;
		return 0;
	}

	//----------------------------------------------------------------------------------- getNbTopics
	/**
	 * @param $object object
	 * @return int
	 */
	public static function getNbTopics($object)
	{
		if(isset($object->nb_topics))
			return $object->nb_topics;
		return 0;
	}

	//----------------------------------------------------------------------------------- getNbPosts
	/**
	 * @param $object object
	 * @return int
	 */
	public static function getNbPosts($object)
	{
		if(isset($object->nb_posts))
			return $object->nb_posts;
		return 0;
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
			case self::$namespace . "Category" :
				$parameters[$var_name][] = array("value" => "Forums");
				$parameters[$var_name][] = array("value" => "Topics");
				$parameters[$var_name][] = array("value" => "Posts");
				break;
			case self::$namespace . "Forum":
				$parameters[$var_name][] = array("value" => "Topics");
				$parameters[$var_name][] = array("value" => "Posts");
				break;
			case self::$namespace . "Topic" :
				$parameters[$var_name][] = array("value" => "Posts");
				break;
			case self::$namespace . "Post" :
				break;
			default:
				break;
		}
		return $parameters;
	}

	//--------------------------------------------------------------------------------- getCategories
	/**
	 * Return all Categories
	 * @return Category
	 */
	public static function getCategories()
	{
		return Dao::readAll(self::$namespace . "Category");
	}

	//------------------------------------------------------------------------------------ getElement
	/**
	 * Return an element with his title in function of a parent element.
	 * @param $parent object|null Parent element, as Category, Forum, Topic.
	 * If the parent is null, the children element is a Category.
	 * @param $title  string Title of the object
	 * @param $search_in_dao boolean Determine if search in dao or not,
	 * allow get default value when it is at false.
	 * @return object Return the element.
	 */
	public static function getElement($parent, $title, $search_in_dao = true)
	{
		$item = null;
		$search = true;
		switch(get_class($parent)){
			case self::$namespace . "Category" :
				$item = new Forum();
				$item->category = $parent;
				$item->title = $title;
				break;
			case self::$namespace . "Forum" :
				$item = new Topic();
				$item->forum = $parent;
				$item->title = $title;
				break;
			case self::$namespace . "Topic" :
				$item = new Post();
				$search = false;
				break;
			case self::$namespace . "Post" :
				$item = $parent;
				$search = false;
				break;
			default:
				$item = new Category();
				$item->title = $title;
				break;
		}
		if($search && $search_in_dao)
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
			$search = null;
			switch(strtolower($key)){
				case "post":
					$search = new Post();
					break;
				case "topic":
					$search = new Topic();
					break;
				case "forum":
					$search = new Forum();
					break;
				case "category":
					$search = new Category();
					break;
			}
			if(isset($search)){
				$element = Dao::read($getter, get_class($search));
				return ($element ? $element : $search);
			}
		}
		return null;
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
		$answer = array("element" => null, "path" => array(), "lost_chain" => false);
		$level = 0;
		$lost_chain = false;
		if(func_get_args()){
			foreach(func_get_args() as $arg){
				if(is_array($arg)){
					foreach($arg as $item){
						$answer = self::getElementsRequiredItem($answer, $parent, $item, $level, $lost_chain);
						$parent = $answer["element"];
						$lost_chain = $answer["lost_chain"];
						$level++;
					}
				}
				else if(is_string($arg)){
					$answer = self::getElementsRequiredItem($answer, $parent, $arg, $level, $lost_chain);
					$parent = $answer["element"];
					$level++;
				}
				else if(is_object($arg)){
					if(self::isEmpty($arg)){
						$arg = self::setParentObject($arg, $parent);
					}
					$answer["element"] = $arg;
					$answer["path"][Forum_Names_Utils::getClassInLevel($level)] = $arg;
					$parent = $arg;
					$level++;
				}
			}
		}
		return $answer;
	}

	public static function getElementsRequiredItem($answer, $parent, $item, $level)
	{
		$element = self::getElement($parent, $item, !$answer["lost_chain"]);
		$answer["element"] = $element;
		$answer["path"][Forum_Names_Utils::getClassInLevel($level)] = $element;
		if(self::isNotFound($element))
			$answer["lost_chain"] = true;
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
	 * @param $object object The parent object
	 * @return null|object[]
	 */
	public static function getNextElements($object)
	{
		$class_name = get_class($object);
		$new_class_name = Forum_Names_Utils::getNextShortClass($class_name);
		$name = Forum_Names_Utils::getPluralName($new_class_name);
		$method_name = "get" . $name;
		if(method_exists(self::$namespace . 'Forum_Utils', $method_name)){
			return self::$method_name($object);
		}
		else if($object == null){
			$method_name = "get" . Forum_Names_Utils::getPluralName(Forum_Names_Utils::getFirstClass());
			return self::$method_name($object);
		}
		return null;
	}

	//---------------------------------------------------------------------------- getObjectDepending
	/**
	 * Allow to specify objects dependencies.
	 * @param $object object
	 * @return array
	 */
	public static function getObjectDepending($object)
	{
		$list_objects = array();
		switch(get_class($object)){
			case self::$namespace . "Topic":
				$object = self::assignTopicFirstPost($object);
				$list_objects[] = $object->first_post;
		}
		return $list_objects;
	}

	//------------------------------------------------------------------------------- getParentObject
	/**
	 * Change the refer to the parent object.
	 * @param $object object The object
	 * @return object Return the parent object
	 */
	public static function getParentObject($object)
	{
		$attribute_parent = self::getParentObjectAttribute($object);
		if(property_exists($object, $attribute_parent))
			return $object->$attribute_parent;
		return null;
	}

	//------------------------------------------------------------------------------- getParentObject
	/**
	 * Return the parent attribute name of an object
	 * @param $object object The object
	 * @return string Return the attribute name
	 */
	public static function getParentObjectAttribute($object)
	{
		$attribute_parent = Forum_Names_Utils::getParentShortClass($object);
		$attribute_parent = strtolower($attribute_parent);
		return $attribute_parent;
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

	/**
	 * Test if the attribute of an object is not void/null.
	 * Don't test if the object exist.
	 * @param $element object The element which must contains the attribute
	 * @param $attribute string The attribute name
	 * @return boolean True if has an object or object id, false if has no object (null).
	 */
	public static function hasElementAtAttribute($element, $attribute)
	{
		$attribute_id = "id_" . $attribute;
		if(isset($element->$attribute) && $element->$attribute != null)
			return true;
		if(isset($element->$attribute_id) && $element->$attribute_id != 0)
			return true;
		return false;
	}

	//--------------------------------------------------------------------------------------- isEmpty
	/**
	 * Test if an object is empty or not
	 * @param $object object
	 * @return bool
	 */
	public static function isEmpty($object)
	{
		foreach(get_object_vars($object) as $attribute){
			if(isset($attribute))
				return false;
		}
		return true;
	}

	//------------------------------------------------------------------------------------ isNotFound
	/**
	 * Test if an object is not found when it has search.
	 * @param $object object
	 * @return bool
	 */
	public static function isNotFound($object)
	{
		$identifier = Dao::getObjectIdentifier($object);
		return !isset($identifier);
	}

	//------------------------------------------------------------------------------- setParentObject
	/**
	 * Change the refer to the parent object.
	 * @param $object object The object
	 * @param $parent object|null The new parent element
	 * @return object Return the object changed
	 */
	public static function setParentObject($object, $parent)
	{
		$attribute_parent = self::getParentObjectAttribute($object);
		if(property_exists($object, $attribute_parent))
			$object->$attribute_parent = $parent;
		return $object;
	}

}
