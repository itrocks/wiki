<?php
namespace SAF\Wiki;
use SAF\Framework\List_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;

class Images_Upload_List_Controller extends List_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		return $this->runView($parameters, $form, $files, $class_name);
	}

	//--------------------------------------------------------------------------------------- runView
	public function runView(
		Controller_Parameters $parameters,
		$add_parameters = array(),
		$form = array(),
		$files = array(),
		$class_name = "SAF\\Wiki\\Images_Upload"
	)
	{
		$parameters = $this->getViewParameters($parameters, $form, $class_name);
		$parameters["images"] = $this->getImages();
		foreach($add_parameters as $key => $parameter){
			$parameters[$key] = $parameter;
		}
		return View::run($parameters, $form, $files, $class_name, "selection");
	}

	//----------------------------------------------------------------------------- getViewParameters
	public function getViewParameters(Controller_Parameters $parameters, $form, $class_name)
	{
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters["title"] = "Images list";
		return $parameters;
	}

	//------------------------------------------------------------------------------------- getImages
	public function getImages()
	{
		$ext = \Images_Upload_Utils::$list_extension_accepted;
		$listImages = Array();
		$folder = opendir(\Images_Upload_Utils::$images_repository);
		for($i=0; $f = readdir($folder); $i++){
			if(in_array(preg_replace("#(.+)\.(.+)#", "$2", $f), $ext)){
				$listImages[$i] = array("link" => "../../" . \Images_Upload_Utils::$images_repository . $f);
			}
		}
		closedir($folder);
		sort($listImages);
		return $listImages;
	}
}
