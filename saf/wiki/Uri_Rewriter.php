<?php
namespace SAF\Wiki;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Main;
use SAF\Framework\Dao;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\View;

/**
 * URI rewriter class
 *
 * How to use '/name-of-my-article' uris and get them as real articles
 */
class Uri_Rewriter implements Registerable
{

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
		$register->aop->beforeMethod(
			[Main::class, 'runController'], [$this, 'beforeMainRunController']
		);
	}

}
