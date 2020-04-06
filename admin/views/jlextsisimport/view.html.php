<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage jlextsisimport
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;



/**
 * sportsmanagementViewjlextsisimport
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewjlextsisimport extends sportsmanagementView
{
    /**
     * sportsmanagementViewjlextsisimport::init()
     *
     * @return
     */
    public function init()
    {
      
        switch ( $this->getLayout() )
        {
        case 'default':
        case 'default_3':
        case 'default_4':
            $this->_displayDefault($tpl);
            return;
            break;
        }
     
        // Set toolbar items for the page
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $uri = Factory::getURI();
        $config = ComponentHelper::getParams('com_media');
        $post = $jinput->post->getArray(array());
        $files = $jinput->get('files');
      
  
        $revisionDate = '2011-04-28 - 12:00';
        $this->revisionDate    = $revisionDate;
      
      
    }
  
  
  
    /**
     * sportsmanagementViewjlextsisimport::_displayDefault()
     *
     * @param  mixed $tpl
     * @return void
     */
    function _displayDefault($tpl)
    {
        //global $option;
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db = Factory::getDBO();
        $uri = Factory::getURI();
        $user = Factory::getUser();
      
        // $model = $this->getModel('project') ;
        // $projectdata = $this->get('Data');
        // $this->assignRef( 'name', $projectdata->name);
      
        $model = $this->getModel();
        $project = $app->getUserState($option . 'project');
        $this->project    = $project;
        $config = ComponentHelper::getParams('com_media');
        $params = ComponentHelper::getParams($option);
        $sis_xmllink    = $params->get('sis_xmllink');
        $sis_nummer    = $params->get('sis_meinevereinsnummer');
        $sis_passwort    = $params->get('sis_meinvereinspasswort');
     
        $revisionDate = '2011-04-28 - 12:00';
        $this->revisionDate    = $revisionDate;
        $import_version = 'NEW';
        $this->import_version    = $import_version;
      
  
    }
  
  
    /**
     * sportsmanagementViewjlextsisimport::_displayDefaultUpdate()
     *
     * @param  mixed $tpl
     * @return void
     */
    function _displayDefaultUpdate($tpl)
    {
        // global $app, $option;
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
      
        $db = Factory::getDBO();
        $uri = Factory::getURI();
        $user = Factory::getUser();
        $model = $this->getModel();
        $project = $app->getUserState($option . 'project');
        $this->project    = $project;
        $config = ComponentHelper::getParams('com_media');
      
        $uploadArray = $app->getUserState($option . 'uploadArray', array ());
        $lmoimportuseteams = $app->getUserState($option . 'lmoimportuseteams');
        $whichfile = $app->getUserState($option . 'whichfile');
      
        $this->uploadArray    = $uploadArray;
      
        $this->importData    = $model->getUpdateData();
      

    }
  
  
  
    /**
     * sportsmanagementViewjlextsisimport::addToolbar()
     *
     * @return void
     */
    protected function addToolbar()
    {
          
        parent::addToolbar();          

    }
}

?>
