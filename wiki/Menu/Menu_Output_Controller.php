<?php
namespace SAF\Wiki;

use SAF\Framework\Controller_Parameters;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Menu;
use SAF\Framework\Session;
use SAF\Framework\User;
use SAF\Framework\View;

class Menu_Output_Controller implements Feature_Controller
{

	//----------------------------------------------------------------------------------- deleteBlock
	/**
	 * Delete a block from a menu
	 *
	 * @param $menu         Menu
	 * @param $delete_title string
	 */
	private function deleteBlock(Menu $menu, $delete_title)
	{
		foreach ($menu->blocks as $block_key => $block) {
			if ($block->title == $delete_title) {
				unset($menu->blocks[$block_key]);
			}
		}
	}

	//----------------------------------------------------------------------------------- replacePage
	/**
	 * Replaces {page} with page title into menu items keys
	 *
	 * @param Menu $menu
	 * @param Page $page
	 */
	private function replacePage(Menu $menu, $page)
	{
		foreach ($menu->blocks as $block) {
			foreach ($block->items as $item) {
				if (isset($page)) {
					$item->link = str_replace('{page}', $page->name, $item->link);
				}
				elseif (strpos($item->link, '{page}')) {
					$item->link = str_replace('{page}', 'Page', $item->link);
				}
			}
		}
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Test if user is login or not, and delete the
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		/** @var $menu Menu */
		$menu = Session::current()->plugins->get(Menu::class);
		$parameters = $parameters->getObjects();
		array_unshift($parameters, $menu);
		$user = User::current();
		// a user is logged in
		if (isset($user) && $user->login) {
			$this->deleteBlock($menu, 'Disconnected');
			$page = Page::current();
			// a page is opened
			if ($page || (Uri_Rewriter::$feature == 'new')) {
				// remove output / edit menus blocks, depending on current running feature
				$this->deleteBlock($menu,
					((Uri_Rewriter::$feature == 'edit') || (Uri_Rewriter::$feature == 'new'))
						? 'Output'
						: 'Edit'
				);
				$this->replacePage($menu, $page);
			}
			// no page is opened
			else {
				$this->deleteBlock($menu, 'Output');
				$this->deleteBlock($menu, 'Edit');
			}
		}
		// no user is logged in
		else {
			$this->deleteBlock($menu, 'Connected');
			$this->deleteBlock($menu, 'Output');
			$this->deleteBlock($menu, 'Edit');
		}
		return View::run($parameters, $form, $files, 'Menu', 'output');
	}

}
