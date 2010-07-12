<?php
##########################################################################
#    SubPageFunctions.php Copyright (C) 2009  PM Gostelow
#
#    This script is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This script is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
##########################################################################

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}
// DO NOT EDIT CLASS!
abstract
class ExtAbstractCredits
{
	const cdNAME          = 'name';           // for package name
	const cdURL           = 'url';            // for site link
	const cdAUTHOR        = 'author';         // for author name/s
	const cdVERSION       = 'version';        // for package release
	const cdNOTE          = 'description';    // for short note
  const cdNOTEMSG       = 'descriptionmsg'; // for long note
	const URLPREFIX       =                   // for link name
		"http://www.mediawiki.org/wiki/Extension:";
  protected
  static $url           = '';               // for package site name
	// always override, always call
	abstract static function register();      // for LocalSettings.php

	// seldom override, always call
	// updates and hooks credits into the extension
	static
	function register_credits( $hook, $pkgCredits, array $pages = array() )
	{
		global $wgExtensionCredits;
		if (empty(self::$url))
			self::$url = self::URLPREFIX . $pkgCredits[self::cdNAME];
		$pkgCredits[self::cdURL] = self::$url;
		foreach( $pages as $key => $value )
			$pkgCredits[$key] = $value;
		$wgExtensionCredits[$hook][] = $pkgCredits;
	}
};

// parser function specific class
// DO NOT EDIT CLASS!
abstract
class pfAbstractFunctions extends ExtAbstractCredits
{
	const SETUP           = 'setup';          // function name
	const MAGIC           = 'magic';          // function name
	const mgSUFFIX        = "_magic";         // magic file suffix
	const mgSAMPLE        = '_sample';
	const mgDEFAULT       = 'default';        // default language code
	const mgFILE          = "%s/%s%s.php"; // file name format
	const pkPREFIX        = "\$wg";           // class var name prefix
	const mgHEADER        = "<?php
# automatically generated for %s on
# %s.
# Please add your own language and aliases.
# New releases should NOT overwrite this file.
#
# To regenerate the default file, simply remove it.
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}
";
	static $hkCredit      = 'parserhook';     // credit hook name
	static $hkLang        = 'LanguageGetMagic';// magic hook name
	static $dir           = '';               // our path name

	abstract
	static
	function setup();

	abstract
	static
	function magic( $word, $lang );

	// seldom override, never call
	// create magic file if it doesn't exist.
	// return file name, or false if it doesn't exist.
	static
	function make_magic( $pkgName, $funcs )
	{
		global $langCodeList;
		if ( empty( self::$dir ))
			self::$dir = dirname( __FILE__ );
		$magicfile = sprintf( self::mgFILE,
			self::$dir, $pkgName, self::mgSUFFIX );
		$samplefile = sprintf( self::mgFILE,
			self::$dir, $pkgName, self::mgSUFFIX . self::mgSAMPLE);

		if ( !file_exists( $magicfile ))
		{
			if ( file_exists( $samplefile ))
				require_once $samplefile;
			else
				$langCodeList = array( self::mgDEFAULT );
			$file = fopen( $magicfile, "w");
			fwrite( $file,
				sprintf( self::mgHEADER, $pkgName, date( DATE_RFC822 )));
			fwrite( $file, "$".__CLASS__.self::mgSUFFIX." = array(\n" );
			foreach( $langCodeList as $code )
			{
				fwrite( $file, "  \"".$code."\" => array(\n");
				foreach( $funcs as $func )
					fwrite( $file, "    \"".$func."\" => array(0,\"".$func."\"),\n");
				fwrite( $file, "  ),\n");
			}
			fwrite( $file, ");\n?>\n");
			fclose( $file );
			if ( !file_exists( $magicfile ))
				$magicfile = false;
		}
		return $magicfile;
	}
}

// Everything is stored in this class
class pfSubPages extends pfAbstractFunctions
{
	// credit constants
	const AUTHOR          = 'Peter Gostelow';
	const PACKAGE         = 'SubPageFunctions';
	const VERSION         = '0.0.3';
	const NOTE            = 'Sub-page extended functions';
	// function constants
	const fnCALENDAR      = "calendar";
	const fnPATHNAME      = "pathname";
	const fnSUBPAGES      = "subpages";
	const fnUSER          = "user";

