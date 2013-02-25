<?php
/**
 * Created by JetBrains PhpStorm.
 * User: hoel
 * Date: 20/02/13
 * Time: 14:18
 * To change this template use File | Settings | File Templates.
 */
class Images_Upload_Utils
{
	public static $list_extension_accepted = array("png", "jpg", "bitmap", "gif", "jpeg");

	/**
	 * The directory name for the uploaded images.
	 * This folder must exist and have write access.
	 * @var string
	 */
	public static $images_repository = "wiki/uploaded_img/";
}
