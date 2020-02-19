<?PHP        
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      table.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage libraries
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * JSMTable
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class JSMTable extends Table
{


	/**
	 * JSMTable::bind()
	 * 
	 * @param mixed $array
	 * @param string $ignore
	 * @return
	 */
	function bind($array, $ignore = '')
	{
		if (key_exists( 'extended', $array ) && is_array( $array['extended'] ))
		{
			$registry = new Registry();
			$registry->loadArray($array['extended']);
			$array['extended'] = (string) $registry;
		}
		if (key_exists( 'extendeduser', $array ) && is_array( $array['extendeduser'] ))
		{
			$registry = new Registry();
			$registry->loadArray($array['extendeduser']);
			$array['extendeduser'] = (string) $registry;
		}
        
        if (isset($array['season_ids']) && is_array($array['season_ids'])) 
        {
        $array['season_ids'] = implode(',', $array['season_ids']);
        }
        
        if ( key_exists( 'params', $array ) && is_array( $array['params'] ) )
		{
			$registry = new Registry();
			$registry->loadArray( $array['params'] );
			$array['params'] = $registry->toString();
		}
		if ( key_exists( 'comp_params', $array ) && is_array( $array['comp_params'] ) )
		{
			$registry = new Registry();
			$registry->loadArray( $array['comp_params'] );
			$array['comp_params'] = $registry->toString();
		}
      
		return parent::bind($array, $ignore);
	}
    
        
}

?>    