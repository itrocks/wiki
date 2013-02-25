<?php
namespace SAF\Wiki;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;

class Images_Upload_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		return (new Images_Upload_List_Controller($parameters, $form, $files));
	}

	//---------------------------------------------------------------------------------------- upload
	public function upload(Controller_Parameters $parameters, $form, $files)
	{
		if(isset($_FILES['image'])){
			$image = $_FILES['image'];
			if (
				isset($image['tmp_name'])
				&&($image['error'] == UPLOAD_ERR_OK)
			) {
				if($this->isImage($image)){
					$destination = \Images_Upload_Utils::$images_repository;
					$result = move_uploaded_file(
						$image['tmp_name'],
						$destination . $image['name']
					);
					if($result){
						$message = "The image has been uploaded";
					} else {
						$message = "An error occurred while move the image in the image folder";
					}
				} else {
					$message = "This file is not an image or have not the right extension";
				}
			}
			else {
				$message = "The image has not been uploaded : " . $this->codeToMessage($image['error']);
			}
		}
		else {
			return (new Images_Upload_List_Controller())->runView($parameters);
		}
		$adding_parameters = array();
		$adding_parameters["message"] = $message;
		return (new Images_Upload_List_Controller())->runView($parameters, $adding_parameters);
	}

	//----------------------------------------------------------------------------- getViewParameters
	public function getViewParameters(Controller_Parameters $parameters, $message)
	{
		$parameters = $parameters->getObjects();
		$parameters["message"] = $message;
		return $parameters;
	}

	//--------------------------------------------------------------------------------------- isImage
	public function isImage($image)
	{
		$extension = $image["type"];
		foreach(\Images_Upload_Utils::$list_extension_accepted as $extension_accepted){
			if($extension == "image/" . $extension_accepted)
				return true;
		}
		return false;
	}

	//--------------------------------------------------------------------------------- codeToMessage
	private function codeToMessage($code)
	{
		switch ($code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = "The uploaded file was only partially uploaded";
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = "No file was uploaded";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = "Missing a temporary folder";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = "Failed to write file to disk";
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = "File upload stopped by extension";
				break;
			default:
				$message = "Unknown upload error";
				break;
		}
		return $message;
	}
}
