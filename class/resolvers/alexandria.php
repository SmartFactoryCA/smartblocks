<?php
if (!defined("XOOPS_ROOT_PATH")) {
    die("Cannot access file directly");
}
require_once(XOOPS_ROOT_PATH."/modules/smartblocks/class/resolver.php");
class AlexandriaResolver extends SmartblocksResolver {
    var	$locations;
    var	$pages;

    function AlexandriaResolver() {
        $this->pages=array();
    }

    /**
		 * Return location ID (category ID)
		 *
		 * @return int
		 */
    function resolveLocation() {
        if(strstr($_SERVER['SCRIPT_NAME'],"page.php")) {
            $myts =& MyTextSanitizer::getInstance();
            $page_handler = xoops_getmodulehandler('page', 'alexandria');
            $page = $page_handler->getByName($myts->addSlashes($_REQUEST['name']));
            return $page->getVar('page_id');
        }

        return 0;
    }

    /**
	 * Get all locations in the module
	 *
	 * @return array
	 */
    function getLocations() {
        if(sizeof($this->pages)==0) {
            $this->getPageHelper(0,0);
        }
        return($this->pages);
    }


    /**
		 * Return the name of a location
		 *
		 * @param int $location
		 * @return string
		 */
    function getLocationName($location) {
        $page_handler =& xoops_getmodulehandler('page', 'alexandria');
        $page = $page_handler->get($location, false);
        return $page['page_title'];
    }

    /**
		 * Returns a list of all parent elements of the current location
		 * (including the location itself)
		 *
		 * @param int $location
		 * @return array
		 */
    function getLocationPath($location) {
        $this->locations = array();
        $this->getLocationHelper($location);
        return $this->locations;
    }

    /**
		 * Private helper functions for traversing the category tree
		 *
		 * @param int $parentid ID to start from
		 * @param int $level used in recursions
		 * @param int $offset integer added to location
		 *
		 * @return void
		 */
    function getPageHelper($parentid=0,$level=0) {
        $page_handler =& xoops_getmodulehandler('page', 'alexandria');
        $criteria = new CriteriaCompo();
        $criteria->setSort('page_title');
        $page_arr =& $page_handler->getObjects($criteria);
        include_once(XOOPS_ROOT_PATH."/class/tree.php");
        $tree = new XoopsObjectTree($page_arr, 'page_id', 'page_parent');

        $this->traversePageTree($tree, $parentid, $level);
    }

    /**
		 * Add pages to $this->pages from a category tree
		 *
		 * @param XoopsObjectTree $tree
		 * @param int $parentid key to start from
		 * @param int $level
		 * @param int $offset integer added to location
		 *
		 * @return void
		 */
    function traversePageTree($tree, $parentid=0, $level = 0) {
        $pages =& $tree->getFirstChild($parentid);
        $level++;
        foreach (array_keys($pages) as $i) {
            $this->pages[] = array(   'location' => ($pages[$i]->getVar('page_id')),
                                      'name' => $pages[$i]->getVar('page_title', 'n'),
                                      'level' => $level);
            $this->traversePageTree($tree, $pages[$i]->getVar('page_id'), $level);
        }
    }

    /**
		 * Add locations to $this->locations
		 *
		 * @param int $page_id
		 * @param int $offset integer to add to location
		 *
		 * @return void
		 */
    function getLocationHelper($page_id) {
        $page_handler =& xoops_getmodulehandler('page', 'alexandria');
        $page =& $page_handler->get($page_id);
        if(!$page->isNew()) {
            $this->locations[] = $page->getVar('page_id');
            if($page->getVar('page_parent') != 0) {
                $this->getLocationHelper($page->getVar('page_parent') );
            }
        }
    }
}

$resolver=& new AlexandriaResolver();
?>
