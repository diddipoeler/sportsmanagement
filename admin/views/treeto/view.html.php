<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage treeto
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewTreeto
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTreeto extends sportsmanagementView
{
	
    /**
     * sportsmanagementViewTreeto::init()
     * 
     * @return
     */
    public function init ()
    //function display( $tpl = null )
	{

		if ( $this->getLayout() == 'edit' || $this->getLayout() == 'edit_3' || $this->getLayout() == 'edit_4' )
		{
			$this->_displayForm(  );
			return;
		}
		elseif ($this->getLayout() == 'gennode' || $this->getLayout() == 'gennode_3' || $this->getLayout() == 'gennode_4' )
		{
			$this->_displayGennode();
			return;
		}
	//	parent::display( $tpl );
	}

	/**
	 * sportsmanagementViewTreeto::_displayForm()
	 * 
	 * @return void
	 */
	function _displayForm()
	{
		$this->setDocument();

	}

	/**
	 * sportsmanagementViewTreeto::_displayGennode()
	 * 
	 * @return void
	 */
	function _displayGennode()
	{
		//$option = Factory::getApplication()->input->getCmd('option');
//		$app = Factory::getApplication();
//		$db = Factory::getDbo();
//		$uri = Factory::getURI();
//		$user = Factory::getUser();
//		$model = $this->getModel();
		
        $this->form = $this->get('Form');
        
        $lists = array();

		$this->treeto = $this->get('Item');
		$projectws = $this->get('Data','project');
		//$this->assignRef('form' 	,$this->get('form'));
        $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
        $mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
	    $this->projectws = $mdlProject->getProject($this->project_id);
		//$this->assignRef('projectws',$projectws);
		$this->lists = $lists;
		//$this->assignRef('treeto',$treeto);

		$this->addToolBar_Gennode();
		//parent::display($tpl);
        $this->setLayout('gennode');  
	}

	/**
	 * sportsmanagementViewTreeto::addToolBar_Gennode()
	 * 
	 * @return void
	 */
	protected function addToolBar_Gennode()
	{
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE_GENERATE'));
		ToolbarHelper::back('Back','index.php?option=com_sportsmanagement&view=treetos&task=treeto.display');
	}

	/**
	 * sportsmanagementViewTreeto::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar()
	{
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE'));
		ToolbarHelper::save('treeto.save');
		ToolbarHelper::apply('treeto.apply');
		//ToolbarHelper::back('Back','index.php?option=com_sportsmanagement&view=treetos&task=treeto.display');
	}

	/**
	 * sportsmanagementViewTreeto::setDocument()
	 * 
	 * @return void
	 */
	protected function setDocument()
	{

	}
}
?>