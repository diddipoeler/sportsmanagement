<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      sortorder.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage fields
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
//require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'countries.php');
jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldsortorder
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class JFormFieldsortorder extends JFormFieldList
{
    /**
     * field type
     * @var string
     */
    public $type = 'sortorder';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   11.1
     */
    protected function getOptions()
    {
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $lang = JFactory::getLanguage();
        $options = array();
        $character = array();
        $languages = $lang->getTag();
        
        $template_sort_orders = JComponentHelper::getParams('com_sportsmanagement')->get('template_sort_orders',0);

        for ($i = 1; $i <= $template_sort_orders; $i++) {
            $options[] = JHtml::_('select.option', $i , $i, 'value', 'text');
        }
           
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
