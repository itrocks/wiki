<?php
namespace ITRocks\Wiki\Plugins;

use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Printer\Model\Page;
use ITRocks\Framework\Widget\Write\Write_Controller;

/**
 * Change page name refactor
 */
class Change_Name_Page_Refactor implements Registerable
{

	//------------------------------------------------------------------------------ $page_class_name
	/**
	 * @var string
	 */
	private $page_class_name = Page::class;

	//-------------------------------------------------------------------------------- $page_var_name
	/**
	 * @var string
	 */
	private $page_var_name = 'name';

	//-------------------------------------------------------------------------------- $page_var_text
	/**
	 * @var string
	 */
	private $page_var_text = 'text';

	//--------------------------------------------------------------- beforeDefaultWriteControllerRun
	/**
	 * Change all references of a link of this page with the new name
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 */
	public function beforeDefaultWriteControllerRun(Parameters $parameters, array $form)
	{
		$objects = $parameters->getObjects();
		$object  = reset($objects);
		// It's specific to Pages objects
		if (is_object($object) && (get_class($object) == $this->page_class_name)) {
			$name = $this->page_var_name;
			if ($object->$name != $form[$name]) {
				$page_var_text = $this->page_var_text;
				$old_name = $object->$name;
				$new_name = $form[$name];
				$references = $this->generateReferences($old_name, $new_name);
				foreach ($references as $reference) {
					$this->replaceInContent(
						$reference['old_reference'],
						$reference['new_reference'],
						$this->page_class_name,
						$page_var_text
					);
				}
			}
		}
	}

	//---------------------------------------------------------------------------- generateReferences
	/**
	 * Generate a tab of the references.
	 * @param $old_name string
	 * @param $new_name string
	 * @return array List references possible.
	 */
	protected function generateReferences($old_name, $new_name)
	{
		return [
			[
				'old_reference' => '[' . $old_name . ']',
				'new_reference' => '[' . $new_name . ']'
			],
			[
				'old_reference' => '":' . str_replace(' ', '_', $old_name) . '',
				'new_reference' => '":' . str_replace(' ', '_', $new_name) . ''
			]
		];
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod(
			[Write_Controller::class, 'run'],
			[$this, 'beforeDefaultWriteControllerRun']
		);
	}

	//------------------------------------------------------------------------------ replaceInContent
	/**
	 * Replaces a string in all the contents of a field specified by a table $var_text specified by the corresponding class.
	 *
	 * @param $old_string string String searched.
	 * @param $new_string string The string used to replace.
	 * @param $class_name string The class corresponding to the databases table.
	 * @param $var_text   string Field's name.
	 */
	public function replaceInContent($old_string, $new_string, $class_name, $var_text)
	{
		$object_search            = Search_Object::create($class_name);
		$object_search->$var_text = '%' . $old_string . '%';
		$pages                    = Dao::search($object_search);
		foreach ($pages as $page) {
			$page->$var_text = str_ireplace($old_string, $new_string, $page->$var_text);
			Dao::write($page);
		}
	}

}
