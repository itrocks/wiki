<?php
namespace ITRocks\Wiki\Plugins\Images_Upload;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 * Images upload controller
 */
class Images_Upload_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------- codeToMessage
	/**
	 * Return a text which explain a code error
	 *
	 * @param $code integer The error code to explain
	 * @return string The text which explain the code
	 */
	private function codeToMessage($code)
	{
		switch ($code) {
			case UPLOAD_ERR_INI_SIZE:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
			case UPLOAD_ERR_FORM_SIZE:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded';
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk';
			case UPLOAD_ERR_EXTENSION:
				return 'File upload stopped by extension';
		}
		return 'Unknown upload error';
	}

	//---------------------------------------------------------------------------------- generateName
	/**
	 * Generate a name who not exist in this server
	 *
	 * @param $destination string The images folder
	 * @param $image_name  string The name of the image
	 * @return string Return the same name if the file not exist
	 *         or return this name file incremented by a number, this name is unique.
	 */
	public function generateName($destination, $image_name)
	{
		$tmp_name = explode('.', $image_name);
		$tmp_base_name = $tmp_name[0];
		$i = 0;
		while (file_exists($destination . join('.', $tmp_name))) {
			$tmp_name[0] = $tmp_base_name . ++$i;
		}
		return join('.', $tmp_name);
	}

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * Get view parameters
	 *
	 * @param $parameters Parameters
	 * @param $message    string
	 * @return array
	 */
	public function getViewParameters(Parameters $parameters, $message)
	{
		$parameters = $parameters->getObjects();
		$parameters['message'] = $message;
		return $parameters;
	}

	//--------------------------------------------------------------------------------------- isImage
	/**
	 * Test if this image is an image permitted
	 *
	 * @param $image array Image to test
	 * @return boolean True if it's a permitted image, false else.
	 */
	public function isImage(array $image)
	{
		$extension = $image['type'];
		foreach (Images_Upload_Utils::$list_extension_accepted as $extension_accepted) {
			if ($extension == 'image/' . $extension_accepted)
				return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return Images_Upload_List_Controller
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		return (new Images_Upload_List_Controller)->run($parameters, $form, $files, get_class($this));
	}

	//------------------------------------------------------------------------------------- runUpload
	/**
	 * Upload an image passed by forms.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function runUpload(
		Parameters $parameters,
		/** @noinspection PhpUnusedParameterInspection */ array $form,
		array $files
	) {
		if (isset($files['image'])) {
			$image = $files['image'];
			if (isset($image['tmp_name']) &&($image['error'] == UPLOAD_ERR_OK)) {
				if ($this->isImage($image)) {
					$destination = Images_Upload_Utils::$images_repository;
					$name = $this->generateName($destination, $image['name']);
					$result = move_uploaded_file($image['tmp_name'], $destination . $name);
					if ($result) {
						$message = 'The image has been uploaded';
					}
					else {
						$message = 'An error occurred while move the image in the image folder';
					}
				}
				else {
					$message = 'This file is not an image or have not the right extension';
				}
			}
			else {
				$message = 'The image has not been uploaded : ' . $this->codeToMessage($image['error']);
			}
		}
		else {
			return (new Images_Upload_List_Controller())->runView($parameters);
		}
		$adding_parameters['message'] = $message;
		return (new Images_Upload_List_Controller())->runView($parameters, $adding_parameters);
	}

}
