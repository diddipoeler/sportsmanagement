<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       personlist.php
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

FormHelper::loadFieldClass('list');

/**
 * FormFieldpersonlist
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldpersonlist extends \JFormFieldList
{
    /**
     * field type
     *
     * @var string
     */
    public $type = 'personlist';

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
        $option = Factory::getApplication()->input->getCmd('option');
        /**
 *          Initialize variables.
 */
        $options = array();
  
          $db = Factory::getDbo();
         $query = $db->getQuery(true);
          
         $query->select("id AS value, concat(lastname,' - ',firstname,'' ) AS text");
         $query->from('#__sportsmanagement_person ');
         $query->order('lastname');
         $db->setQuery($query);
         $options = $db->loadObjectList();
  
  
        /**
 *          Merge any additional options in the XML definition.
 */
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
