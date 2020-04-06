<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jlextindividualsport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage controllers
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

jimport('joomla.application.component.controller');



/**
 * sportsmanagementControllerjlextindividualsport
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjlextindividualsport extends JController
{




	//    function apply($data)
	//    {
	//        $app = Factory::getApplication();
	//        $option = Factory::getApplication()->input->getCmd('option');
	//        $model = $this->getModel('jlextindividualsport');
	//        $model->apply($data);
	// 
	// 
	//    }

	//    function save($data)
	//    {
	//        $app = Factory::getApplication();
	//        $option = Factory::getApplication()->input->getCmd('option');
	//        $model = $this->getModel('jlextindividualsport');
	//        $model->save($data);
	// 
	// 
	//    }



	/**
	 * sportsmanagementControllerjlextindividualsport::addmatch()
	 *
	 * @return
	 */
	function addmatch()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication();
		$db = Factory::getDbo();

			  // Option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&id=241&team1=23&team2=31&rid=31
		$post = Factory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState("$option.pid", '0');
		$post['round_id'] = $app->getUserState("$option.rid", '0');

		// $post['match_id'] = $post['id'];

			  $model = $this->getModel('jlextindividualsport');

		// $model->addmatch();

			  $row = $model->getTable();

		// Bind the form fields to the table
		if (!$row->bind($post))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		 // Make sure the record is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		// Store to the database
		if ($row->store($post))
		{
			// $msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH'.$db->insertid());
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH' . $db->getErrorMsg());
		}
		else
		{
			$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH') . $model->getError();
		}

			  $link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid=' . $post['round_id'] . '&id=' . $post['match_id'] . '&team1=' . $post['projectteam1_id'] . '&team2=' . $post['projectteam2_id'] . '';
		$this->setRedirect($link, $msg);

			  /*
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        $post=Factory::getApplication()->input->post->getArray(array());

        $post['match_id']		= $app->getUserState( $option . 'match_id',0 );
        $post['project_id']=$app->getUserState($option.'project',0);
        $post['round_id']=$app->getUserState($option.'round_id',0);
        $model=$this->getModel('jlextindividualsport');
        if ($model->store($post))
        {
         $msg=Text::_('COM_SPORTSMANAGEMENT_MATCH_CTRL_ADD_SINGLE_MATCH');
        }
        else
        {
         $msg=Text::_('COM_SPORTSMANAGEMENT_MATCH_CTRL_ERROR_ADD_SINGLE_MATCH').$model->getError();
        }
        $link='index.php?option=com_sportsmanagement&view=jlextindividualsportes';
        $this->setRedirect($link,$msg);
        */

	}


}
