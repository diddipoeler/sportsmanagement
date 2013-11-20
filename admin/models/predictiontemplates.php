<?php



// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.modellist' );



class sportsmanagementModelPredictionTemplates extends JModelList
{

	var $_identifier = "predictiontemplates";
	
	

	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
        
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $query->select(array('tmpl.*', 'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_template AS tmpl')
        ->join('LEFT', '#__users AS u ON u.id = tmpl.checked_out');

        if ($where)
        {
            $query->where($where);
        }
        if ($orderby)
        {
            $query->order($orderby);
        }

		
		return $query;
	}

	function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'tmpl_filter_order',		'filter_order',		'tmpl.title',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'tmpl_filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		$where = array();
		$prediction_id = (int) $mainframe->getUserState( 'com_joomleague' . 'prediction_id' );
		if ( $prediction_id > 0 )
		{
			$where[] = 'tmpl.prediction_id = ' . $prediction_id;
		}
		$where 	= ( count( $where ) ? ''. implode( ' AND ', $where ) : '' );

		return $where;
	}

	function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'tmpl_filter_order',		'filter_order',		'tmpl.title',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'tmpl_filter_order_Dir',	'filter_order_Dir',	'',				'word' );

		if ( $filter_order == 'tmpl.title' )
		{
			$orderby 	= 'tmpl.title ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= '' . $filter_order . ' ' . $filter_order_Dir . ' , tmpl.title ';
		}

		return $orderby;
	}

	

	
	/**
	 * check that all prediction templates in default location have a corresponding record, except if game has a master template
	 *
	 */
	function checklist()
	{
	  $mainframe		=& JFactory::getApplication();
      $option = JRequest::getCmd('option');
		$prediction_id	= $this->_prediction_id;
		//$defaultpath	= JLG_PATH_EXTENSION_PREDICTIONGAME.DS.'settings';
		$defaultpath	= JPATH_COMPONENT_SITE.DS.'settings';
    //$extensionspath	= JPATH_COMPONENT_SITE . DS . 'extensions' . DS;
    // Get the views for this component.
	$path = JPATH_SITE.'/components/'.$option.'/views';
        
		$templatePrefix	= 'prediction';
//    $defaultvalues = array();
    
		if (!$prediction_id){return;}

		// get info from prediction game
		$query = 'SELECT master_template 
					FROM #__joomleague_prediction_game 
					WHERE id = ' . (int) $prediction_id;

		$this->_db->setQuery($query);
		$params = $this->_db->loadObject();

		// if it's not a master template, do not create records.
		if ($params->master_template){return true;}

		// otherwise, compare the records with the files // get records
		$query = 'SELECT template 
					FROM #__joomleague_prediction_template 
					WHERE prediction_id = ' . (int) $prediction_id;

		$this->_db->setQuery($query);
		$records = $this->_db->loadResultArray();
		if (empty($records)){$records=array();}
    
		// add default folder
		$xmldirs[] = $defaultpath . DS . 'default';

		// now check for all xml files in these folders
		foreach ($xmldirs as $xmldir)
		{
			if ($handle = opendir($xmldir))
			{
				/* check that each xml template has a corresponding record in the
				database for this project. If not, create the rows with default values
				from the xml file */
				while ($file = readdir($handle))
				{
					if ($file!='.'&&$file!='..'&&strtolower(substr($file,(-3)))=='xml'&&
						strtolower(substr($file,0,strlen($templatePrefix)))==$templatePrefix)
					{
						$template = substr($file,0,(strlen($file)-4));
                        
                        //$mainframe->enqueueMessage(JText::_('PredictionGame template -> '.$template),'');
                        
						// Determine if a metadata file exists for the view.
				        //$metafile = $path.'/'.$template.'/metadata.xml';
                        $metafile = $path.'/'.$template.'/tmpl/default.xml';
                        $attributetitle = '';
                        if (is_file($metafile)) 
                        {
                        // Attempt to load the xml file.
					   if ($metaxml = simplexml_load_file($metafile)) 
                        {
                        //$mainframe->enqueueMessage(JText::_('PredictionGame template metaxml-> '.'<br /><pre>~' . print_r($metaxml,true) . '~</pre><br />'),'');    
                        // This will save the value of the attribute, and not the objet
                        //$attributetitle = (string)$metaxml->view->attributes()->title;
                        $attributetitle = (string)$metaxml->layout->attributes()->title;
                        //$mainframe->enqueueMessage(JText::_('PredictionGame template attribute-> '.'<br /><pre>~' . print_r($attributetitle,true) . '~</pre><br />'),'');
                        if ($menu = $metaxml->xpath('view[1]')) 
                        {
							$menu = $menu[0];
                            //$mainframe->enqueueMessage(JText::_('PredictionGame template menu-> '.'<br /><pre>~' . print_r($menu,true) . '~</pre><br />'),'');
                            }
                        }
                        }
                        
                        if ((empty($records)) || (!in_array($template,$records)))
						{
						  $jRegistry = new JRegistry();
							$form = JForm::getInstance($file, $xmldir.DS.$file);
							$fieldsets = $form->getFieldsets();
							
							//echo 'fieldsets<br /><pre>~' . print_r($fieldsets,true) . '~</pre><br />';
							//echo 'form<br /><pre>~' . print_r($form,true) . '~</pre><br />';
							
							$defaultvalues = array();
							foreach ($fieldsets as $fieldset) 
              {
								foreach($form->getFieldset($fieldset->name) as $field) 
                {
									//echo 'field<br /><pre>~' . print_r($field,true) . '~</pre><br />';
                  $jRegistry->set($field->name, $field->value);
                  $defaultvalues[] = $field->name.'='.$field->value;
								}				
							}
							$defaultvalues = $jRegistry->toString('ini');
                            
//$mainframe->enqueueMessage(JText::_('defaultvalues -> '.'<pre>'.print_r($defaultvalues,true).'</pre>' ),'');
                            
							$defaultvalues = ereg_replace('"', '', $defaultvalues);
                            //$defaultvalues = preg_replace('"', '', $defaultvalues);
							//$defaultvalues = implode('\n', $defaultvalues);
							//echo 'defaultvalues<br /><pre>~' . print_r($defaultvalues,true) . '~</pre><br />';
							
							$tblTemplate_Config = JTable::getInstance('predictiontemplate', 'table');
							$tblTemplate_Config->template = $template;
                            if ( $attributetitle )
                            {
                                $tblTemplate_Config->title = $attributetitle;
                            }
                            else
                            {
                                $tblTemplate_Config->title = $file;
                            }
							
							$tblTemplate_Config->params = $defaultvalues;
							$tblTemplate_Config->prediction_id = $prediction_id;
							/*
							// Make sure the item is valid
							if (!$tblTemplate_Config->check())
							{
								$this->setError($this->_db->getErrorMsg());
								return false;
							}
					    */
							// Store the item to the database
							if (!$tblTemplate_Config->store())
							{
								$this->setError($this->_db->getErrorMsg());
								return false;
							}
							array_push($records,$template);
							
							/*
              //template not present, create a row with default values
							$params = new JLParameter(null, $xmldir . DS . $file);

							//get the values
							$defaultvalues = array();
							foreach ($params->getGroups() as $key => $group)
							{
								foreach ($params->getParams('params',$key) as $param)
								{
									$defaultvalues[] = $param[5] . '=' . $param[4];
								}
							}
							$defaultvalues = implode('\n', $defaultvalues);

							$title = JText::_($params->name);
							$query =	"	INSERT INTO #__joomleague_prediction_template (title, prediction_id, template, params)
											VALUES ( '$title', '$prediction_id', '$template', '$defaultvalues' )";

							$this->_db->setQuery($query);
							//echo error, allows to check if there is a mistake in the template file
							if (!$this->_db->query())
							{
								$this->setError($this->_db->getErrorMsg());
								return false;
							}
							array_push($records,$template);
							*/
						}
					}
				}
				closedir($handle);
			}
		}
	}

}
?>