<?php
// vim: set ai ts=4 sw=4 ft=php:
/**
 * This is Part of the User Control Panel Object
 * A replacement for the Asterisk Recording Interface
 * for FreePBX
 *
 * This is the whole shebang. Here she is in all of her glory
 *
 * License for all code of this FreePBX module can be found in the license file inside the module directory
 * Copyright 2006-2014 Schmooze Com Inc.
 */
namespace UCP;
include(__DIR__.'/UCP_Helpers.class.php');
class UCP extends UCP_Helpers {
	// Static Object used for self-referencing.
	private static $obj;
	public static $conf;

	function __construct($mode = 'local') {
		if($mode == 'local') {
			//Setup our objects for use
			//FreePBX is the FreePBX Object
			$this->FreePBX = \FreePBX::create();
			//UCP is the UCP Specific Object from BMO
			$this->Ucp = $this->FreePBX->Ucp;
			//System Notifications Class
			//TODO: pull this from BMO
			$this->notifications = \notifications::create();
			//database subsystem
			$this->db = $this->FreePBX->Database;
			//This causes crazy errors later on. Dont use it
			//$this->db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		}

		// Ensure the local object is available
		self::$obj = $this;
	}

	/**
	 * Alternative Constructor
	 *
	 * This allows the Current UCP to be referenced from anywhere, without
	 * needing to instantiate a new one. Calling $x = UCP::create() will
	 * create a new UCP if one has not already beeen created (unlikely!), or
	 * return a reference to the current one.
	 *
	 * @return object FreePBX UCP Object
	 */
	public static function create() {
		if (!isset(self::$obj)) {
			self::$obj = new UCP();
		}
		return self::$obj;
	}

	/**
	 * Get the UCP Version
	 *
	 * In accordance with pjax, when the version changes here it will force refresh
	 * the entire page, instead of just the container, when content is retrieved this
	 * will force the client to get new html assets, this version will then be placed
	 * in a meta tag
	 *
	 * https://github.com/defunkt/jquery-pjax#layout-reloading
	 *
	 * @return string The version
	 */
	function getVersion() {
		$info = $this->FreePBX->Modules->getInfo("Ucp");
		return 'v'.$info['ucp']['dbversion'];
	}

	function getSetting($username,$module,$setting) {
		return $this->FreePBX->UCP->getSetting($username,$module,$setting);
	}

	function setSetting($username,$module,$setting,$value) {
		return $this->FreePBX->UCP->setSetting($username,$module,$setting,$value);
	}

	//These Scripts persist throughout the navigation of UCP
	public function getScripts($force = false) {
		$cache = dirname(__DIR__).'/assets/js/compiled/main';
		if(!file_exists($cache) && !mkdir($cache,0777,true)) {
			die('Can Not Create Cache Folder at '.$cache);
		}

		$globalJavascripts = array(
			"socket.io.js",
			"bootstrap-3.1.1.custom.min.js",
			"jquery-ui-1.10.4.custom.min.js",
			"jquery.keyframes.min.js",
			"fileinput.js",
			"recorder.js",
			"jquery.iframe-transport.js",
			"jquery.fileupload.js",
			"jquery.form.min.js",
			"jquery.jplayer.min.js",
			"quo.js",
			"purl.js",
			"modernizr.js",
			"jquery.pjax.js",
			"notify.js",
			"packery.pkgd.min.js",
			"class.js",
			"jquery.transit.min.js",
			"date.format.js",
			"jquery.textfill.min.js",
			"jed.js",
			"jquery.cookie.js",
			"emojione.min.js",
			"ucp.js",
			"module.js"
		);
		$contents = '';
		$files = array();
		foreach ($globalJavascripts as $f) {
			$file = dirname(__DIR__).'/assets/js/'.$f;
			if(file_exists($file)) {
				$files[] = $file;
				$contents .= file_get_contents($file)."\n\n";
			}
		}

		$md5 = md5($contents);
		$filename = 'jsphpg_'.$md5.'.js';
		if(!file_exists($cache.'/'.$filename) || $force) {
			foreach(glob($cache.'/jsphp_*.js') as $f) {
				unlink($f);
			}
			$output = \JShrink\Minifier::minify($contents);
			file_put_contents($cache.'/'.$filename,$output);
		}

		return $filename;
	}

	//These Scripts persist throughout the navigation of UCP
	public function getLess($force = false) {
		$cache = dirname(__DIR__).'/assets/css/compiled/main';
		//TODO: needs to be an array of directories that need to be created on install
		if(!file_exists($cache) && !mkdir($cache,0777,true)) {
			die('Can Not Create Cache Folder at '.$cache);
		}
		if($force) {
			foreach(glob($cache.'/lessphp*') as $f) {
				unlink($f);
			}
		}

		$options = array( 'cache_dir' => $cache );

		$final = array();
		//Needs to be one unified LESS file along with the module LESS file
		$btfiles = array();
		$vars = array("icon-font-path" => '"../../../fonts/"');
		$btfiles[dirname(__DIR__).'/assets/less/bootstrap.less'] = '../../../../';
		$final['bootstrapcssless'] = \Less_Cache::Get( $btfiles, $options, $vars );

		$ucpfiles = array();
		$ucpfiles[dirname(__DIR__).'/assets/less/UCP.less'] = '../../../../';
		$final['ucpcssless'] = \Less_Cache::Get( $ucpfiles, $options );

		$ucpfiles = array();
		$ucpfiles[dirname(__DIR__).'/assets/less/font-awesome/font-awesome.less'] = '../../../../';
		$vars = array("fa-font-path" => '"../../../fonts"');
		$final['facssless'] = \Less_Cache::Get( $ucpfiles, $options, $vars );

		return $final;
	}

}
