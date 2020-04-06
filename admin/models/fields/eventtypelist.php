<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       eventtypelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldeventtypelist
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldeventtypelist extends \JFormFieldList
{
    
    /**
     * field type
     *
     * @var string
     */
    public $type = 'eventtypelist';

    /**
     * Method to get the field options.
     *
     * @return array  The field option objects.
     *
     * @since 11.1
     */
    protected function getOptions()
    {
        // Reference global application object
        $this->jsmapp = Factory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        // Initialize variables.
        $options = array();
          $db = Factory::getDbo();
         $query = $db->getQuery(true);
            
         $query->select('pos.id AS value, pos.name AS text');
         $query->from('#__sportsmanagement_eventtype as pos');
         $query->where('pos.published = 1');
         $query->order('pos.ordering,pos.name');
         $db->setQuery($query);
                    
        try { 
            $options = $db->loadObjectList();
        }
        catch (Exception $e) {
             Log::add(Text::_($e->getMessage()), Log::NOTICE, 'jsmerror');
        }
            
        foreach ( $options as $row )
            {
            $row->text = Text::_($row->text);
        }
    
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
