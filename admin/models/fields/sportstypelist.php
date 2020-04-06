<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       sportstypelist.php
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
use Joomla\CMS\Language\Text;
jimport('joomla.filesystem.folder');
FormHelper::loadFieldClass('list');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!class_exists('sportsmanagementHelper') ) {
    //add the classes for handling
    $classpath = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components/com_sportsmanagement'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
}

/**
 * FormFieldsportstypelist
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldsportstypelist extends \JFormFieldList
{
    /**
     * field type
     *
     * @var string
     */
    public $type = 'sportstypelist';

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
          $lang = Factory::getLanguage();
          $db = sportsmanagementHelper::getDBConnection(false, false);
         $query = $db->getQuery(true);
          
         $query->select('id AS value, name AS text');
         $query->from('#__sportsmanagement_sports_type');
         $query->order('name');
         $db->setQuery($query);
         $options = $db->loadObjectList();
  
          $extension = "COM_SPORTSMANAGEMENT";
        $source = JPATH_ADMINISTRATOR . '/components/' . $extension;
        $lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
        ||    $lang->load($extension, $source, null, false, false)
        ||    $lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
        ||    $lang->load($extension, $source, $lang->getDefault(), false, false);
      
        foreach ( $options as $row )
          {
            $row->text = Text::_($row->text);
        }
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
