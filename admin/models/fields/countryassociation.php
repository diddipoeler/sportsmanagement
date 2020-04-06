<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       countryassociation.php
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

jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');


/**
 * FormFieldcountryassociation
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldcountryassociation extends \JFormFieldList
{
    /**
     * field type
     *
     * @var string
     */
    public $type = 'countryassociation';

    /**
     * Method to get the field options.
     *
     * @return array  The field option objects.
     *
     * @since 11.1
     */
    protected function getOptions()
    {
        // Initialize variables.
        $options = array();
        $varname = (string) $this->element['varname'];
          $vartable = (string) $this->element['targettable'];
        $select_id = Factory::getApplication()->input->getVar($varname);
        if (is_array($select_id)) {
            $select_id = $select_id[0];
        }
        
        if ($select_id) {        
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            
            $query->select('t.id AS value, t.name AS text');
            $query->from('#__sportsmanagement_associations AS t');
             $query->join('inner', '#__sportsmanagement_'.$vartable.' AS wt ON wt.country = t.country ');
            $query->where('wt.id = '.$select_id);
            $query->order('t.name');
            $db->setQuery($query);
            $options = $db->loadObjectList();
        }
        
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
