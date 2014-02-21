<?php
namespace SAF\Wiki;

use SAF\Framework\Dao;
use SAF\Framework\Default_List_Data;
use SAF\Framework\Wiki;
use SAF\Plugins;

class Links_Recognition implements Plugins\Registerable
{

	//----------------------------------------------------------------------------- beforeWikiTextile
	/**
	 * @param $result string
	 */
	public static function beforeWikiTextile(&$result)
	{
		/** @var $pages Default_List_Data */
		$pages = Dao::select(Page::class, ['name']);
		for($i = 0 ; $i < $pages->length() ; $i++){
			$title_page = $pages->getRow($i)->getValue('name');
			$link_replace = '<a href=\'' . str_replace(' ', '_', $title_page) . '\'>' . $title_page . '</a>';
			$result = self::replaceIfNotBetweenTags($result, $title_page, $link_replace, '<a', '</a>');
		}
	}

	//-------------------------------------------------------------------------------------- register
	public function register(Plugins\Register $register)
	{
		$register->aop->afterMethod( [ Wiki::class, 'textile' ], [ __CLASS__, 'beforeWikiTextile' ]);
	}

	//----------------------------------------------------------------------- replaceIfNotBetweenTags
	/**
	 * Replace a string in a text by an other string if this string is not between tags given in
	 * parameters.
	 * This method is not case sensitive.
	 *
	 * @example
	 * replaceIfNotBetweenTags(
	 *    '<a href=\'Welcome\'>Welcome page</a> : This is Welcome page.',
	 *    'Welcome',
	 *    'the welcoming',
	 *    '<a',
	 *    '</a>'
	 *   );
	 *  return '<a href=\'Welcome\'>Welcome page</a> : This is the welcoming page.'
	 *
	 * @param $text       string The subject where search
	 * @param $search     string The searched value
	 * @param $replace    string The replace value
	 * @param $tag_begin  string The begin tag
	 * @param $tag_end    string The end tag
	 * @return string The text with values replaced
	 */
	private static function replaceIfNotBetweenTags($text, $search, $replace, $tag_begin, $tag_end)
	{
		$offset = 0;
		$offset_tag = 0;
		while (($pos = stripos($text, $search, $offset)) !== false) {
			$tag_begin_pos = 0;
			$tag_end_pos = 0;
			$old_tag_end_pos = -1;
			$offset_tag_tmp = $offset_tag;
			$can_replace = true;
			while ($tag_end_pos < $pos && $tag_end_pos !== false && $tag_end_pos != $old_tag_end_pos) {
				$old_tag_end_pos = $tag_end_pos;
				$tag_begin_pos_tmp = stripos($text, $tag_begin, $offset_tag_tmp);
				if ($tag_begin_pos_tmp === false) {
					break;
				}
				$tag_begin_pos = $tag_begin_pos_tmp;
				$tag_end_pos = stripos($text, $tag_end, $tag_begin_pos);
				if ($tag_begin_pos < $pos && $tag_end_pos > $pos) {
					$can_replace = false;
				}
				$offset_tag_tmp = $tag_end_pos + 1;
			}
			$offset_tag = $tag_begin_pos;
			if ($can_replace) {
				$text = substr_replace($text, $replace, $pos, strlen($search));
				$offset = $pos + strlen($replace);
			}
			else {
				$offset = $pos + strlen($search);
			}
		}
		return $text;
	}

}