	// static function array
	protected
		static $funcs        = array(
			self::fnCALENDAR,
			self::fnPATHNAME,
			self::fnSUBPAGES,
			self::fnUSER );

	// static credit array
	static $credits     = array(
		self::cdNAME      => self::PACKAGE,
		self::cdURL       => '',
		self::cdAUTHOR    => self::AUTHOR,
		self::cdNOTE      => self::NOTE,
		self::cdVERSION   => self::VERSION);

	// static magic array, see also *_magic.php file
	private
		static $magic       = array(
			self::mgDEFAULT => array(
				self::fnCALENDAR => array(0, self::fnCALENDAR),
				self::fnPATHNAME => array(0, self::fnPATHNAME),
				self::fnSUBPAGES => array(0, self::fnSUBPAGES),
				self::fnUSER     => array(0, self::fnUSER )),
			);

	// static methods

	// always call, seldom override
	// register setup and magic functions, and return magic file name.
	static
	function register()
	{
		global $wgHooks, $wgExtensionCredits, $wgExtensionFunctions;

		$magicfile = self::make_magic( self::PACKAGE, self::$funcs );
		if ( false != $magicfile )
		{
			self::register_credits( self::$hkCredit, self::$credits);
			$wgExtensionFunctions[] = __CLASS__.'::'.self::SETUP;
			$wgHooks[self::$hkLang][] = __CLASS__.'::'.self::MAGIC;
		}
		return $magicfile;
	}

	// seldom override, never call
	// add functions to parser hook
	static
	function setup()
	{ // create a global instance variable
		$globalVar = self::pkPREFIX.self::PACKAGE;
		global $wgParser, $$globalVar;
		// create ourself globally
		$whoami = __CLASS__;
		$$globalVar = new $whoami;
		foreach( self::$funcs as $name )
			$wgParser->setFunctionHook( $name, array(
				&$$globalVar, $name ));
	}

	// never override, never call
	// return language specific function names
	static
	function magic( $magicWord, $langcode )
	{
		global $GLOBALS;
		$langVar = __CLASS__.self::mgSUFFIX;

		$lang = @$GLOBALS[$langVar][$langcode];     // try language var
		if ( !is_array( $lang ))
			$lang = @$GLOBALS[$langVar][self::mgDEFAULT]; // try file default
		if ( !is_array( $lang ))
			$lang = self::$magic[self::mgDEFAULT];   // try internal default
		if (is_array( $lang ))
			foreach ( self::$funcs as $key )
				$magicWord[$key]= $lang[$key];
		return true;
	}


	// figure out if $NS is name, number, or null
	private
	function ns_index( &$parser, &$NS )
	{
		if ( !is_numeric( $NS ))
			if ( !empty( $NS ))
				$ns_child = Namespace::getCanonicalIndex( strtolower( $NS ) );
			else $ns_child = $parser->getTitle()->getNamespace();
		else $ns_child = intval( $NS );
		return $ns_child;
	}

// PARSER FUNCTIONS

