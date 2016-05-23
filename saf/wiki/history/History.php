<?php
namespace SAF\Wiki;

use SAF\Framework;
use SAF\Wiki\Article;

/**
 * Wiki article history
 *
 * @override new_value @max_length 2000000
 * @override old_value @max_length 2000000
 */
class History extends Framework\History
{

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @link Object
	 * @mandatory
	 * @replaces object
	 * @var Article
	 */
	public $article;

}
