<?php
namespace SAF\Wiki;
use SAF\Framework\Builder;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Output_Controller;
use SAF\Framework\Search_Object;
use SAF\Framework\View;
use SAF\Framework\Dao;
use SAF\Framework\Default_List_Data;
use SAF\Framework\Default_List_Row;

class Forum_Search_Advanced_Controller extends Output_Controller
{

	public static $search_option = array(
		"Topic title"   => array("class" => "SAF\\Wiki\\Topic", "attribute" => "title",
		                          "author" => "first_post->author"),
		"Topic content" => array("class" => "SAF\\Wiki\\Topic", "attribute" => "first_post->content",
		                          "author" => "first_post->author"),
		"Post content"  => array("class" => "SAF\\Wiki\\Post", "attribute" => "content",
		                          "author" => "author"),
	);

	public static $cache = array();

	public static $criteria_list = array(
		"search" => array("name" => "search", "label" => "Keyword search"),
		"author_filter" => array("name" => "author_filter", "label" => "Author search")
	);




	//------------------------------------------------------------------------------- chargeLocations
	/**
	 * @param $locations array An array of Forum's id
	 * @return array|bool
	 */
	public function chargeLocations($locations)
	{
		$new_locations = array();
		if(count($locations) == 0)
			return true;
		foreach($locations as $location){
			$new_locations[] = Dao::read($location, "SAF\\Wiki\\Forum");
		}
		return $new_locations;
	}

	//--------------------------------------------------------------------------------- chargeOptions
	/**
	 * Found option selected and put his params in an array.
	 * If not valid search option selected, return all option.
	 * @param $search_option array The list of option label
	 * @return array
	 */
	public function chargeOptions($search_option)
	{
		$objects = array();
		foreach($search_option as $option){
			if(isset(self::$search_option[$option])){
				$objects[$option] = self::$search_option[$option];
			}
		}
		if(count($objects) == 0){
			$objects = self::$search_option;
		}
		return $objects;
	}

	//----------------------------------------------------------------------------------------- exist
	/**
	 * Test if an element exist and is not void.
	 * @param $element string
	 * @return bool
	 */
	private static function exist($element)
	{
		return isset($element) && str_replace(" ", "", $element) != "";
	}

	//------------------------------------------------------------------------------- getCriteriaList
	/**
	 * Return the list of criteria
	 * @return array
	 */
	public function getCriteriaList()
	{
		return self::$criteria_list;
	}

	//---------------------------------------------------------------------- getPossibleSearchOptions
	/**
	 * Return the list of search possible option
	 * @return array
	 */
	public function getPossibleSearchOptions()
	{
		$names_options = array();
		foreach(self::$search_option as $option => $param)
		{
			$names_options[] = $option;
		}
		return $names_options;
	}

	//--------------------------------------------------------------------- getPossibleSearchLocation
	/**
	 * Return the list of forums, short by category, and the category's name.
	 * @return array
	 */
	public function getPossibleSearchLocation()
	{
		$categories = Forum_Utils::getCategories();
		$names_location = array();
		foreach($categories as $category){
			$forums = Forum_Utils::getForums($category);
			$items = array();
			foreach($forums as $forum){
				$items[] = array("value" => $forum->title, "key" => Dao::getObjectIdentifier($forum));
			}
			$names_location[$category->title] =
				array("value" => $category->title, "search_location_items" => $items);
		}
		return $names_location;
	}

	//---------------------------------------------------------------------------------------- output
	/**
	 * Default output, return the complete form.
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function output(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		$parameters["search_location"] = $this->getPossibleSearchLocation();
		$parameters["search_options"] = $this->getPossibleSearchOptions();
		$parameters["criteria_list"] = $this->getCriteriaList();
		return View::run($parameters, $form, $files, "Forum_Search_Advanced", "output");
	}

	//------------------------------------------------------------------------------- putInParameters
	/**
	 * Put search results in parameters.
	 * @param $parameters
	 * @param $search
	 * @return mixed
	 */
	public function putInParameters($parameters, $search)
	{
		foreach($search as $object){
			$params = array();
			if(get_class($object) == "SAF\\Wiki\\Topic"){
				$topic = $object;
				$topic = Forum_Utils::assignTopicFirstPost($topic);
				$content = $topic->first_post->content;
				$object = null;
			}
			else {
				$topic = Forum_Utils::getParentObject($object);
				if(!isset($topic)){
					$topic = new Topic();
					$topic->first_post = $object;
					$topic = Dao::searchOne($topic);
				}
				$content = $object->content;
			}
			$forum = Forum_Utils::getParentObject($topic);
			$category = Forum_Utils::getParentObject($forum);
			$url = Forum_Url_Utils::getBaseUrl($category, $forum, $topic);
			$url = Forum_Url_Utils::getUrl("", $url, array(), false, $object);
			$params["link"] = $url;
			$params["title"] = $topic->title;
			$attributes_values = array(substr($content, 0, 100) . "...");
			$params = Forum_Utils::getAttributeCol($topic, $params);
			$params["attribute_values"] = array_merge($attributes_values, $params["attribute_values"]);
			$parameters["topics"][] = $params;
		}
		$attributes_values = array("preview");
		$parameters = Forum_Utils::getAttributeNameCol("SAF\\Wiki\\Topic", "attribute_titles", $parameters);
		$parameters["attribute_titles"] = array_merge($attributes_values, $parameters["attribute_titles"]);
		return $parameters;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * The controller search and print results if $form["search"] exist,
	 * else print search field.
	 * @param $parameters   Controller_Parameters
	 * @param $form         array
	 * @param $files        array
	 * @param $class_name   string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$search_value = null;
		$search_author = null;
		if(isset($form["search"]))
			$search_value = $form["search"];
		if(isset($form["author_filter"]))
			$search_author = $form["author_filter"];
		if(self::exist($search_value) || self::exist($search_author)){
			$locations = array();
			if(isset($form["search_location"]))
				$locations = $form["search_location"];
			$search_option = array();
			if(isset($form["search_options"]))
				$search_option = $form["search_options"];
			$search = $this->search($search_value, $search_author, $locations, $search_option);
		}
		if(isset($search)){
			$parameters = parent::getViewParameters($parameters, $form, $class_name);
			$parameters = $this->putInParameters($parameters, $search);
			return View::run($parameters, $form, $files, $class_name, "results");
		}
		else {
			return $this->output($parameters, $form, $files, $class_name);
		}
	}

