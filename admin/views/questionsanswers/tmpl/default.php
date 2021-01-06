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
	@build			6th January, 2021
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		default.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');

?>
<div id="j-main-container">
	<div class="form-horizontal">
	<?php echo JHtml::_('bootstrap.startTabSet', 'cpanel_tab', array('active' => 'cpanel')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'cpanel_tab', 'cpanel', JText::_('cPanel', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php echo JHtml::_('bootstrap.startAccordion', 'dashboard_left', array('active' => 'main')); ?>
					<?php echo JHtml::_('bootstrap.addSlide', 'dashboard_left', 'Control Panel', 'main'); ?>
						<?php echo $this->loadTemplate('main');?>
					<?php echo JHtml::_('bootstrap.endSlide'); ?>
				<?php echo JHtml::_('bootstrap.endAccordion'); ?>
			</div>
			<div class="span3">
				<?php echo JHtml::_('bootstrap.startAccordion', 'dashboard_right', array('active' => 'vdm')); ?>
					<?php echo JHtml::_('bootstrap.addSlide', 'dashboard_right', 'Vast Development Method', 'vdm'); ?>
						<?php echo $this->loadTemplate('vdm');?>
					<?php echo JHtml::_('bootstrap.endSlide'); ?>
				<?php echo JHtml::_('bootstrap.endAccordion'); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'cpanel_tab', 'vast_development_method', JText::_('Vast Development Method', true)); ?>
		<div class="row-fluid">
			<div class="span12">
				<?php  echo JHtml::_('bootstrap.startAccordion', 'vast_development_method_accordian', array('active' => 'vast_development_method_one')); ?>
					<?php  echo JHtml::_('bootstrap.addSlide', 'vast_development_method_accordian', 'Notice Board', 'vast_development_method_one'); ?>
						<?php echo $this->loadTemplate('vast_development_method_notice_board');?>
					<?php  echo JHtml::_('bootstrap.endSlide'); ?>
				<?php  echo JHtml::_('bootstrap.endAccordion'); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'cpanel_tab', 'readme', JText::_('Readme', true)); ?>
		<div class="row-fluid">
			<div class="span12">
				<?php  echo JHtml::_('bootstrap.startAccordion', 'readme_accordian', array('active' => 'readme_one')); ?>
					<?php  echo JHtml::_('bootstrap.addSlide', 'readme_accordian', 'Information', 'readme_one'); ?>
						<?php echo $this->loadTemplate('readme_information');?>
					<?php  echo JHtml::_('bootstrap.endSlide'); ?>
				<?php  echo JHtml::_('bootstrap.endAccordion'); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
</div>