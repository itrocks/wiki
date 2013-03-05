<?php
namespace SAF\Wiki;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\List_Controller;
use SAF\Framework\Dao;
use SAF\Framework\User;
use SAF\Framework\View;

class Forum_Controller extends List_Controller
{
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters = $this->generateContent($parameters, null, $this->getBaseUrl(), 3);
		return View::run($parameters, $form, $files, $class_name, "structure_double");
	}

	public function category(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = $this->getElementsRequired($parameters[0]);
		$base_url = $this->getBaseUrl($answer["path"]);
		$parameters = $this->generateContent($parameters, $answer["element"], $base_url, 2);
		$parameters["path"] = $answer["path"];
		return View::run($parameters, $form, $files, $class_name, "structure_double");
	}

	public function forum(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = $this->getElementsRequired($parameters[0], $parameters[1]);
		$base_url = $this->getBaseUrl($answer["path"]);
		$parameters = $this->generateContent($parameters, $answer["element"], $base_url, 1);
		return View::run($parameters, $form, $files, $class_name, "structure_simple");
	}

	public function topic(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$answer = $this->getElementsRequired($parameters[0], $parameters[1], $parameters[2]);
		$base_url = $this->getBaseUrl($answer["path"]);
		$parameters = $this->generateContent($parameters, $answer["element"], $base_url, 1);
		return View::run($parameters, $form, $files, $class_name, "output_topic");
	}

	public function getElementsRequired(){
		$parent = null;
		$answer = array("element" => null, "path" => array());
		$level = 0;
		if(func_get_args()){
			foreach(func_get_args() as $arg){
				$element = $this->getElement($parent, $arg);
				if(!$element)
					break;
				$answer["element"] = $element;
				$answer["path"][$this->getClassInLevel($level)] = $element;
				$parent = $element;
				$level++;
			}
		}
		return $answer;
	}

	public function generateContent($parameters, $from, $base_url, $level_number = 1, $level_max = -1)
	{
		if($level_max == -1)
			$level_max = $level_number;
		if($level_number){
			$level_number--;
			$level_name = $this->getLevelName($level_number, $level_max);
			$parameters = $this->addAttribute($parameters, $from);
			$block_elements = $this->getNextElements($from);

			$blocks = array();
			foreach($block_elements as $block_element){
				$url = $this->getUrl($block_element->title, $base_url);
				$block = array(
					"link" => $url
				);
				$block = $this->generateContent($block, $block_element, $url, $level_number, $level_max);
				$block = $this->addAttribute($block, $block_element, $level_max - $level_number == 1);
				$blocks[] = $block;
			}
			$parameters[$level_name] = $blocks;
		}
		return $parameters;
	}

	public function getCategories()
	{
		return Dao::readAll("SAF\\Wiki\\Category");
	}

	public function getForums($category)
	{
		$search = new Forum();
		$search->category = $category;
		/** @var $forums Forum[] */
		$forums = Dao::search($search);
		return $forums;
	}

	public function getTopics($forum)
	{
		$search = new Topic();
		$search->forum = $forum;
		/** @var $forums Topic[] */
		$forums = Dao::search($search);
		return $forums;
	}

	public function getPosts($topic)
	{
		$search = new Post();
		$search->topic = $topic;
		/** @var $forums Post[] */
		$forums = Dao::search($search);
		return $forums;
	}

	public function getBaseUrl()
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

	public function getUrl($element, $base_url = null){
		if($base_url == null)
			$base_url = $this->getBaseUrl();
		$url = $base_url . $element . "/";
		$url = str_replace(" ", "%20", $url);
		return $url;
	}

	public function getNextElements($object)
	{
		switch(get_class($object)){
			case "SAF\\Wiki\\Category" :
				return $this->getForums($object);
			case "SAF\\Wiki\\Forum" :
				return $this->getTopics($object);
			case "SAF\\Wiki\\Topic" :
				return $this->getPosts($object);
			case "SAF\\Wiki\\Post" :
				return null;
			default:
				return $this->getCategories();
		}
	}

	public function getElement($parent, $title)
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

	public function getClassInLevel($level)
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

	public function getAttributeNameCol($short_class, $var_name, $attributes){
		switch($short_class){
			case "Category" :
				$attributes[$var_name][] = array("value" => "Number of forums");
				$attributes[$var_name][] = array("value" => "Number of topics");
				$attributes[$var_name][] = array("value" => "Number of posts");
				break;
			case "Forum" :
				$attributes[$var_name][] = array("value" => "Number of topics");
				$attributes[$var_name][] = array("value" => "Number of posts");
				break;
			case "Topic" :
				$attributes[$var_name][] = array("value" => "Number of posts");
				break;
			case "Post" :
				break;
			default:
				break;
		}
		return $attributes;
	}

	public function getAttributeCol($object, $parameters){
		$title_parent_var_name = "attribute_titles_parent";
		$title_var_name = "attribute_titles";
		$value_var_name = "attribute_values";
		$parameters[$title_parent_var_name] = array();
		$parameters[$title_var_name] = array();
		$parameters[$value_var_name] = array();
		switch(get_class($object)){
			case "SAF\\Wiki\\Category" :
				$parameters = $this->getAttributeNameCol("Category", $title_parent_var_name, $parameters);
				$parameters = $this->getAttributeNameCol("Forum", $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => 1);
				$parameters[$value_var_name][] = array("value" => 2);
				$parameters[$value_var_name][] = array("value" => 3);
				break;
			case "SAF\\Wiki\\Forum" :
				$parameters = $this->getAttributeNameCol("Forum", $title_parent_var_name, $parameters);
				$parameters = $this->getAttributeNameCol("Topic", $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => 4);
				$parameters[$value_var_name][] = array("value" => 5);
				break;
			case "SAF\\Wiki\\Topic" :
				$parameters = $this->getAttributeNameCol("Forum", $title_parent_var_name, $parameters);
				$parameters = $this->getAttributeNameCol("Post", $title_var_name, $parameters);
				$parameters[$value_var_name][] = array("value" => 4);
				break;
			case "SAF\\Wiki\\Post" :
				break;
			default:
				break;
		}
		return $parameters;
	}
	public function addAttribute($parameters, $object, $is_header = false)
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
				$author = \SAF\Framework\Search_Object::newInstance('Saf\\Wiki\\Wiki_User');
				$author = Dao::read($object->id_author, get_class($author));
				$parameters["content"] = $object->content;
				$parameters["author_name"] = $author->login;
				$parameters["author_link"] = $this->getBaseUrl("author") . $parameters["author_name"];
				$parameters["type"] = "Post";
				break;
			default:
				break;
		}
		if($object)
			$parameters["title"] = $object->title;
		$parameters = $this->getAttributeCol($object, $parameters);
		$parameters["attributes_number"] = count($parameters["attribute_values"]) + 1;
		return $parameters;
	}

	private function getLevelName($level_now, $level_max)
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
