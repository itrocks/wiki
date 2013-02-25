<?php
namespace SAF\Wiki;
use SAF\Framework\Default_Output_Controller;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\View;

class Images_Upload_Display_Controller extends Default_Output_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$ext = \Images_Upload_Utils::$list_extension_accepted;
		$parameters = parent::getViewParameters($parameters, $form, $class_name);
		$parameters["title"] = "Images list";
		$listImages = Array();
		$folder = opendir(\Images_Upload_Utils::$images_repository);
		for($i=0; $f = readdir($folder); $i++){
			if(is_file($f)){
				if(in_array(preg_replace("#(.+)\.(.+)#", "$2", $f), $ext)){
					$listImages[$i] = $f;
				}
			}
		}
		closedir($folder);
		sort($listImages);
		return View::run($parameters, $form, $files, $class_name, "display");
	}
}
