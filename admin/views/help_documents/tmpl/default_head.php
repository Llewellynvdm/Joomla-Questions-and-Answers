<?php
/*--------------------------------------------------------------------------------------------------------|  www.vdm.io  |------/
    __      __       _     _____                 _                                  _     __  __      _   _               _
    \ \    / /      | |   |  __ \               | |                                | |   |  \/  |    | | | |             | |
     \ \  / /_ _ ___| |_  | |  | | _____   _____| | ___  _ __  _ __ ___   ___ _ __ | |_  | \  / | ___| |_| |__   ___   __| |
      \ \/ / _` / __| __| | |  | |/ _ \ \ / / _ \ |/ _ \| '_ \| '_ ` _ \ / _ \ '_ \| __| | |\/| |/ _ \ __| '_ \ / _ \ / _` |
       \  / (_| \__ \ |_  | |__| |  __/\ V /  __/ | (_) | |_) | | | | | |  __/ | | | |_  | |  | |  __/ |_| | | | (_) | (_| |
        \/ \__,_|___/\__| |_____/ \___| \_/ \___|_|\___/| .__/|_| |_| |_|\___|_| |_|\__| |_|  |_|\___|\__|_| |_|\___/ \__,_|
                                                        | |
                                                        |_|
/-------------------------------------------------------------------------------------------------------------------------------/

	@version		1.0.x
	@build			8th February, 2021
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		default_head.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<tr>
	<?php if ($this->canEdit&& $this->canState): ?>
		<th width="1%" class="nowrap center hidden-phone">
			<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $this->listDirn, $this->listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
		</th>
		<th width="20" class="nowrap center">
			<?php echo JHtml::_('grid.checkall'); ?>
		</th>
	<?php else: ?>
		<th width="20" class="nowrap center hidden-phone">
			&#9662;
		</th>
		<th width="20" class="nowrap center">
			&#9632;
		</th>
	<?php endif; ?>
	<th class="nowrap" >
			<?php echo JHtml::_('searchtools.sort', 'COM_QUESTIONSANSWERS_HELP_DOCUMENT_TITLE_LABEL', 'a.title', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo JHtml::_('searchtools.sort', 'COM_QUESTIONSANSWERS_HELP_DOCUMENT_TYPE_LABEL', 'a.type', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo JText::_('COM_QUESTIONSANSWERS_HELP_DOCUMENT_GROUPS_LABEL'); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo JHtml::_('searchtools.sort', 'COM_QUESTIONSANSWERS_HELP_DOCUMENT_LOCATION_LABEL', 'a.location', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo JHtml::_('searchtools.sort', 'COM_QUESTIONSANSWERS_HELP_DOCUMENT_ADMIN_VIEW_LABEL', 'g.', $this->listDirn, $this->listOrder); ?>
	</th>
	<th class="nowrap hidden-phone" >
			<?php echo JHtml::_('searchtools.sort', 'COM_QUESTIONSANSWERS_HELP_DOCUMENT_SITE_VIEW_LABEL', 'h.', $this->listDirn, $this->listOrder); ?>
	</th>
	<?php if ($this->canState): ?>
		<th width="10" class="nowrap center" >
			<?php echo JHtml::_('searchtools.sort', 'COM_QUESTIONSANSWERS_HELP_DOCUMENT_STATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
		</th>
	<?php else: ?>
		<th width="10" class="nowrap center" >
			<?php echo JText::_('COM_QUESTIONSANSWERS_HELP_DOCUMENT_STATUS'); ?>
		</th>
	<?php endif; ?>
	<th width="5" class="nowrap center hidden-phone" >
			<?php echo JHtml::_('searchtools.sort', 'COM_QUESTIONSANSWERS_HELP_DOCUMENT_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
	</th>
</tr>