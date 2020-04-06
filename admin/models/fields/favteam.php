<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       favteam.php
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
 * FormFieldFavteam
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class JFormFieldFavteam extends \JFormFieldList
{
    /**
     * field type
     *
     * @var string
     */
    public $type = 'Favteam';

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
          // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $view = $jinput->getCmd('view');
        $layout = $jinput->getCmd('layout');
        $id = $jinput->getVar('id', '0');
      
        // Initialize variables.
        $options = array();

        $varname = (string) $this->element['varname'];
      
        if ($layout == 'edit' ) {
            $project_id = $id;  
        }
        else
        {
            $project_id = $app->getUserState("$option.pid", '0');  
        }
      

        if ($project_id) {      
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
          
            $query->select('t.id AS value, t.name AS text');
            $query->from('#__sportsmanagement_team AS t');
            $query->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
            $query->where('pt.project_id = '.$project_id);
            $query->order('t.name');
            $db->setQuery($query);
            $options = $db->loadObjectList();
        }
      
        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }
}
