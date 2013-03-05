<?php
namespace SAF\Wiki;
use SAF\Framework\Dao;

class Forum_Utils
{
	public static function getElementsRequired()
	{
		$parent = null;
		$answer = array("element" => null, "path" => array());
		$level = 0;
		if(func_get_args()){
			foreach(func_get_args() as $arg){
				$element = self::getElement($parent, $arg);
				if(!$element)
					break;
				$answer["element"] = $element;
				$answer["path"][self::getClassInLevel($level)] = $element;
				$parent = $element;
				$level++;
			}
		}
		return $answer;
	}

	public static function generateContent($parameters, $from, $base_url, $level_number = 1, $level_max = -1)
	{
		if($level_max == -1)
			$level_max = $level_number;
		if($level_number){
			$level_number--;
			$level_name = self::getLevelName($level_number, $level_max);
			$parameters = self::addAttribute($parameters, $from);
			$block_elements = self::getNextElements($from);

			$blocks = array();
			foreach($block_elements as $block_element){
				$url = self::getUrl($block_element->title, $base_url);
				$block = array(
					"link" => $url
				);
				$block = self::generateContent($block, $block_element, $url, $level_number, $level_max);
				$block = self::addAttribute($block, $block_element, $level_max - $level_number == 1);
				$blocks[] = $block;
			}
			$parameters[$level_name] = $blocks;
		}
		return $parameters;
	}

	public static function getCategories()
	{
		return Dao::readAll("SAF\\Wiki\\Category");
	}

	public static function getForums($category)
	{
		$search = new Forum();
		$search->category = $category;
		/** @var $forums Forum[] */
		$forums = Dao::search($search);
		return $forums;
	}

	public static function getTopics($forum)
	{
		$search = new Topic();
		$search->forum = $forum;
		/** @var $forums Topic[] */
		$forums = Dao::search($search);
		return $forums;
	}

	public static function getPosts($topic)
	{
		$search = new Post();
		$search->topic = $topic;
		/** @var $forums Post[] */
		$forums = Dao::search($search);
		return $forums;
	}

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

	/**
	 * Form an url, an put in right format.
	 * @param $element string The element destination for the url
	 * @param $base_url null|string The base url, if not indicated or null, use getBaseUrl()
	 * @return mixed The url
	 */
	public static function getUrl($element, $base_url = null)
	{
		if($base_url == null)
			$base_url = self::getBaseUrl();
		$url = $base_url . $element . "/";
		$url = str_replace(" ", "%20", $url);
		return $url;
	}

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

	/**
	 * Return an element with his title in function of a parent element.
	 * @param $parent object|null Parent element, as Category, Forum, Topic.
	 * If the parent is null, the children element is a Category.
	 * @param $title  string Title of the object
	 * @return object Return the element.
	 */
	public static function getElement($parent, $title)
	{
		switch(get_class($parent)){
			case "SAF\\Wiki\\Category" :
				$forum = new Forum();
				$forum->category = $parent;
				$forum->title = $title;
				$element = Dao::searchOne($forum);
				break;
			case "SAF\\Wiki\\Forum" :
				$topic = new Topic();
				$topic->forum = $parent;
				$topic->title = $title;
				$element = Dao::searchOne($topic);
				break;
			case "SAF\\Wiki\\Topic" :
				$post = new Post();
				$post->topic = $parent;
				$post->title = $title;
				$element = Dao::searchOne($post);
				break;
			case "SAF\\Wiki\\Post" :
				$element = $parent;
				break;
			default:
				$category = new Category();
				$category->title = $title;
				$element = Dao::searchOne($category);
				break;
		}
		return $element;
	}

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

	/**
	 * Return attributes col.
	 * @param $object     object It's a Category, Forum, Topic or Post object
	 * @param $parameters array The parameters where put
	 * @return mixed Parameters
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

	/**
	 * @param $parameters array The parameters where put
	 * @param $object     object It's a Category, Forum, Topic or Post object
	 * @return mixed
	 */
	public static function addAttribute($parameters, $object)
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
		return $parameters;
	}

	/**
	 * Return a name for a block's level
	 * @param $level_now string current level
	 * @param $level_max string level max
	 * @return string A level name
	 */
	private static function getLevelName($level_now, $level_max)
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
}
