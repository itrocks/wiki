<?php
namespace SAF\Wiki;
use AopJoinpoint;
use SAF\Framework\Aop;
use SAF\Framework\Plugin;
use SAF\Framework\Dao;

class Forum_Sort_Elements implements Plugin
{

	//--------------------------------------------------------- afterForumUtilsGetForumsAndCategories
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterForumUtilsGetForumsAndCategories(AopJoinpoint $joinpoint)
	{
		$callback = function($a, $b){
			if($b->position == $a->position)
				return (Dao::getObjectIdentifier($a) < Dao::getObjectIdentifier($b) ? -1 : 1);
			else
				return ($a->position < $b->position ? -1 : 1);
		};
		$items = $joinpoint->getReturnedValue();
		if(uasort($items, $callback)){
			$joinpoint->setReturnedValue($items);
		}
	}

	//---------------------------------------------------------------------- afterForumUtilsGetTopics
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function afterForumUtilsGetTopics(AopJoinpoint $joinpoint)
	{
		$callback = function($a, $b){
			Forum_Utils::assignAttributeObjectInElement($a, "last_post", Forum_Utils::$namespace . "Post");
			Forum_Utils::assignAttributeObjectInElement($b, "last_post", Forum_Utils::$namespace . "Post");
			if(!isset($b->last_post->date_post) && !isset($a->last_post->date_post))
				return 0;
			if(!isset($a->last_post->date_post))
				return 1;
			if(!isset($b->last_post->date_post))
				return -1;
			if($a->last_post->date_post < $b->last_post->date_post)
				return 1;
			if($a->last_post->date_post > $b->last_post->date_post)
				return -1;
			return 0;
		};
		$topics = $joinpoint->getReturnedValue();
		if(uasort($topics, $callback)){
			$joinpoint->setReturnedValue($topics);
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Wiki\Forum_Utils->getTopics()',
			array(__CLASS__, "afterForumUtilsGetTopics")
		);
		Aop::add("after",
			'SAF\Wiki\Forum_Utils->getForums()',
			array(__CLASS__, "afterForumUtilsGetForumsAndCategories")
		);
		Aop::add("after",
			'SAF\Wiki\Forum_Utils->getCategories()',
			array(__CLASS__, "afterForumUtilsGetForumsAndCategories")
		);
	}
}
