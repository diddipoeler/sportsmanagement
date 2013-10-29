<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<div class="contentpaneopen">
		<div class="contentheading">
			<?php echo JText::_('COM_JOOMLEAGUE_RANKING_NOTES'); ?>
		</div>
	</div>

    <table width="96%" align="center" border="2" cellpadding="0" cellspacing="0">
        <tr>
            <td align="left">
                <br />
                <?php 
                if ( $this->ranking_notes )
                {
                echo $this->ranking_notes;
                }
                else
                {
                echo JText::_('COM_JOOMLEAGUE_NO_RANKING_NOTES');
                }
                ?>
        </td>
            </tr>
    </table>

</div> 