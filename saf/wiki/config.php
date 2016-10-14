<?php
namespace SAF\Wiki;

use SAF\Framework\Configuration;
use SAF\Framework\Dao;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Locale;
use SAF\Framework\Locale\Number_Format;
use SAF\Framework\Plugin\Priority;
use SAF\Framework\Tools\Wiki;
use SAF\Framework\User\Write_Access_Control;
use SAF\Framework\Widget\Menu;
use SAF\Wiki\Article;

global $loc, $pwd;
require __DIR__ . '/../../loc.php';
require __DIR__ . '/../../pwd.php';
require __DIR__ . '/../framework/config.php';

//-------------------------------------------------------------------------------------------- wiki
$config['SAF/Wiki'] = [
	Configuration::APP         => Application::class,
	Configuration::ENVIRONMENT => $loc['environment'],
	Configuration::EXTENDS_APP => 'SAF/Framework',

	//---------------------------------------------------------------------------------------- normal
	Priority::NORMAL => [
		Write_Access_Control::class,
		Article\Redirect::class,
		Dao::class => [
			Link::DATABASE => $loc[Link::DATABASE],
			Link::LOGIN    => $loc[Link::LOGIN],
			Link::PASSWORD => $pwd[Link::class]
		],
		Locale::class => [
			Locale::DATE     => 'd/m/Y',
			Locale::LANGUAGE => 'fr',
			Locale::NUMBER   => [
				Number_Format::DECIMAL_MAXIMAL_COUNT => 2,
				Number_Format::DECIMAL_MINIMAL_COUNT => 2,
				Number_Format::DECIMAL_SEPARATOR     => ',',
				Number_Format::THOUSAND_SEPARATOR    => ' '
			]
		],
		Markup\Images::class,
		Markup\Links::class,
		Menu::class => [
			Menu::TITLE => ['/', 'Home', '#main'],
			'Articles' => [
				'/'                     => 'Home',
				'/SAF/Wiki/Articles'    => 'Full articles list',
				'/SAF/Wiki/Article/add' => 'Add a new article',
				'/SAF/Wiki/Search/form' => 'Search'
			],
			'Tools' => [
				'/SAF/Wiki/Attachments'     => 'Attachment files',
				'/SAF/Framework/User/login' => 'Connect user'
			]
		],
		Uri_Rewriter::class,
		Wiki::class
		/*
		Wiki\Anti_Bot::class,
		Wiki\Change_Name_Page_Refactor::class,
		Wiki\Email_Confirmation_Register::class,
		Wiki\Images_Upload::class,
		Wiki\Links_Recognition::class,
		Wiki\Modification_Reserved_Connected::class,
		Wiki\Register_Email::class,
		Wiki\Stay_Connected::class,
		*/
	]
];
