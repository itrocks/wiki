<?php
namespace ITRocks\Wiki\Markup;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\File\Session_File\Files;
use ITRocks\Framework\Dao\File\Session_File;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Wiki;
use ITRocks\Framework\View;
use ITRocks\Wiki\Attachment;

/**
 * Image link rewriter class
 * Its job is to rewrite !Image name! into !/Images/image-name.png!
 */
class Images implements Registerable
{

	//----------------------------------------------------------------------------------- rewriteLink
	/**
	 * Rewrite link preg callback
	 *
	 * @param $matches string[]
	 * @return string
	 */
	public function rewriteLink(array $matches)
	{
		$attachment_name = $matches[1];
		if (in_array($attachment_name[0], ['>', '<', '='])) {
			$start = $attachment_name[0];
			$attachment_name = substr($attachment_name, 1);
		}
		else {
			$start = '';
		}
		if (
			(strpos($attachment_name, 'http://') === 0) || (strpos($attachment_name, 'https://') === 0)
		) {
			return '!' . $attachment_name . '!';
		}
		/** @var $attachment Attachment */
		$attachment = Dao::searchOne(['name' => $attachment_name], Attachment::class);
		if ($attachment) {
			/** @var $session_files Files */
			$session_files = Session::current()->get(Files::class, true);
			$session_files->files[] = $attachment->file;
			return '!' . $start
				. View::link(Session_File::class, 'output', [$attachment->file->name])
				. '!';
		}
		return '_' . $matches[1] . '_!';
	}

	//---------------------------------------------------------------------------------- rewriteLinks
	/**
	 * Rewrite wiki's images links using full image path
	 *
	 * @param $string string The string to parse.
	 */
	public function rewriteLinks(&$string)
	{
		$string = preg_replace_callback('%!([\w<=>].*\w)!%', [$this, 'rewriteLink'], $string);
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
