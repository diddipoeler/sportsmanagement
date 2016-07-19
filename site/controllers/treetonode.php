<?php
/**
 * @copyright	Copyright (C) 2006-2014 joomleague.at. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

class JoomleagueControllerTreetonode extends JoomleagueController
{
	public function display($cachable = false, $urlparams = false)
	{
		// Get the view name from the query string
		$viewName = JRequest::getVar( 'view', 'treetonode' );

		// Get the view
		$view = $this->getView( $viewName );

		// Get the joomleague model
		$jl = $this->getModel( 'project', 'JoomleagueModel' );
		$jl->set( '_name', 'project' );
		if (!JError::isError( $jl ) )
		{
			$view->setModel ( $jl );
		}

		// Get the joomleague model
		$sm = $this->getModel( 'treetonode', 'JoomleagueModel' );
		$sm->set( '_name', 'treetonode' );
		if ( !JError::isError( $sm ) )
		{
			$view->setModel ( $sm );
		}

		$this->showprojectheading();
		$view->display();
		$this->showbackbutton();
		$this->showfooter();
	}
}
