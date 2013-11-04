<?PHP

foreach ($this->importData as $key => $value)
		{
			?>
			<fieldset>
				<legend><?php echo JText::_($key); ?></legend>
				<table class='adminlist'><tr><td><?php echo $value; ?></td></tr></table>
			</fieldset>
			<?php
		}




?>