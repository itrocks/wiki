<?php
namespace SAF\Wiki;

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
