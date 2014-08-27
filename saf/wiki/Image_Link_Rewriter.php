<?php
namespace SAF\Wiki;

use SAF\Framework\Dao;
use SAF\Framework\Dao\File\Session_File\Files;
use SAF\Framework\Dao\File\Session_File;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Session;
use SAF\Framework\Tools\Wiki;
use SAF\Framework\View;
use SAF\Wiki\Attachments\Attachment;

/**
 * Image link rewriter class
 * Its job is to rewrite !Image name! into !/Images/image-name.png!
 */
class Image_Link_Rewriter implements Registerable
{

	//----------------------------------------------------------------------------------- rewriteLink
	/**
	 * Rewrite link preg callback
	 *
	 * @param $matches string[]
	 * @return string
	 */
	public function rewriteLink($matches)
	{
		/** @var $attachment Attachment */
		$attachment = Dao::searchOne(['name' => $matches[1]], Attachment::class);
		if ($attachment) {
			/** @var $session_files Files */
			$session_files = Session::current()->get(Files::class, true);
			$session_files->files[] = $attachment->file;
			return '!' . View::link(Session_File::class, 'output', [$attachment->file->name]) . '!';
		}
		else {
			return '_' . $matches[1] . '_!';
		}
	}

	//---------------------------------------------------------------------------------- rewriteLinks
	/**
	 * Rewrite wiki's images links using full image path
	 *
	 * @param $string string The string to parse.
	 * @return string The parsed string
	 */
	public function rewriteLinks(&$string)
	{
		$string = preg_replace_callback('%!(\w.*\w)!%', [$this, 'rewriteLink'], $string);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->beforeMethod([Wiki::class, 'textile'], [$this, 'rewriteLinks']);
	}

}
