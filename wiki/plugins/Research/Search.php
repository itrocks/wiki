<?php
namespace SAF\Wiki;
use SAF\Framework\Current;

class Search
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- $search
	/**
	 * @var string
	 */
	var $search;

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Search
	 * @return Search
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent($set_current);
	}

}
