<?php
/**
 * @author Jean-Lou Dupont
 * @package PreloadManager
 * @version $Id$
 */
//<source lang=php>
class PreloadManager {
	
	const defaultNs = "Main";
	const basePage  = 'PreloadManager/';
	
	/**
	 * Preload hook.
	 */
	public function hEditFormPreloadText( &$text, &$title ) {
		
		$text = $this->loadTemplate( $title );
		
		return true;
	}
	/**
	 * Loads a 'template' based on the title's namespace
	 */
	protected function loadTemplate( &$title ) {
		
		$ns = $title->getNsText();
		if (empty($ns))
			$ns = self::defaultNs;
		
		$tpl_page = self::basePage.$ns;
		
		return $this->getPageContents( NS_MEDIAWIKI, $tpl_page );
	}
	/**
	 * Returns a page's content or NULL
	 */
	protected function getPageContents( $ns, $page ) {
		
		$title = Title::newFromText( $page, $ns );
		if (!is_object( $title ))		
			return null;
			
		$contents = null;

		$rev = Revision::newFromTitle( $title );
		if( is_object( $rev ) )
		    $contents = $rev->getText();		
		
		return $this->getIncludeOnly( $contents );
	}	
	/**
	 * Only 1 includeonly section supported.
	 */
	protected function getIncludeOnly( &$contents )	 {
		
		$result = preg_match( '/<includeonly>(.*)<\/includeonly>/si', $contents, $matches );
		if ( $result !== 1 )
			return null;
		return $matches[1];
	}
	
} // end class
//</source>