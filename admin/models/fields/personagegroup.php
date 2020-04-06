<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       personagegroup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage fields
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

/**
 * FormFieldpersonagegroup
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldpersonagegroup extends \JFormFieldList
{
    /**
     * field type
     *
     * @var string
     */
    public $type = 'personagegroup';
    /**
     * Method to get the field options.
     *
     * @return array  The field option objects.
     *
     * @since 11.1
     */
    protected function getOptions()
    {
        $app = Factory::getApplication();
        $db = Factory::getDbo();
         $query = $db->getQuery(true);
         $personquery = $db->getQuery(true);
         $personquery2 = $db->getQuery(true);
          
         $person_age_range = $db->getQuery(true);
          
          // Initialize variables.
        $options = array();

          $varname = (string) $this->element['varname'];
          $vartable = (string) $this->element['targettable'];
          // Get some field values from the form.
          $select_id    = (int) $this->form->getValue('id');
        $agegroup_id    = (int) $this->form->getValue('agegroup_id');

         $query->select('a.id AS value, concat(a.name, \' von: \',a.age_from,\' bis: \',a.age_to,\' Stichtag: \',a.deadline_day) AS text');
         $query->from('#__sportsmanagement_agegroup as a');
            $query->join('INNER', '#__sportsmanagement_'.$vartable.' AS t on t.sports_type_id = a.sportstype_id');
            $query->where('t.id = '.$select_id);
         $query->order('name');
         $db->setQuery($query);
         $options_select = $db->loadObjectList();
      
        foreach($options_select as $row)
         {
   
            $options[] = HTMLHelper::_('select.option', $row->value, $row->text);
   
        }
          
  
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
      
        //return HTMLHelper::_('select.genericlist', $options, 'month', 'class="inputbox"', 'value', 'text', $person_range);
        return $options;
    }
  
}



?>    