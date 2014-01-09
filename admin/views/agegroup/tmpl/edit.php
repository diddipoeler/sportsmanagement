<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

//echo COM_SPORTSMANAGEMENT_FIELDSETS_TEMPLATE;

?>
<form action="<?php echo JRoute::_('index.php?option=com_sportsmanagement&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" >
 
<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('details') as $field) :?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; 
                
               
                
                ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>

<div class="width-40 fltrt">
		<?php
		echo JHtml::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'details') :
				continue;
			endif;
			echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
		if (isset($fieldset->description) && !empty($fieldset->description)) :
				echo '<p class="tab-description">'.JText::_($fieldset->description).'</p>';
			endif;
		//echo $this->loadTemplate($fieldset->name);
        $this->fieldset = $fieldset->name;
        echo $this->loadTemplate('fieldsets');
		endforeach; ?>
		<?php echo JHtml::_('sliders.end'); ?>

	
	</div>


    
 <div class="clr"></div>
	
 
	<div>
		<input type="hidden" name="task" value="agegroup.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
				<div id="cpanel">
      <div class="icon-wrapper">            
            <div id="icon">
				<?php 
                
                foreach ( $this->sporttypes as $key => $value )
                {
                    //echo $value . '<br>';
                    switch ($value)
                    {
                        case 'soccer':
                        echo $this->addIcon('dfbnetimport.png','index.php?option=com_sportsmanagement&view=jlextdfbnetplayerimport', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBNETIMPORT'));
                        echo $this->addIcon('dfbschluessel.png','index.php?option=com_sportsmanagement&view=jlextdfbkeyimport', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY'));
                        echo $this->addIcon('lmoimport.png','index.php?option=com_sportsmanagement&view=jlextlmoimports', JText::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT'));
                        echo $this->addIcon('profleagueimport.png','index.php?option=com_sportsmanagement&view=jlextprofleagimport', JText::_('COM_SPORTSMANAGEMENT_EXT_PROF_LEAGUE_IMPORT'));
                        break;
                        case 'basketball':
                        echo $this->addIcon('dbbimport.png','index.php?option=com_sportsmanagement&view=jlextdbbimport', JText::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT'));
                        break;
                        case 'handball':
                        echo $this->addIcon('sisimport.png','index.php?option=com_sportsmanagement&view=jlextsisimport', JText::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT'));
                        break;
                        default:
                        break;
                        
                    }
                    
                    
                    
                }
                
                
                
                
                ?>
        		
                
			</div>
      </div>
      </div>

<div style="text-align:center; clear:both">      
      <br />      
      <br />      
      
              <a title= "<?php echo JText::_('COM_SPORTSMANAGEMENT_SITE_LINK')?>" target= "_blank" href="http://www.fussballineuropa.de">
                <img src= "<?php echo JURI::base( true ) ?>/components/com_sportsmanagement/assets/icons/logo_transparent.png"               width="180" height="auto"</a>            
      <br />
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_DESC" ); ?>
      <br />      
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_COPYRIGHT" ); ?> : &copy; 
      <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
      <br />      
      <?php echo JText::_( "COM_SPORTSMANAGEMENT_VERSION" ); ?> :       
      <?php echo JText::sprintf( '%1$s', $this->version ); ?>     
    </div>
</form>
