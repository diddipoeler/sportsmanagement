<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      treetos.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');



/**
 * sportsmanagementModelTreetos
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementModelTreetos extends JModelList
{
	var $_identifier = "treetos";
	static $_project_id = 0;
    
    /**
     * sportsmanagementModelTreetos::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {
            $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
                self::$_project_id = $app->getUserState( "$option.pid", '0' );
                //$config['filter_fields'] = array(
//                        'r.name',
//                        'r.roundcode',
//                        'r.round_date_first',
//                        'r.round_date_last',
//                        'r.id',
//                        'r.ordering'
//                        );
                parent::__construct($config);
        }
        
	/**
	 * sportsmanagementModelTreetos::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
	   $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $search	= $this->getState('filter.search');
	
        $query = JFactory::getDbo()->getQuery(true);
        // Select some fields
		$query->select('tt.*');
		// From the rounds table
		$query->from('#__sportsmanagement_treeto AS tt');
        $query->join('LEFT', '#__sportsmanagement_division d on d.id = tt.division_id');
        $query->where('tt.project_id = ' . self::$_project_id);
		
		return $query;
	}



	/**
	 * sportsmanagementModelTreetos::storeshort()
	 * 
	 * @param mixed $cid
	 * @param mixed $data
	 * @return
	 */
	function storeshort( $cid, $data )
	{
		$result = true;
		for ( $x = 0; $x < count( $cid ); $x++ )
		{
			
			$tblTreeto = JTable::getInstance('Treeto','sportsmanagementTable');
			$tblTreeto->id = $cid[$x];
			$tblTreeto->division_id =	$data['division_id' . $cid[$x]];
			
			if (!$tblTreeto->check())
			{
				$this->setError($tblTreeto->getError());
				$result = false;
			}
			if (!$tblTreeto->store())
			{
				$this->setError($tblTreeto->getError());
				$result = false;
			}
		}
		return $result;
	}

}
?>