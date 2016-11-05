<?php
namespace ITRocks\Wiki;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\View;

/**
 * URI rewriter class
 *
 * How to use '/name-of-my-article' uris and get them as real articles
 */
class Uri_Rewriter implements Registerable
{

	//------------------------------------------------------------------- $before_main_run_controller
	/**
	 * @var boolean
	 */
	private $before_main_run_controller = false;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Current feature after url rewriting
	 *
	 * @var string
	 */
	public static $feature;

	//-------------------------------------------------------------------------------- $features_list
	/**
	 * @var string[]
	 */
	public static $features_list = [
		Feature::F_ADD, Feature::F_DELETE, Feature::F_EDIT, Feature::F_OUTPUT, Feature::F_WRITE
	];

	//--------------------------------------------------------------------------------- afterViewLink
	/**
	 * @param $result  string The result of the call to View::link() is the calculated link
	 * @param $object  object The object to which the link is generated
	 * @param $feature string The feature : we act only if OUTPUT here
	 */
	public function afterViewLink(&$result, $object, $feature)
	{
		if (
			!$this->before_main_run_controller
			&& ($object instanceof Article)
			&& (($feature === Feature::F_OUTPUT) || !$feature)
		) {
			$result = SL . $object->uri;
		}
	}

	//----------------------------------------------------------------------- beforeMainRunController
	/**
	 * @param $uri string
	 * @param $get string[]
	 */
	public function beforeMainRunController(&$uri, &$get)
	{
		if (!$uri || ($uri == SL)) {
			$uri = SL . strUri(Loc::tr('Home'));
		}
		if (ctype_lower($uri[1])) {
			// text after the last slash may be a feature
			if (substr_count($uri, SL) > 1) {
				$maybe_feature = rLastParse($uri, SL);
				if ($this->isFeature($maybe_feature)) {
					$feature = $maybe_feature;
					$uri = lLastParse($uri, SL);
				}
			}
			// search article
			$this->before_main_run_controller = true;
			/** @var $article Article */
			$article = Dao::searchOne(['uri' => substr($uri, 1)], Article::class);
			if ($article) {
				$uri = View::link($article);
				if (isset($feature)) {
					$uri .= SL . $feature;
				}
			}
			else {
				$get['title'] = ucfirst(strFromUri(substr($uri, 1)));
				$uri = View::link(Article::class, Feature::F_ADD);
			}
			$this->before_main_run_controller = false;
		}
	}

	//------------------------------------------------------------------------------------- isFeature
	/**
	 * Returns true if $feature is a feature from self::$features_list
	 *
	 * @param $feature string
	 * @return boolean
	 */
	private function isFeature($feature)
	{
		return in_array($feature, self::$features_list);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[View::class, 'link'], [$this, 'afterViewLink']
		);
		$register->aop->beforeMethod(
			[Main::class, 'runController'], [$this, 'beforeMainRunController']
		);
	}

}
