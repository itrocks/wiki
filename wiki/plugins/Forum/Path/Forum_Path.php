<?php
namespace SAF\Wiki;
use SAF\Framework\Current;

class Forum_Path
{
	use Current { current as private pCurrent; }

	//----------------------------------------------------------------------------------------- $path
	/**
	 * @var array
	 */
	public $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $path array
	 */
	function __construct($path = null)
	{
		$this->path = $path;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Forum_Path
	 * @return Forum_Path
	 */
	public static function current($set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Set a value in the path
	 * @param $index string The index, generally the short class name of the object.
	 * @param $value object The object of this level.
	 */
	public function set($index, $value)
	{
		$this->path[$index] = $value;
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Set a value in the path
	 * @param $index string The index, generally the short class name of the object.
	 * @return null|object
	 */
	public function get($index)
	{
		if(isset($this->path[$index]))
			return $this->path[$index];
		return null;
	}

}
