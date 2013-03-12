<?php

//--------------------------------------------------------------------------------------- wiki
$CONFIG["wiki"] = array(
	"app" => "Wiki",
	"extends" => "framework",
	'SAF\Framework\Dao' => array(
		"user"     => "wiki",
		"password" => "cd93Rjfz4",
		"database" => "wiki"
	),
	'SAF\Framework\Builder' => array(
		'SAF\Framework\User' => 'SAF\Wiki\Wiki_User'
	),
	'SAF\Framework\Menu' => array(
		"Disconnect" => array(
			"/User/login" => "Log in",
			"/User/register" => "Sign in"
		),
		"Connect" => array(
			"/User/disconnect" => "Log out"
		)
	),
	'SAF\Framework\View' => array(
		"css" => "bwiki"
	),
	'SAF\Framework\Plugins' => array(
		"normal" => array(
			'SAF\Framework\Wiki',
			'SAF\Wiki\Uri_Rewriter',
			'SAF\Wiki\Modification_Reserved_Connected',
			'SAF\Wiki\Anti_Bot',
			'SAF\Wiki\Register_Email',
			'SAF\Wiki\Email_Confirmation_Register',
			'SAF\Wiki\Stay_Connected',
			'SAF\Wiki\Image_Wiki_Link_Parse',
			'SAF\Wiki\Parse_Wiki_Link',
			'SAF\Wiki\Change_Name_Page_Refactor',
			'SAF\Wiki\Fix_Link_Url',
			'SAF\Wiki\Links_Recognition',
			'SAF\Wiki\Images_Upload',
			'SAF\Wiki\Forum_Uri_Rewriter'
		)
	)
);

if (strpos(__FILE__, $_SERVER["PHP_SELF"])) require "index.php";
