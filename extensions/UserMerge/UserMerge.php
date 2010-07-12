<?php
/** \file
* \brief Contains setup code for the User Merge and Delete Extension.
*/

# Not a valid entry point, skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo "User Merge and Delete extension";
        exit(1);
}

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'User Merge and Delete',
	'url'            => 'http://www.mediawiki.org/wiki/Extension:User_Merge_and_Delete',
	'author'         => 'Tim Laqua',
	'descriptionmsg' => 'usermerge-desc',
	'version'        => '1.6.1'
);

$wgAvailableRights[] = 'usermerge';
# $wgGroupPermissions['bureaucrat']['usermerge'] = true;

$dir = dirname(__FILE__) . '/';
$wgAutoloadClasses['UserMerge'] = $dir . 'UserMerge_body.php';

$wgExtensionMessagesFiles['UserMerge'] = $dir . 'UserMerge.i18n.php';
$wgExtensionAliasesFiles['UserMerge'] = $dir . 'UserMerge.alias.php';
$wgSpecialPages['UserMerge'] = 'UserMerge';
$wgSpecialPageGroups['UserMerge'] = 'users';

$wgUserMergeProtectedGroups = array( "sysop" );

# Add a new log type
$wgLogTypes[]                         = 'usermerge';
$wgLogNames['usermerge']              = 'usermerge-logpage';
$wgLogHeaders['usermerge']            = 'usermerge-logpagetext';
$wgLogActions['usermerge/mergeuser']  = 'usermerge-success-log';
$wgLogActions['usermerge/deleteuser'] = 'usermerge-userdeleted-log';
