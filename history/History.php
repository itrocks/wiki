<?php
namespace ITRocks\Wiki;

use ITRocks\Framework;

/**
 * Wiki article history
 *
 * @override new_value @max_length 2000000
 * @override old_value @max_length 2000000
 */
class History extends Framework\History
{

	//-------------------------------------------------------------------------------------- $article
	/**
	 * @link Object
	 * @mandatory
	 * @replaces object
	 * @var Article
	 */
	public $article;

}