	// Return a three column html table of subpage links rooted at the
	// parent's page. This allows navigating down path-names to provide
	// vertical tree browsing within a namespace, and horizontal jumping
	// between similarly named trees in other namespaces.
	// Note: This only lists the parent's immediate children.
	// FIXME: the column count should be a parametre, not hard-coded
	// syntax: {{#subpages: [NAMESPACE]}}}
	// 	where: NAMESPACE = name, index, or null
	function subpages( &$parser, $NS = NULL ) {
		global $wgCanonicalNamespaceNames; // for validating index
		$page_tab = '<span style="color:darkred;font-weight:800">
		[subpages] Warning: invalid namespace "'.$NS.'"</span>';

		// figure out if $NS is name, number, or null
		$ns_parent = $parser->getTitle()->getNamespace();
		if ( !is_numeric( $NS ))
			if ( !empty( $NS ))
				$ns_child = Namespace::getCanonicalIndex( strtolower( $NS ) );
			else $ns_child = $ns_parent;
		else $ns_child = intval( $NS );

		// validate ns and set caption
		if ( NS_MAIN != $ns_child ) // 0 is valid, but not in ns array
//			if ( 100 !== $ns_child )
			if ( is_null( $ns_child )
					|| !array_key_exists( $ns_child, $wgCanonicalNamespaceNames ))
				return $page_tab; // invalid ns
			else $ns_caption = Namespace::getCanonicalName( $ns_child );
//			else $ns_caption = 'Faculty';
		else $ns_caption = "Page";
		$subpage = $ns_child == $ns_parent; // false == not a subpage

		// select parent's title + one path name
		$title_parent = str_replace( " ","_", $parser->getTitle()->getText() );
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'page' ),
			array( 'DISTINCT ON(page_title,page_namespace) page_title'/*, 'page_namespace'*/ ),
			array(
				'page_title ~ \'^'.$title_parent.'/[^/]+$\'',
				'page_namespace =  \''.$ns_child.'\'',
				'page_is_redirect = \'0\''),'',
			array( 'ORDER BY' => 'page_title ASC')/*,
			__METHOD__*/);
		$rows = $dbr->numRows( $res );
		// setup subpage table with one row and three columns
		if ( 0 < $rows ) {
			$limit = $rows / 3; // three columns per row
			$page_tab = '
<table width="100%">
	<caption style="font-size:14pt">'
		.$ns_caption.' Sub-pages ('.$rows.')</caption>
	<tr>';
		// set link as either subpage, namespace, or main
			$link_prefix = '
			<li>[[';
			if ( !$subpage )
				if ( NS_MAIN != $ns_child )
					$link_prefix .= ':'.$ns_caption. ':' .$title_parent.'/';
				else $link_prefix .= $title_parent.'/';
			else $link_prefix .= '/';
			$link_suffix = ']]</li>';

			// names ordered by column and then row
			for( $col = 0; 3 > $col; ++$col ) {
				$page_tab .= '
		<td valign="top">';
				$ul = false;
			// NOTE: the list may have less than $limit objects
			// NOTE: $row is the list index, not the db row
				for( $row = 0; $limit > $row; ++$row ) {
					$x =& $dbr->fetchObject( $res );
					if ( $x ) {
						if ( !$ul ) {// open list
							$page_tab .= "<ul>";
							$ul = true; // remember to close list
						}
					$title_path = explode( '/', $x->page_title );
					$path_count = count( $title_path );
			// if the title is a pathed name, select the rightmost name
					if ( 0 < $path_count )
						$page_name = $title_path[ $path_count - 1 ];
					else
						$page_name = $x->page_title;
			// this is correct: the name must appear twice;
			// 1st for ending the link, 2nd for displayed link name
					if ( !$subpage )
						$page_name .= '|'.$page_name;
					$page_tab .= $link_prefix.$page_name.$link_suffix;
					} else
					{
				if ( $ul ) // open list, close it
					$page_tab .= "
		</ul>";
				$page_tab .= "</td>";
					 break 2; // short list, simply continue
					}
				}
				if ( $ul ) // open list, close it
					$page_tab .= "
		</ul>";
				$page_tab .= "</td>";
			}
			$page_tab .= '
	</tr>
</table>';
		} else $page_tab = ''; // no matches, ignore
		$dbr->freeResult( $res );
		return $page_tab;
	}

	// Return the $index path name in the title.
	function pathname( &$parser, $index, $count = 0 ) {
		$path_names = explode( "/", $parser->getTitle()->getText());
		if ( 0 == $count )
			$count = count( $path_names ) - $index;
		return implode("/", array_slice( $path_names, $index, $count ));
	}

	private
	function unqsort( array $value ) {
		$value = array_values( array_unique( $value ));
		sort( $value );
		return $value;
	}

	// Return a topic module/level table of existing topic pages
	// Depends on the path name of the current page.
	// FIXME: Add style params for table?
	function calendar( &$parser, $NS = NULL, $page_prefix = NULL )
	{
		global $wgCanonicalNamespaceNames; // for validating index
		$caption = 'Calendar';
		$page_tab = '<span style="color:darkred;font-weight:800">
		[calendar] Warning: invalid namespace "'.$NS.'"</span>';

		$ns_child = $this->ns_index( $parser, $NS );

		// validate ns and set caption
		if ( !is_null( $ns_child )
				&& array_key_exists( $ns_child, $wgCanonicalNamespaceNames ))
			if ( NS_MAIN == $ns_child ) // 0 is valid, but not in ns array
				$ns_caption = "Page";
			else
				$ns_caption = Namespace::getCanonicalName( $ns_child );
		else return $page_tab;

		if ( !is_numeric( $page_prefix ))
			if ( is_null( $page_prefix ))
				$title_parent = $parser->getTitle()->getText();
			else
			{
				$title_parent = $page_prefix;
				$caption .= '['.$ns_caption.':'.$title_parent.']';
			}
		else return '<span style="color:darkred;font-weight:800">
		[calendar] Warning: invalid page name "'.$page_prefix.'"</span>';

		if ( !empty( $title_parent ))
		{
		$dbr = wfGetDB( DB_SLAVE );
		$row_res = $dbr->select(
			array( 'page' ),
			array( 'page_title', 'page_namespace', 'page_is_redirect' ),
			array( 
				'page_title ~ \''.$title_parent.'/[^/]+/[^/]+$\'',
				'page_namespace =  \''.$ns_child.'\''),
			array( 'ORDER BY' => 'page_title ASC'),
			__METHOD__);
		$row_count = $dbr->numRows( $row_res );
		} else return 'no parent page'; // no parent page

		if ( 0 == $row_count )
		// ..erm, free row_res first???
			return 'No modules found for '.$ns_caption.':'.$title_parent;
		// Create a keyed boolean table array, where the columns are
		// the schema and rows are the modules. A tab_array cell is true
		// if the schema is in the module's path.
		$col_headers = array(); // schema, e.g. 101, 102, ...
		$row_headers = array(); // modules, e.g. cooking, baking, ...
		for ( $row = 0; $row_count > $row; ++$row  ) {
			$x = $dbr->fetchObject( $row_res );
			$path = array_slice( explode( "/", $x->page_title ), -2, 2 );
			$col_headers[] = $path[ 0 ];
			$row_headers[] = $path[ 1 ];
			$tab_array[ $path[ 0 ]][ $path[1] ] = $x->page_is_redirect;
		}
		$dbr->freeResult( $row_res );
		// clean up duplicates and rekey ...
//		echo $col_headers."= ".count($col_headers)."<br>";
		$col_headers = $this->unqsort( $col_headers );
		// must be 1st and out-of-order!
		array_unshift( $col_headers, 'Module/Level');
		$row_headers = $this->unqsort( $row_headers );
		// create table and column headers (as topic links)
		$page_tab = '
<table width="100%" border="2">
	<caption style="font-size:14pt">'.$caption.'</caption>';
		$page_tab .= '<tr>';
		foreach( $col_headers as $key => $header ) 
		{
			if ( 0 == $key )
				$page_tab .= '<th>'.$header.'</th>';
			else
//				if ( empty( $levels ) || in_array( $header, $level_list ))
					$page_tab .= '<th>[['.$ns_caption.':'.$title_parent.'/'.$header.'|'.$header.']]</th>';
		}
		$page_tab .= '
		</tr>';
		// For every tab_array[schema][module] == true, create topic link
		// else create n/a data tag.
		for ( $row = 0; count( $row_headers) > $row; ++$row ) 
		{
			$row_key = $row_headers[ $row ];
//			if ( empty( $modules ) || in_array( $row_key, $module_list ))
//			{
			$page_tab .= '
		<tr>';
			for ( $col = 0; count( $col_headers) > $col; ++$col ) 
			{
				$col_key = $col_headers[ $col ];
				if ( 0 == $col ) // module name collumn
				{
					$page_tab .= '
			<td>'.$row_key.'</td>';
				}
				else 
				if ( empty( $levels ) || in_array( $col_key, $level_list ))
				{ // schema collumn
		// NOTE: tab_array is sparse, so ignore index warnings
					$value = @$tab_array[ $col_key ][ $row_key ];
					if ( !is_null( $value ))
						if ( 0 == intval( $value ) )
							$page_tab .= '
			<td style="text-align:center">[['.$ns_caption.':'
							.$title_parent.'/'.$col_key.'/'.$row_key.'|Available]]</td>';
						else 
							$page_tab .= '
			<td style="text-align:center">[['.$ns_caption.':'
							.$title_parent.'/'.$col_key.'/'.$row_key.'|redirect]]</td>';
					else
						$page_tab .= '
			<td style="text-align:center">N/A</td>';
				}
			}
			$page_tab .= '
		</tr>';
		}//}
		$page_tab .= '
<table>';
		return $page_tab;
	}

	public
	function user (&$parser)
	{
		global $wgUser;
		$parser->disableCache();
		if ($wgUser->isAnon()) {
			return 'anon';
		}
		return $wgUser->getName();
	}
}


// register the extension
require_once( pfSubPages::register() );
?>
