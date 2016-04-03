<?php
namespace SAF\Wiki\Article;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Main;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;
use SAF\Framework\Tools\Call_Stack;
use SAF\Wiki\Article;

/**
 * The Redirect plugin allows articles containing #REDIRECT [Another article title]
 */
class Redirect implements Registerable
{

	//------------------------------------------------------------------------------ $redirected_from
	/**
	 * @multiline
	 * @var string
	 */
	private $redirected_from;

	//----------------------------------------------------------------------------- onArticleTextRead
	/**
	 * On each Article::$text read access : apply #REDIRECT when into the "output" feature
	 *
	 * @param $object Article
	 * @param $value string the read property value to be changed : Article::$text
	 * @return string the changed property value : #REDIRECT changed to javascript
	 */
	public function onArticleTextRead(Article $object, $value)
	{
		// apply #REDIRECT on "output" feature only
		if (
			(substr($value, 0, 11) === '#REDIRECT [') && (substr($value, -1) === ']')
			&& ((new Call_Stack())->getFeature() == Feature::F_OUTPUT)
		) {
			if (!$this->redirected_from) {
				$this->redirected_from = SL . Loc::tr('Redirected from') . SL;
			}
			else {
				$this->redirected_from .= SP . '>';
			}
			// redirected from is a link to the "edit" feature
			$this->redirected_from .= SP
				. DQ . str_replace('@', '&#64;', $object->title) . DQ . ':'
				. strUri($object->title . SL . Feature::F_EDIT);
			Main::$current->redirect(SL . strUri(substr($value, 11, -1)));
		}
		// when current article comes from a redirection, display the "directed from" edit links
		elseif ($this->redirected_from) {
			$value = $this->redirected_from . LF . LF . $value;
		}
		return $value;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code : thread of #REDIRECT
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->readProperty([Article::class, 'text'], [$this, 'onArticleTextRead']);
	}

}
