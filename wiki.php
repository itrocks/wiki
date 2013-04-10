<?php

//-------------------------------------------------------------------------------------------- wiki
$CONFIG["wiki"] = array(
	"app" => "Wiki",
	"extends" => "framework",
	'SAF\Framework\Builder' => array(
		'SAF\Framework\User' => 'SAF\Wiki\Wiki_User'
	),
	'SAF\Framework\Dao' => array(
		"user"     => "wiki",
		"password" => "cd93Rjfz4",
		"database" => "wiki"
	),
	'SAF\Framework\Menu' => array(
		"Disconnected" => array(
			"/User/login"    => "Log in",
			"/User/register" => "Sign in"
		),
		"Connected" => array(
			"/User/disconnect" => "Log out"
		),
		"Output" => array(
			"/Page/new"    => "New page",
			"/{page}/edit" => "Edit"
		),
		"Edit" => array(
			"/{page}/save"   => "Save page",
			"/{page}"        => "Cancel",
			"/{page}/delete" => "Delete",
			"/Images/upload" => "Images upload"
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
			'SAF\Wiki\Forum_Uri_Rewriter',
			'SAF\Wiki\Update_Nb_Values',
			'SAF\Wiki\Only_Output_For_Not_Connected',
			'SAF\Wiki\Edition_Reserved_To_Author',
			'SAF\Wiki\News_Subscribe',
			'SAF\Wiki\Last_Post',
			'SAF\Wiki\Forum_Sort_Elements',
			'SAF\Wiki\Topic_Controls',
			'SAF\Wiki\Forum_Controls',
			'SAF\Wiki\Category_Controls',
			'SAF\Wiki\Ascent_Controls',
			'SAF\Wiki\ReadOrNotRead'
		)
	)
);

if (strpos($_SERVER["PHP_SELF"], $_SERVER["SCRIPT_NAME"]) == false) require "index.php";
