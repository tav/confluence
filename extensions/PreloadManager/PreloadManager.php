<?php
/**
 * @author Jean-Lou Dupont
 * @package PreloadManager
 * @version @@package-version@@
 * @Id $Id$
 */
//<source lang=php>

if (class_exists('StubManager'))
{
	$wgExtensionCredits['other'][] = array( 
		'name'        => 'PreloadManager', 
		'version'     => '@@package-version@@',
		'author'      => 'Jean-Lou Dupont', 
		'description' => "Manages page text preloading on a per-namespace basis",
		'url'		=> 'http://mediawiki.org/wiki/Extension:PreloadManager',
	);
	StubManager::createStub2(	array(	'class' 		=> 'PreloadManager', 
										'classfilename'	=> dirname(__FILE__).'/PreloadManager.body.php',
										'hooks'			=> array(	'EditFormPreloadText',
																),
									)
							);
}
else
	echo "Extension:PreloadManager requires Extension:StubManager.";						
//</source>