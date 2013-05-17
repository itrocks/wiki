<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;
use SAF\Framework\Names;
use SAF\Framework\Search_Object;
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
					$parameters["last_edited"] = self::getDate($object->last_edited);
				}
				break;
			default:
				$parameters["title"] = "Index";
				break;
		}
		$parameters["title"] = self::getTitle($object);
		$parameters = self::getAttributeCol($object, $parameters);
		$parameters["attributes_number"] = count($parameters["attribute_values"]) + 2;
		if(isset($object))
			$parent_url = Forum_Url_Utils::getParentUrl($base_url);
		else
			$parent_url = $base_url;
		$parameters["buttons"] = Forum_Buttons_Utils::getButtons($object, $parent_url, $mode);
		$parameters["bottom_buttons"] = Forum_Buttons_Utils::getBottomButtons($object, $parent_url, $mode);
		$parameters["type"] = Namespaces::shortClassName($class_name);
		$parameters["type_child"] =
			Namespaces::shortClassName(Forum_Names_Utils::getNextClass($class_name));
		$parameters["id"] = Dao::getObjectIdentifier($object);
		return $parameters;
	}

	//---------------------------------------------------------------------------- assignAuthorInPost
	/**
	 * @param $post Post
	 * @return mixed
	 */
	public static function assignAuthorInPost($post)
	{
		return self::assignAttributeObjectInElement
			($post, "author", get_class(Search_Object::create("SAF\\Framework\\User")));
	}

	//---------------------------------------------------------------- assignAttributeObjectInElement
	/**
	 * Search and assign to the element his object attribute. Put a new object if the object not found.
	 * If has already an object, no change.
	 * @param $element
	 * @param $attribute
	 * @param $class_name
	 * @param $initialize_value boolean True if the value must be initialize by new object if not found,
	 * false else.
	 * @return mixed
	 */
	public static function assignAttributeObjectInElement(
		$element, $attribute, $class_name, $initialize_value = true
	)	{
		if($element->$attribute == null){
			$attribute_id = "id_" . $attribute;
			$object = null;
			if(isset($element->$attribute_id))
				$object = Dao::read($element->$attribute_id, $class_name);
			if($object == null && $initialize_value){
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
		return self::assignAttributeObjectInElement($topic, "first_post", self::$namespace . "Post");
	}

	//-------------------------------------------------------------------------- assignTopicFirstPost
	/**
	 * @param $object object An object.
	 * @return Topic Return the topic with his last post
	 */
	public static function assignLastPost($object)
	{
		return self::assignAttributeObjectInElement($object, "last_post", self::$namespace . "Post");
	}

	//----------------------------------------------------------------------------- contentFormatting
	/**
	 * Format a string for visualization, with textile.
	 * @param $content string
	 * @return string
	 */
	public static function contentFormatting($content)
	{
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
	)	{
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
		switch($class_name){
			case self::$namespace . "Category" :
				/** @var $object Category */
				$parameters = self::getAttributeNameCol($class_name, $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol($next_class, $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => self::getNbForums($object));
				$parameters[$value_var_name][] = array("value" => self::getNbTopics($object));
				$parameters[$value_var_name][] = array("value" => self::getNbPosts($object));
				break;
			case self::$namespace . "Forum" :
				/** @var $object Forum */
				$parameters = self::getAttributeNameCol($class_name, $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol($next_class, $title_var_name, $parameters);
				$parameters[$value_var_name][] = self::getLastPostAttribute($object);
				$parameters[$value_var_name][] = array("value" => self::getNbTopics($object));
				$parameters[$value_var_name][] = array("value" => self::getNbPosts($object));
				break;
			case self::$namespace . "Topic" :
				/** @var $object Topic */
				$parameters = self::getAttributeNameCol($class_name, $title_parent_var_name, $parameters);
				$parameters = self::getAttributeNameCol($next_class, $title_var_name, $parameters);
				$parameters[$value_var_name][] = self::getLastPostAttribute($object);
				$parameters[$value_var_name][] = array("value" => self::getNbPosts($object));
				break;
			case self::$namespace . "Post" :
				break;
			default:
				break;
		}
		return $parameters;
	}

	//--------------------------------------------------------------------------- getAttributeNameCol
	/**
	 * Assign columns names in parameters.
	 * @param $class_name string The short class name, as Category, Forum, Topic or Post
	 * @param $var_name string The var name used for parameters.
	 * @param $parameters array The parameters where put
	 * @return mixed parameters
	 */
	public static function getAttributeNameCol($class_name, $var_name, $parameters)
	{
		switch($class_name){
			case self::$namespace . "Category" :
				$parameters[$var_name][] = "Forums";
				$parameters[$var_name][] = "Topics";
				$parameters[$var_name][] = "Posts";
				break;
			case self::$namespace . "Forum":
				$parameters[$var_name][] = "Last post";
				$parameters[$var_name][] = "Topics";
				$parameters[$var_name][] = "Posts";
				break;
			case self::$namespace . "Topic" :
				$parameters[$var_name][] = "Last post";
				$parameters[$var_name][] = "Posts";
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

	//--------------------------------------------------------------------------------------- getDate
	/**
	 * Return timestamp put in parameters to default forum display format.
	 * @param $time int|null
	 * @return string The date formatted
	 */
	public static function getDate($time = null)
	{
		return date('j-m-y H:i', ($time !== null ? $time : time()));
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
					if(!isset($parent) || Forum_Names_Utils::isAParent($arg, $parent)){
						$parent_calibration = $parent;
						$child = $arg;
						$objects_child = array();
						while(get_class($child) != Forum_Names_Utils::getNextClass($parent_calibration)){
							$child = Forum_Utils::getParentObjectSome($child);
							$objects_child[] = $child;
							$level++;
						}
						$objects_child = array_reverse($objects_child);
						foreach($objects_child as $object){
							$answer["path"][Namespaces::shortClassName(get_class($object))] = $object;
						}
					}
					if(self::isEmpty($arg)){
						$arg = self::setParentObject($arg, $parent);
					}
					$answer["element"] = $arg;
					$answer["path"][Namespaces::shortClassName(get_class($arg))] = $arg;
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

	//----------------------------------------------------------------------------------- getLastPost
	/**
	 * Return the last post.
	 * @param $object object
	 * @return Post
	 */
	public static function getLastPost($object)
	{
		switch(get_class($object)){
			case self::$namespace . "Topic":
				/** @var $object Topic  */
				$posts = self::getPosts($object);
				$last_post = end($posts);
				if(!isset($last_post) || $last_post === false){
					$last_post = self::assignTopicFirstPost($object)->first_post;
				}
				return $last_post;
			case self::$namespace . "Forum":
				/** @var $object Forum  */
				$topics = self::getTopics($object);
				/** @var $last_post Post */
				$last_post = null;
				foreach($topics as $topic){
					$post = self::getLastPost($topic);
					if(isset($post->date_post) &&
						$post->date_post > ($last_post != null ? $last_post->date_post : 0))
						$last_post = $post;
				}
				return $last_post;
		}
		return new Post();
	}

	//-------------------------------------------------------------------------- getLastPostAttribute
	/**
	 * Return the text field of last post attribute.
	 * @param $object object
	 * @return array
	 */
	public static function getLastPostAttribute($object)
	{
		$last_post = self::getLastPost($object);
		if(is_object($last_post)){
			$date = self::getDate($last_post->date_post);
			$last_post = self::assignAuthorInPost($last_post);
			$link = Forum_Url_Utils::findUrl($last_post, true);
			return
				array("label" => $date, "value" => " |by| " . $last_post->author->login, "link" => $link);
		}
		return "";
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

	//----------------------------------------------------------------------------------- getNbForums
	/**
	 * @param $object object
	 * @return int
	 */
	public static function getNbForums($object)
	{
		return count(self::getNextElements($object));
	}

	//----------------------------------------------------------------------------------- getNbTopics
	/**
	 * @param $object object
	 * @return int
	 */
	public static function getNbTopics($object)
	{
		return count(self::getNextElements($object));
	}

	//----------------------------------------------------------------------------------- getNbPosts
	/**
	 * @param $object object
	 * @return int
	 */
	public static function getNbPosts($object)
	{
		return count(self::getNextElements($object));
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
	 * Return the parent object if exist.
	 * @param $object object The object
	 * @return object Return the parent object
	 */
	public static function getParentObject($object)
	{
		$attribute_parent = self::getParentObjectAttribute($object);
		if(property_exists($object, $attribute_parent)){
			$object = self::assignAttributeObjectInElement
				($object, $attribute_parent, Forum_Names_Utils::getParentClass($object), false);
			return $object->$attribute_parent;
		}
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

	//--------------------------------------------------------------------------- getParentObjectSome
	/**
	 * Get the parent object, search in particular case or initialize value,
	 * but make sure to return an object, except if the object is at the root or not recognize.
	 * @param $object object The object
	 * @return object Return the parent object
	 */
	public static function getParentObjectSome($object)
	{
		$parent_object = self::getParentObject($object);
		if(!isset($parent_object) && get_class($object) == self::$namespace . "Post"){
			$search = new Topic();
			$search->first_post = $object;
			$parent_object = Dao::searchOne($search);
		}
		if(!isset($parent_object)){
			$parent_class = Forum_Names_Utils::getParentClass($object);
			if($parent_class != "")
				$parent_object = new $parent_class();
		}
		return $parent_object;
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

	//-------------------------------------------------------------------------------------- getTitle
	/**
	 * @param $object object
	 * @return mixed
	 */
	public static function getTitle($object){
		if(isset($object->title))
			return $object->title;
		else
			return "";
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

	//--------------------------------------------------------------------- isEqualAttributeAndObject
	/**
	 * Test if an object in source at the attribute name has the same id
	 * as the second object in parameters.
	 * @param $source         object The source object, must have the attribute_name
	 * @param $attribute_name string The attribute name of the object to test from the source object.
	 * @param $object         object The object to compare.
	 * @return bool
	 */
	public static function isEqualAttributeAndObject($source, $attribute_name, $object)
	{
		$id = Dao::getObjectIdentifier($object);
		$id_attribute_name = "id_" . $attribute_name;
		return isset($source->$attribute_name)
			&& $id == Dao::getObjectIdentifier($source->$attribute_name)
			|| isset($source->$id_attribute_name)
			&& $id == $source->$id_attribute_name;
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

	//---------------------------------------------------------------------------- setObjectAttribute
	/**
	 * @param $source     object
	 * @param $attribute  string
	 * @param $new_object object
	 * @return object
	 */
	public static function setObjectAttribute($source, $attribute, $new_object)
	{
		if(property_exists($source, $attribute)){
			$attribute_id = "id_" . $attribute;
			$source->$attribute = $new_object;
			unset($source->$attribute_id);
		}
		return $source;
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
		self::setObjectAttribute($object, $attribute_parent, $parent);
		return $object;
	}

}
