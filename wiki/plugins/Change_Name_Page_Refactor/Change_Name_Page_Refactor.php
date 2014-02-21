<?php
namespace SAF\Wiki;

use SAF\Framework\Controller_Parameters;
use SAF\Framework\Dao;
use SAF\Framework\Default_Write_Controller;
use SAF\Framework\Search_Object;
use SAF\Plugins;

class Change_Name_Page_Refactor implements Plugins\Registerable
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
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 */
	public function beforeDefaultWriteControllerRun(Controller_Parameters $parameters, $form)
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
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod(
			array(Default_Write_Controller::class, 'run'),
			array($this, 'beforeDefaultWriteControllerRun')
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
		$object_search = Search_Object::create($class_name);
		$object_search->$var_text = '%' . $old_string . '%';
		$pages = Dao::search($object_search);
		foreach ($pages as $page) {
			$page->$var_text = str_ireplace($old_string, $new_string, $page->$var_text);
			Dao::write($page);
		}
	}

}
