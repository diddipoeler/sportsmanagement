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
$this->jsmapp = JFactory::getApplication();
// JInput object
$this->jsmjinput = $this->jsmapp->input;
$this->jsmoption = $this->jsmjinput->getCmd('option');
$this->jsmview = $this->jsmjinput->getCmd('view');    
var $_identifier = $this->jsmview;
var $limitstart = 0;
var $limit = 0;
var $_total = null;
var $_pagination = null;    
    
}


class JSMModelLegacy extends JModelLegacy
{
    

    
}