	//---------------------------------------------------------------------------------------- search
	/**
	 * Search
	 * @param $search_value   string
	 * @param $search_author  string
	 * @param $locations      array
	 * @param $search_option  string
	 * @return array
	 */
	public function search($search_value, $search_author, $locations, $search_option)
	{
		$options = $this->chargeOptions($search_option);
		$locations = $this->chargeLocations($locations);
		$results = $this->searchAll($search_value, $search_author, $locations, $options);
		return $results;
	}

	//------------------------------------------------------------------------------------- searchAll
	/**
	 * Search with options and locations pre-charged.
	 * @param $search_value   string
	 * @param $search_author  string The strict name of an author
	 * @param $locations      array|bool If it's all location, it's at true, else, an array of Forum
	 * @param $options        array An array with all options selected
	 * @return array
	 */
	public function searchAll($search_value, $search_author, $locations, $options)
	{
		$list_search = array();
		$author = null;
		if($search_author){
			$author = Search_Object::newInstance("SAF\\Framework\\User");
			$author->login = $search_author;
			$author = Dao::searchOne($author);
			if(!$author)
				return array();
		}
		$search_value = "%" . str_replace(" ", "%", $search_value) . "%";
		foreach($options as $key => $option){
			$searched = array();
			if($locations === true){
				if($key == "Post content"){
					$post = new Post();
					$post->content = $search_value;
					$post->author = $author;
					$results = Dao::search($post);
					foreach($results as $result){
						if($result->id_topic != 0)
							$searched[] = $result;
					}
				}
				if($key == "Topic content"){
					$filter["id_topic"] = "0";
					if(isset($author))
						$filter["id_author"] = Dao::getObjectIdentifier($author);
					$filter["content"] = $search_value;
					$return = Dao::select("SAF\\Wiki\\Post", array("id"), $filter);
					if(isset($return)){
						/** @var  $return Default_List_Data */
						foreach($return->elements as $row){
							/** @var $row Default_List_Row */
							$searched[] = Dao::read($row->id(), $row->class_name);
						}
					}
				}
				if($key == "Topic title"){
					$topic = new Topic();
					$topic->title = $search_value;
					$topics = Dao::search($topic);
					if(isset($author)){
						foreach($topics as $topic){
							$topic = Forum_Utils::assignTopicFirstPost($topic);
							if($topic->first_post->id_author == Dao::getObjectIdentifier($author)){
								$searched[] = $topic;
							}
						}
					}
					else {
						$searched = $topics;
					}
				}
				$list_search = array_merge($list_search, $searched);
			}
			else {
				$searched = array();
				if($key == "Post content"){
					$post = new Post();
					$post->content = $search_value;
					$post->author = $author;
					$results = Dao::search($post);
					foreach($results as $post){
						if($post->id_topic != 0){
							$post =
								Forum_Utils::assignAttributeObjectInElement($post, "topic", "SAF\\Wiki\\Topic");
							foreach($locations as $location){
								if($post->topic->id_forum	== Dao::getObjectIdentifier($location))
									$searched[] = $post;
							}
						}
					}
					$list_search = array_merge($list_search, $searched);
				}
				if($key == "Topic content"){
					$filter["id_topic"] = "0";
					if(isset($author))
						$filter["id_author"] = Dao::getObjectIdentifier($author);
					$filter["content"] = $search_value;
					$return = Dao::select("SAF\\Wiki\\Post", array("id"), $filter);
					if(isset($return)){
						/** @var  $return Default_List_Data */
						foreach($return->elements as $row){
							/** @var $row Default_List_Row */
							$post = $row->getObject();
							/** @var $post Post */
							$topic = new Topic();
							$topic->first_post = $post;
							$topic = Dao::searchOne($topic);
							foreach($locations as $location){
								if($topic->id_forum == Dao::getObjectIdentifier($location)){
									$searched[] = $post;
								}
							}
						}
					}
					$list_search = array_merge($list_search, $searched);
				}
				if($key == "Topic title"){
					foreach($locations as $location){
						$topic = new Topic();
						$topic->title = $search_value;
						$topic->forum = $location;
						$topics = Dao::search($topic);
						if(isset($author)){
							foreach($topics as $topic){
								$topic = Forum_Utils::assignTopicFirstPost($topic);
								if($topic->first_post->id_author == Dao::getObjectIdentifier($author)){
									$searched[] = $topic;
								}
							}
						}
						else {
							$searched = $topics;
						}
						$list_search = array_merge($list_search, $searched);
					}
				}
			}
		}
		return $list_search;
	}
}
