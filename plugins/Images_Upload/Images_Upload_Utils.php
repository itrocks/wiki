<?php
namespace ITRocks\Wiki\Plugins\Images_Upload;

/**
 * Images upload utils
 */
class Images_Upload_Utils
{

	//---------------------------------------------------------------------- $list_extension_accepted
	/**
	 * @var string[]
	 */
	public static $list_extension_accepted = ['png', 'jpg', 'bitmap', 'gif', 'jpeg'];

	//---------------------------------------------------------------------------- $images_repository
	/**
	 * The directory name for the uploaded images.
	 * This folder must exist and have write access
	 *
	 * @var string
	 */
	public static $images_repository = 'wiki/uploaded_img/';
}
