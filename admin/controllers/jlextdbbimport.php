<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextdbbimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

/*
Spielplan - Bayernliga Herren Nord (Senioren; Liganr.: 20001)
http://www.basketball-bund.net/index.jsp?Action=101&liga_id=12150

ergebnisse
http://www.basketball-bund.net/public/ergebnisse.jsp?print=1&viewDescKey=sport.dbb.liga.ErgebnisseViewPublic/index.jsp_&liga_id=12150
spielplan
http://www.basketball-bund.net/public/spielplan_list.jsp?print=1&viewDescKey=sport.dbb.liga.SpielplanViewPublic/index.jsp_&liga_id=12150

*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

/**
 * sportsmanagementControllerjlextdbbimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementControllerjlextdbbimport extends JControllerLegacy
{


	/**
	 * sportsmanagementControllerjlextdbbimport::save()
	 * 
	 * @return
	 */
	function save() {
	   $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication ();
		$document = JFactory::getDocument ();
		// Check for request forgeries
		JFactory::getApplication()->input->checkToken () or die ( 'COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN' );
		$msg = '';
		//JToolbarHelper::back ( JText::_ ( 'COM_SPORTSMANAGEMENT_GLOBAL_BACK' ), JRoute::_ ( 'index.php?option='.$option.'&view=jldfbnetimport' ) );
		// $app = JFactory::getApplication();
		$model = $this->getModel ( 'jlextdbbimport' );
		$post = JFactory::getApplication()->input->get ( 'post' );
		
		//$delimiter = JFactory::getApplication()->input->getVar ( 'delimiter', null );
		$whichfile = JFactory::getApplication()->input->getVar ( 'whichfile', null );
		
		//$app->enqueueMessage ( JText::_ ( 'delimiter ' . $delimiter . '' ), '' );
		//$app->enqueueMessage ( JText::_ ( 'whichfile ' . $whichfile . '' ), '' );
		
		if ($whichfile == 'playerfile') {
			JError::raiseNotice ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_PLAYERFILE' ) );
		} elseif ($whichfile == 'matchfile') {
			JError::raiseNotice ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_MATCHFILE' ) );
			
			if (isset ( $post ['dfbimportupdate'] )) {
				JError::raiseNotice ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_MATCHFILE_UPDATE' ) );
			}
		}
		
		// first step - upload
		if (isset ( $post ['sent'] ) && $post ['sent'] == 1) {
			$upload = JFactory::getApplication()->input->getVar ( 'import_package', null, 'files', 'array' );
			
			$lmoimportuseteams = JFactory::getApplication()->input->getVar ( 'lmoimportuseteams', null );
			
			$app->setUserState ( $option . 'lmoimportuseteams', $lmoimportuseteams );
			$app->setUserState ( $option . 'whichfile', $whichfile );
			$app->setUserState ( $option . 'delimiter', $delimiter );
			
			$tempFilePath = $upload ['tmp_name'];
			$app->setUserState ( $option . 'uploadArray', $upload );
			$filename = '';
			$msg = '';
			$dest = JPATH_SITE . DS . 'tmp' . DS . $upload ['name'];
			$extractdir = JPATH_SITE . DS . 'tmp';
			$importFile = JPATH_SITE . DS . 'tmp' . DS . 'joomleague_import.csv';
			if (JFile::exists ( $importFile )) {
				JFile::delete ( $importFile );
			}
			if (JFile::exists ( $tempFilePath )) {
				if (JFile::exists ( $dest )) {
					JFile::delete ( $dest );
				}
				if (! JFile::upload ( $tempFilePath, $dest )) {
					JError::raiseWarning ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_CANT_UPLOAD' ) );
					return;
				} else {
					if (strtolower ( JFile::getExt ( $dest ) ) == 'zip') {
						$result = JArchive::extract ( $dest, $extractdir );
						if ($result === false) {
							JError::raiseWarning ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_EXTRACT_ERROR' ) );
							return false;
						}
						JFile::delete ( $dest );
						$src = JFolder::files ( $extractdir, 'l98', false, true );
						if (! count ( $src )) {
							JError::raiseWarning ( 500, 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_EXTRACT_NOJLG' );
							// todo: delete every extracted file / directory
							return false;
						}
						if (strtolower ( JFile::getExt ( $src [0] ) ) == 'csv') {
							if (! @ rename ( $src [0], $importFile )) {
								JError::raiseWarning ( 21, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_ERROR_RENAME' ) );
								return false;
							}
						} else {
							JError::raiseWarning ( 500, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_TMP_DELETED' ) );
							return;
						}
					} else {
						if (strtolower ( JFile::getExt ( $dest ) ) == 'csv' || strtolower ( JFile::getExt ( $dest ) ) == 'ics') {
							if (! @ rename ( $dest, $importFile )) {
								JError::raiseWarning ( 21, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_RENAME_FAILED' ) );
								return false;
							}
						} else {
							JError::raiseWarning ( 21, JText::_ ( 'COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT_CTRL_WRONG_EXTENSION' ) );
							return false;
						}
					}
				}
			}
		}
		
		if (isset ( $post ['dfbimportupdate'] )) {
			$link = 'index.php?option='.$option.'&view=jlextdfbnetplayerimport&task=jlextdbbimport.update';
		} else {
			
			if ($whichfile == 'matchfile') {
				$xml_file = $model->getData ();
				$link = 'index.php?option='.$option.'&view=jlxmlimports&task=jlxmlimport.edit';
			} else {
				$xml_file = $model->getData ();
				$link = 'index.php?option='.$option.'&view=jlxmlimports&task=jlxmlimport.edit';
			}
		}
		
		
		
		$this->setRedirect ( $link, $msg );
	}





    
}  

?>  