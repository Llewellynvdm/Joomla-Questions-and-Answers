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
	@subpackage		default_qna-table.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$backToRef = '<button class="uk-button uk-width-1-1 uk-button-small uk-margin-small-bottom uk-button-primary" onclick="history.go(-1);" value="Back"><i class="uk-icon-arrow-circle-left" ></i> '.JText::_('COM_QUESTIONSANSWERS_BACK').'</button>';

?>
<?php if ($this->user->authorise('question_and_answer.create', 'com_questionsanswers')): ?>
	<a class="uk-button uk-button-success" href="<?php echo JURI::root(); ?>index.php?option=com_questionsanswers&view=questions_and_answers&task=question_and_answer.edit&ref=questions_and_answers">
		<?php echo JText::_('COM_QUESTIONSANSWERS_CREATE_NEW'); ?>
	</a>
<?php endif; ?>
<h1><?php echo $this->Title; ?></h1>
<?php if (isset($this->buttons) && $this->buttons): ?>
	<div class="uk-grid-width-small-1-1 uk-grid-width-medium-1-2"  data-uk-grid="{gutter: 20}">
	<?php foreach ($this->buttons as $item => $name): ?>
		<div>
			<a class="uk-button uk-button-large  uk-width-1-1 uk-button-primary"
				href="<?php echo JRoute::_(QuestionsanswersHelperRoute::getQuestions_and_answersRoute(0, $item)); ?>">
				<br />
				<span class="uk-text-large"><?php echo $name; ?></span>
				<br /><br />
			</a>
		</div>
	<?php endforeach; ?>
	</div>
<?php endif; ?>
<table id="table" class="footable uk-table" data-show-toggle="true" data-toggle-column="first" data-paging="true" data-filtering="true" data-paging-size="150" data-sorting="true"></table>
<?php if ($this->backButton): ?>
	<?php echo $backToRef; ?>
<?php endif; ?>
<script type="text/javascript">
	// token 
	var token = '<?php echo JSession::getFormToken(); ?>';
	// set the key
	var key = '<?php echo $this->qnaBundlesKey; ?>';
	// the get url
	var columnsUrl = "<?php echo JURI::root(); ?>index.php?option=com_questionsanswers&task=ajax.getColumns&format=json&raw=true&page=questions_and_answers&token="+token+"&vdm="+vastDevMod;
	var rowsUrl = "<?php echo JURI::root(); ?>index.php?option=com_questionsanswers&task=ajax.getRows&format=json&raw=true&page=questions_and_answers&token="+token+"&key="+key+"&vdm="+vastDevMod;
	jQuery(function($){
		<?php if (isset($this->buttons) && $this->buttons): ?>
		// set the Category filter
		FooTable.mainFiltering = FooTable.Filtering.extend({
			construct: function(instance){
				this._super(instance);
				this.categories = ['<?php echo implode("','",$this->categoryArray); ?>'];
				this.categoryDef = '<?php echo JText::_('COM_QUESTIONSANSWERS_ALL_CATEGORIES'); ?>';
				this.$category = null;
			},
			$create: function(){
				this._super(); 
				var self = this,
					$form_grp = $('<div/>', {'class': 'form-group'})
						.append($('<label/>', {'class': 'sr-only', text: 'categories'}))
						.prependTo(self.$form);

				self.$category = $('<select/>', { 'class': 'form-control' })
					.on('change', function(){
						self.filter();
					})
					.append($('<option/>', {text: self.categoryDef}))
					.appendTo($form_grp);

				$.each(self.categories, function(i, category){
					self.$category.append($('<option/>').text(category));
				});
			},
			filter: function(query, columns){
				var val = this.$category.val();
				if (val != this.categoryDef) this.addFilter('category_name', val, ['category_name']);
				else this.removeFilter('category_name');
				return this._super(query, columns);
			},
			clear: function(){
				this.$category.val(this.categoryDef);
				this.removeFilter('category_name');
				return this._super();
			}
		});
		FooTable.components.core.register('filtering', FooTable.mainFiltering);
		<?php endif; ?>
		$('.footable').footable({
			"columns": $.get(columnsUrl),
			"rows":  $.get(rowsUrl)
		});
	});
</script>
