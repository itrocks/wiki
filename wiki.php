<?php
global $pwd;
include_once "pwd.php";

//-------------------------------------------------------------------------------------------- wiki
$CONFIG["wiki"] = array(
	"app" => "Wiki",
	"extends" => "framework",
	'SAF\Framework\Builder' => array(
		'SAF\Framework\User' => 'SAF\Wiki\Wiki_User'
	),
	'SAF\Framework\Dao' => array(
		"database" => "saf_wiki",
		"user"     => "saf_wiki",
		"password" => $pwd["saf_wiki"]
	),
	'SAF\Framework\Menu' => array(
		"Disconnected" => array(
			"/|Home|"        => "Home",
			"/User/login"    => "Log in",
			"/User/register" => "Sign in"
		),
		"Connected" => array(
			"/|Home|"          => "Home",
			"/User/disconnect" => "Log out"
		),
		"Output" => array(
			"/Page/new"    => "New page",
			"/{page}/edit" => "Edit"
		),
		"Edit" => array(
			"/{page}/write?#page_edit" => "Save page",
			"/{page}"                  => "Cancel",
			"/{page}/delete"           => "Delete",
			"/Images_Upload"           => array("Images upload", "#images_upload")
		)
	),
	'SAF\Framework\View' => array(
		"css" => "bwiki"
	),
	'SAF\Framework\Plugins' => array(
		"normal" => array(
			'SAF\Framework\Html_Session' => "use_cookie",
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
			//'SAF\Wiki\Fix_Link_Url',
			'SAF\Wiki\Links_Recognition',
			'SAF\Wiki\Images_Upload'
		)
	)
);

require_once "framework/components/html_session/Html_Session.php";
new SAF\Framework\Html_Session("use_cookie");

if (strpos($_SERVER["PHP_SELF"], $_SERVER["SCRIPT_NAME"]) == false) require "index.php";
