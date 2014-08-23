<?php
namespace SAF\Wiki;

use SAF\Framework;

global $pwd;
require 'pwd.php';
require 'saf.php';

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

//-------------------------------------------------------------------------------------------- wiki
$config['SAF/Wiki'] = [
	'app'     => Application::class,
	'extends' => 'SAF/Framework',

	//---------------------------------------------------------------------------------------- normal
	'normal' => [
		Framework\Dao::class => [
			'database' => 'saf_wiki',
			'login'    => 'saf_wiki',
			'password' => $pwd['saf_wiki']
		],
		Framework\Tools\Wiki::class,
		Framework\Widget\Menu::class => [
			'title' => ['/', 'Home', '#main'],
			'Articles' => [
				'/SAF/Wiki/Articles/Article/add' => 'Add a new article',
				'/SAF/Wiki/Articles/Articles'    => 'Full articles list',
				'/SAF/Wiki/Search/form'          => 'Search'
			]
		],
		/*
		Wiki\Anti_Bot::class,
		Wiki\Change_Name_Page_Refactor::class,
		Wiki\Email_Confirmation_Register::class,
		Wiki\Images_Upload::class,
		Wiki\Image_Wiki_Link_Parse::class,
		Wiki\Links_Recognition::class,
		Wiki\Modification_Reserved_Connected::class,
		Wiki\Parse_Wiki_Link::class,
		Wiki\Register_Email::class,
		Wiki\Stay_Connected::class,
		Wiki\Uri_Rewriter::class
		*/
	]
];
