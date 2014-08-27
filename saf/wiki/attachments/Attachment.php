<?php
namespace SAF\Wiki\Attachments;
use SAF\Framework\Dao\File;

/**
 * Wiki attached file class
 *
 * @representative name
 */
class Attachment
{

	//----------------------------------------------------------------------------------------- $file
	/**
	 * @link Object
	 * @mandatory
	 * @setter setFile
	 * @var File
	 */
	public $file;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * @mandatory
	 * @var string
	 */
	public $name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return strval($this->name);
	}

	//--------------------------------------------------------------------------------------- setFile
	/**
	 * @param $file File
	 */
	/* @noinspection PhpUnusedPrivateMethodInspection @getter */
	private function setFile(File $file)
	{
		$this->file = $file;
		if (empty($this->name)) {
			$this->name = ucfirst(strFromUri(lLastParse($file->name, DOT)));
		}
	}

}
