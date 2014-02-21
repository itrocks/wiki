<?php
namespace SAF\Framework;

use SAF\Wiki;

global $pwd;
include_once 'pwd.php';

/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

//-------------------------------------------------------------------------------------------- wiki
$CONFIG['wiki'] = [
	'app'     => 'Wiki',
	'extends' => 'framework',

	//------------------------------------------------------------------------------------------ core
	'core' => [
		Builder::class => [
			'SAF\Framework\User' => 'SAF\Wiki\Wiki_User'
		]
	],

	//--------------------------------------------------------------------------------------- highest
	'highest' => [
		Dao::class => [
			'database' => 'saf_wiki',
			'user'     => 'saf_wiki',
			'password' => $pwd['saf_wiki']
		]
	],

	//---------------------------------------------------------------------------------------- normal
	'normal' => array(
		\SAF\Framework\Wiki::class,
		Menu::class => [
			'Disconnected' => [
				'/|Home|'        => 'Home',
				'/User/login'    => 'Log in',
				'/User/register' => 'Sign in'
			],
			'Connected' => [
				'/|Home|'          => 'Home',
				'/User/disconnect' => 'Log out'
			],
			'Output' => [
				'/Page/new'    => 'New page',
				'/{page}/edit' => 'Edit'
			],
			'Edit' => [
				'/{page}/write?#page_edit' => 'Save page',
				'/{page}'                  => 'Cancel',
				'/{page}/delete'           => 'Delete',
				'/Images_Upload'           => ['Images upload', '#images_upload']
			]
		],
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
	),
];
