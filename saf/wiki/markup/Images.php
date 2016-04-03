<?php
namespace SAF\Wiki\Markup;

use SAF\Framework\Dao;
use SAF\Framework\Dao\File\Session_File\Files;
use SAF\Framework\Dao\File\Session_File;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Session;
use SAF\Framework\Tools\Wiki;
use SAF\Framework\View;
use SAF\Wiki\Attachment;

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
	public function rewriteLink($matches)
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
	 * @return string The parsed string
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
