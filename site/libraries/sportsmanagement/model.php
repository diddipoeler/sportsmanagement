<?PHP        
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      model.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage libraries
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');
jimport('joomla.application.component.modellist');
jimport('joomla.application.component.model');

class JSMModelList extends JModelList
{
// Reference global application object
$this->jsmapp = JFactory::getApplication('site');
// JInput object
$this->jsmjinput = $this->jsmapp->input;
$this->jsmoption = $this->jsmjinput->getCmd('option');
$this->jsmview = $this->jsmjinput->getCmd('view');    
/*
var $_identifier = $this->jsmview;
static $limitstart = 0;
static $limit = 0;
var $_total = null;
var $_pagination = null;    
*/
/*    
protected function populateState($ordering = 'obj.name', $direction = 'asc')
	{
	   if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info') )
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''),'');
        }
		// Load the filter state.
        $value = $this->getUserStateFromRequest($this->context . '.list.limit', 'limit', $this->jsmapp->get('list_limit'), 'int');
		$this->setState('list.limit', $value);	
    self::$limit = $value;	
		// List state information.
		parent::populateState($ordering, $direction);
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);
    self::$limitstart = $value;	
	}    
    */
    
    
}


class JSMModelLegacy extends JModelLegacy
{
    

    
}
