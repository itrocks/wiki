<?php
namespace ITRocks\Wiki\Plugins\Images_Upload;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Feature\List_;
use ITRocks\Framework\View;
use ITRocks\Wiki\Plugins\Images_Upload;

/**
 * Images upload list controller
 */
class Images_Upload_List_Controller extends List_\Controller
{

	//----------------------------------------------------------------------------- getViewParameters
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $class_name string
	 * @return array
	 */
	public function getViewParameters(Parameters $parameters, array $form, $class_name)
	{
		$parameters          = parent::getViewParameters($parameters, $form, $class_name);
		$parameters['title'] = 'Images list';
		return $parameters;
	}

	//------------------------------------------------------------------------------------- getImages
	/**
	 * List all images accepted in this plugin, and return their link.
	 *
	 * @return array A list of image link sorted by name.
	 */
	public function getImages()
	{
		$ext        = Images_Upload_Utils::$list_extension_accepted;
		$listImages = [];
		$folder = opendir(Images_Upload_Utils::$images_repository);
		for ($i = 0; $f = readdir($folder); $i++) {
			if (in_array(preg_replace('%(.+)\.(.+)%', '$2', $f), $ext)) {
				$listImages[$i] = [
					'link' => '../../' . Images_Upload_Utils::$images_repository . $f,
					'name' => $f
				];
			}
		}
		closedir($folder);
		sort($listImages);
		return $listImages;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		return $this->runView($parameters, [], $form, $files, $class_name);
	}

	//--------------------------------------------------------------------------------------- runView
	/**
	 * @param $parameters     Parameters
	 * @param $add_parameters array
	 * @param $form           array
	 * @param $files          array[]
	 * @param $class_name     string
	 * @return mixed
	 */
	public function runView(
		Parameters $parameters, $add_parameters = [], array $form = [], array $files = [],
		$class_name = Images_Upload::class
	) {
		$parameters           = $this->getViewParameters($parameters, $form, $class_name);
		$parameters['images'] = $this->getImages();
		foreach ($add_parameters as $key => $parameter) {
			$parameters[$key] = $parameter;
		}
		return View::run($parameters, $form, $files, $class_name, 'selection');
	}

}
