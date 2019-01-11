<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jlextsisimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * sportsmanagementControllerjlextsisimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerjlextsisimport extends BaseController
{
	
    /**
     * sportsmanagementControllerjlextsisimport::save()
     * 
     * @return void
     */
    function save() 
    {
	   $option = Factory::getApplication()->input->getCmd('option');
		$app = Factory::getApplication ();
		$document = Factory::getDocument ();
		// Check for request forgeries
		Factory::getApplication()->input->checkToken () or die ( 'COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN' );
		$msg = '';
		// $app = Factory::getApplication();
		$model = $this->getModel ( 'jlextsisimport' );
		$post = Factory::getApplication()->input->get ( 'post' );
		

		
        $xml_file = $model->getData ();
		$link = 'index.php?option='.$option.'&view=jlxmlimports&task=jlxmlimport.edit';
                
		$this->setRedirect ( $link, $msg );
	}





    
}  

?>  