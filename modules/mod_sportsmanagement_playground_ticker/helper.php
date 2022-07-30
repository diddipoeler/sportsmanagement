<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.06
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_playground_ticker
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @modded 	   llambion (2020)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * modJSMPlaygroundTicker
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMPlaygroundTicker
{

	/**
	 * modJSMPlaygroundTicker::getData()
	 *
	 * @param   mixed  $params
	 *
	 * @return void
	 */
	public static function getData($params)
	{
		$app = Factory::getApplication();
		
		// llambion
		$project = (int) $params->get('p');
		$x = $params->get('limit', 1);

		// JInput object
		$jinput             = $app->input;
		$cfg_which_database = $jinput->getInt('cfg_which_database', 0);

		// Get a db connection.
		$db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);

		// Create a new query object.
		$query = $db->getQuery(true);

		/**
		 * Nun möchte man aber manchmal mehrere Datensätze zufällig selektieren und nicht nur einen.
		 * Zuerst wird die Gesamtanzahl an Datensätzen bestimmt, die die Bedingungen erfüllt.
		 * Anschließend müssen x Zufallszahlen gebildet werden. Und mit diesen wird dann eine SQL-Abfrage mit UNIONs gebaut.
		 */
		// Select some fields
		
		// 2020.07.22 All estadiums or only certain 
		
		
		if ($project < 1)
	    {
			$query->select('count( * )');

			// From table
			$query->from('#__sportsmanagement_playground');
			$db->setQuery($query);
			$anz_cnt = $db->loadResult();

			/**
			* Die Schleife beim Erhalten der Zufallszahlen ist deshalb eine while- und keine for-Schleife, weil es sonst passieren kann,
			* dass es zwar mehr als x Datensätze gibt, die die Bedingungen erfüllen, aber dummerweise 2 mal die gleiche Zufallszahl ermittelt wird.
			* Die Bedingung $anz_cnt>count($rands) dient dazu, dass keine Endlosschleife entsteht, wenn weniger als x Datensätze die Bedingung erfüllen.
			* Bei der abschließenden Abfrage wird UNION ALL benutzt statt UNION, damit MySQL die Einzelergebnisse nicht noch versucht zu gruppieren
			* wir wissen ja durch die while-Schleife bereits, dass keine Duplikate selektiert werden können). UNION bedeutet nämlich in Wirklichkeit UNION DISTINCT.
			*/
			$rands = array();

			while (count($rands) < $x && $anz_cnt > count($rands))
			{
				$rand = mt_rand(0, $anz_cnt - 1);

				if (!isset($rands[$rand]))
				{
					$rands[$rand] = $rand;
				}
			}

		}
		
		else // added 20.07.24 Llambion
			
		{		
			/* Select playgrounds that belong to certain proyect  */

			$query->select('estadio.id');
			
			$query->from('#__sportsmanagement_playground AS estadio');
			
			$query->join('INNER','#__sportsmanagement_project_team t ON estadio.id = t.standard_playground');
		
			$query->where('t.project_id = '. $project);
		
			$db->setQuery($query); 
				
			$result = $db->loadObjectList(); 
		
			$estadios = count($result);
			
			if ($estadios > 0) 
			
			{
			
			// Search id's for random values
		
			$rands = array();
			$x     = $params->get('limit', 1);

			while (count($rands) < $x && $estadios > count($rands))
			{
				$rand = mt_rand(0, $estadios - 1);
			
				if (!isset($rands[$rand]))
				{
				$rands[$rand] = $result[$rand]->id;
				}
			}

            }
			
		}

        //echo 'rands' . count($rands);

		$queryparts = array();

        // Changed to consider all or only project stadiums
		
		if (count($rands) > 0)
		
	    {
		
		foreach ($rands as $rand)
		{
			if ($project = 0)
			{			
				$queryparts[] = "SELECT * FROM #__sportsmanagement_playground LIMIT " . $rand .
					",1";
			}
			else
			{
				$queryparts[] = "SELECT pg.id AS id_playground, pg.name as playground_name, pg.address, pg.zipcode, pg.city, pg.country, 
										pg.club_id, pg.extended, pg.latitude, pg.longitude, pg.state, pg.picture, pg.website, pg.max_visitors,
										cl.name AS club_name
										FROM #__sportsmanagement_playground AS pg 
										INNER JOIN #__sportsmanagement_club AS cl 
										ON pg.club_id = cl.id WHERE pg.id=" . $rand . ''  ;
			}
		}

		$query = "(" . implode(") UNION ALL (", $queryparts) . ")";
		
		//echo $project . '<br>';
		//echo $query;
	
		$db->setQuery($query);
		$result = $db->loadObjectList();
		
		}
		
		else
			
			{
				$result = -1;
			}

        //echo 'result' . $result;
		return $result;

	}
	
	// 
	/**
	 * modJSMPlaygroundTicker::getEstadios_Proyecto()
	 *
	 * @param   mixed  $params
	 *
	 * @return estadios
	 */
	public static function getEstadios_Proyecto($params)
	{

		$app = Factory::getApplication();

		$p = (int) $params->get('p');
		$x = $params->get('limit', 1);

		// JInput object
		$jinput             = $app->input;
		$cfg_which_database = $jinput->getInt('cfg_which_database', 0);

		// Get a db connection.
		$db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);

		// Create a new query object.
		$query = $db->getQuery(true);

 
        /* Select playgrounds that belong to certain teams proyect  */

		$query->select('estadio.id');
		
		$query->from('#__sportsmanagement_playground AS estadio');
		
		$query->join('INNER','#__sportsmanagement_project_team t ON estadio.id = t.standard_playground');
		
		$query->where('t.project_id = '. $p);
		
		$db->setQuery($query); 
				
		$result = $db->loadObjectList(); 
		
		$estadios = count($result);
		
		if ($estadios > 0 )
			
			{
		
			// Busco los numeros aleatorios y extraigo los campos que me interesan
		
			$rands = array();
			$x     = $params->get('limit', 1);

			while (count($rands) < $x && $estadios > count($rands))
			{
				$rand = mt_rand(0, $estadios - 1);
			
				if (!isset($rands[$rand]))
				{
				
					$rands[$rand] = $result[$rand]->id;
				}
			}

			$queryparts = array();

			foreach ($rands as $rand)
			{
				$queryparts[] = "SELECT * FROM #__sportsmanagement_playground WHERE id=" . $rand ;
			}

			$query = "(" . implode(") UNION ALL (", $queryparts) . ")";
		
			//echo $query;
		
			$db->setQuery($query);
			$result = $db->loadObjectList();							
			
			}
		else
			{
			 $result = -1;	
			}	
						
		return $result;
	}

}