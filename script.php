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
	@build			14th August, 2019
	@created		30th January, 2017
	@package		Questions and Answers
	@subpackage		script.php
	@author			Llewellyn van der Merwe <https://www.vdm.io/>
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html

	Questions &amp; Answers

/-----------------------------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.modal');

/**
 * Script File of Questionsanswers Component
 */
class com_questionsanswersInstallerScript
{
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 */
	public function __construct(JAdapterInstance $parent) {}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $parent) {}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $parent)
	{
		// Get Application object
		$app = JFactory::getApplication();

		// Get The Database object
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Question_and_answer alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.question_and_answer') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$question_and_answer_found = $db->getNumRows();
		// Now check if there were any rows
		if ($question_and_answer_found)
		{
			// Since there are load the needed  question_and_answer type ids
			$question_and_answer_ids = $db->loadColumn();
			// Remove Question_and_answer from the content type table
			$question_and_answer_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.question_and_answer') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($question_and_answer_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer items
			$question_and_answer_done = $db->execute();
			if ($question_and_answer_done)
			{
				// If succesfully remove Question_and_answer add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.question_and_answer) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Question_and_answer items from the contentitem tag map table
			$question_and_answer_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.question_and_answer') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($question_and_answer_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer items
			$question_and_answer_done = $db->execute();
			if ($question_and_answer_done)
			{
				// If succesfully remove Question_and_answer add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.question_and_answer) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Question_and_answer items from the ucm content table
			$question_and_answer_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_questionsanswers.question_and_answer') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($question_and_answer_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer items
			$question_and_answer_done = $db->execute();
			if ($question_and_answer_done)
			{
				// If succesfully remove Question_and_answer add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.question_and_answer) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Question_and_answer items are cleared from DB
			foreach ($question_and_answer_ids as $question_and_answer_id)
			{
				// Remove Question_and_answer items from the ucm base table
				$question_and_answer_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $question_and_answer_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($question_and_answer_condition);
				$db->setQuery($query);
				// Execute the query to remove Question_and_answer items
				$db->execute();

				// Remove Question_and_answer items from the ucm history table
				$question_and_answer_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $question_and_answer_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($question_and_answer_condition);
				$db->setQuery($query);
				// Execute the query to remove Question_and_answer items
				$db->execute();
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Question_and_answer catid alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.questions_and_answers.category') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$question_and_answer_catid_found = $db->getNumRows();
		// Now check if there were any rows
		if ($question_and_answer_catid_found)
		{
			// Since there are load the needed  question_and_answer_catid type ids
			$question_and_answer_catid_ids = $db->loadColumn();
			// Remove Question_and_answer catid from the content type table
			$question_and_answer_catid_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.questions_and_answers.category') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($question_and_answer_catid_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer catid items
			$question_and_answer_catid_done = $db->execute();
			if ($question_and_answer_catid_done)
			{
				// If succesfully remove Question_and_answer catid add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.questions_and_answers.category) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Question_and_answer catid items from the contentitem tag map table
			$question_and_answer_catid_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.questions_and_answers.category') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($question_and_answer_catid_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer catid items
			$question_and_answer_catid_done = $db->execute();
			if ($question_and_answer_catid_done)
			{
				// If succesfully remove Question_and_answer catid add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.questions_and_answers.category) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Question_and_answer catid items from the ucm content table
			$question_and_answer_catid_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_questionsanswers.questions_and_answers.category') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($question_and_answer_catid_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer catid items
			$question_and_answer_catid_done = $db->execute();
			if ($question_and_answer_catid_done)
			{
				// If succesfully remove Question_and_answer catid add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.questions_and_answers.category) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Question_and_answer catid items are cleared from DB
			foreach ($question_and_answer_catid_ids as $question_and_answer_catid_id)
			{
				// Remove Question_and_answer catid items from the ucm base table
				$question_and_answer_catid_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $question_and_answer_catid_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($question_and_answer_catid_condition);
				$db->setQuery($query);
				// Execute the query to remove Question_and_answer catid items
				$db->execute();

				// Remove Question_and_answer catid items from the ucm history table
				$question_and_answer_catid_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $question_and_answer_catid_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($question_and_answer_catid_condition);
				$db->setQuery($query);
				// Execute the query to remove Question_and_answer catid items
				$db->execute();
			}
		}

		// Create a new query object.
		$query = $db->getQuery(true);
		// Select id from content type table
		$query->select($db->quoteName('type_id'));
		$query->from($db->quoteName('#__content_types'));
		// Where Help_document alias is found
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.help_document') );
		$db->setQuery($query);
		// Execute query to see if alias is found
		$db->execute();
		$help_document_found = $db->getNumRows();
		// Now check if there were any rows
		if ($help_document_found)
		{
			// Since there are load the needed  help_document type ids
			$help_document_ids = $db->loadColumn();
			// Remove Help_document from the content type table
			$help_document_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.help_document') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($help_document_condition);
			$db->setQuery($query);
			// Execute the query to remove Help_document items
			$help_document_done = $db->execute();
			if ($help_document_done)
			{
				// If succesfully remove Help_document add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.help_document) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Help_document items from the contentitem tag map table
			$help_document_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.help_document') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($help_document_condition);
			$db->setQuery($query);
			// Execute the query to remove Help_document items
			$help_document_done = $db->execute();
			if ($help_document_done)
			{
				// If succesfully remove Help_document add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.help_document) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Help_document items from the ucm content table
			$help_document_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_questionsanswers.help_document') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($help_document_condition);
			$db->setQuery($query);
			// Execute the query to remove Help_document items
			$help_document_done = $db->execute();
			if ($help_document_done)
			{
				// If succesfully remove Help_document add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.help_document) type alias was removed from the <b>#__ucm_content</b> table'));
			}

			// Make sure that all the Help_document items are cleared from DB
			foreach ($help_document_ids as $help_document_id)
			{
				// Remove Help_document items from the ucm base table
				$help_document_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $help_document_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_base'));
				$query->where($help_document_condition);
				$db->setQuery($query);
				// Execute the query to remove Help_document items
				$db->execute();

				// Remove Help_document items from the ucm history table
				$help_document_condition = array( $db->quoteName('ucm_type_id') . ' = ' . $help_document_id);
				// Create a new query object.
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__ucm_history'));
				$query->where($help_document_condition);
				$db->setQuery($query);
				// Execute the query to remove Help_document items
				$db->execute();
			}
		}

		// If All related items was removed queued success message.
		$app->enqueueMessage(JText::_('All related items was removed from the <b>#__ucm_base</b> table'));
		$app->enqueueMessage(JText::_('All related items was removed from the <b>#__ucm_history</b> table'));

		// Remove questionsanswers assets from the assets table
		$questionsanswers_condition = array( $db->quoteName('name') . ' LIKE ' . $db->quote('com_questionsanswers%') );

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__assets'));
		$query->where($questionsanswers_condition);
		$db->setQuery($query);
		$help_document_done = $db->execute();
		if ($help_document_done)
		{
			// If succesfully remove questionsanswers add queued success message.
			$app->enqueueMessage(JText::_('All related items was removed from the <b>#__assets</b> table'));
		}

		// little notice as after service, in case of bad experience with component.
		echo '<h2>Did something go wrong? Are you disappointed?</h2>
		<p>Please let me know at <a href="mailto:joomla@vdm.io">joomla@vdm.io</a>.
		<br />We at Vast Development Method are committed to building extensions that performs proficiently! You can help us, really!
		<br />Send me your thoughts on improvements that is needed, trust me, I will be very grateful!
		<br />Visit us at <a href="https://www.vdm.io/" target="_blank">https://www.vdm.io/</a> today!</p>';
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $parent){}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, JAdapterInstance $parent)
	{
		// get application
		$app = JFactory::getApplication();
		// is redundant or so it seems ...hmmm let me know if it works again
		if ($type === 'uninstall')
		{
			return true;
		}
		// the default for both install and update
		$jversion = new JVersion();
		if (!$jversion->isCompatible('3.8.0'))
		{
			$app->enqueueMessage('Please upgrade to at least Joomla! 3.8.0 before continuing!', 'error');
			return false;
		}
		// do any updates needed
		if ($type === 'update')
		{
		}
		// do any install needed
		if ($type === 'install')
		{
		}
		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, JAdapterInstance $parent)
	{
		// get application
		$app = JFactory::getApplication();
		// set the default component settings
		if ($type === 'install')
		{

			// Get The Database object
			$db = JFactory::getDbo();

			// Create the question_and_answer content type object.
			$question_and_answer = new stdClass();
			$question_and_answer->type_title = 'Questionsanswers Question_and_answer';
			$question_and_answer->type_alias = 'com_questionsanswers.question_and_answer';
			$question_and_answer->table = '{"special": {"dbtable": "#__questionsanswers_question_and_answer","key": "id","type": "Question_and_answer","prefix": "questionsanswersTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$question_and_answer->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "null","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "answer","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "metadata","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "metakey","core_metadesc": "metadesc","core_catid": "catid","core_xreference": "null","asset_id": "asset_id"},"special": {"question":"question","answer":"answer","answer_documents":"answer_documents","main_image":"main_image"}}';
			$question_and_answer->router = 'QuestionsanswersHelperRoute::getQuestion_and_answerRoute';
			$question_and_answer->content_history_options = '{"formFile": "administrator/components/com_questionsanswers/models/forms/question_and_answer.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","answer_documents","main_image"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","catid"],"displayLookup": [{"sourceColumn": "catid","targetTable": "#__categories","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"}]}';

			// Set the object into the content types table.
			$question_and_answer_Inserted = $db->insertObject('#__content_types', $question_and_answer);

			// Create the question_and_answer category content type object.
			$question_and_answer_category = new stdClass();
			$question_and_answer_category->type_title = 'Questionsanswers Question_and_answer Catid';
			$question_and_answer_category->type_alias = 'com_questionsanswers.questions_and_answers.category';
			$question_and_answer_category->table = '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}';
			$question_and_answer_category->field_mappings = '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}';
			$question_and_answer_category->router = 'QuestionsanswersHelperRoute::getCategoryRoute';
			$question_and_answer_category->content_history_options = '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}';

			// Set the object into the content types table.
			$question_and_answer_category_Inserted = $db->insertObject('#__content_types', $question_and_answer_category);

			// Create the help_document content type object.
			$help_document = new stdClass();
			$help_document->type_title = 'Questionsanswers Help_document';
			$help_document->type_alias = 'com_questionsanswers.help_document';
			$help_document->table = '{"special": {"dbtable": "#__questionsanswers_help_document","key": "id","type": "Help_document","prefix": "questionsanswersTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$help_document->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "title","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "content","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "null","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"title":"title","type":"type","groups":"groups","location":"location","admin_view":"admin_view","site_view":"site_view","not_required":"not_required","content":"content","article":"article","url":"url","target":"target","alias":"alias"}}';
			$help_document->router = 'QuestionsanswersHelperRoute::getHelp_documentRoute';
			$help_document->content_history_options = '{"formFile": "administrator/components/com_questionsanswers/models/forms/help_document.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","not_required"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","type","location","not_required","article","target"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "article","targetTable": "#__content","targetColumn": "id","displayColumn": "title"}]}';

			// Set the object into the content types table.
			$help_document_Inserted = $db->insertObject('#__content_types', $help_document);


			// Install the global extenstion params.
			$query = $db->getQuery(true);
			// Field to update.
			$fields = array(
				$db->quoteName('params') . ' = ' . $db->quote('{"autorName":"Llewellyn van der Merwe","autorEmail":"joomla@vdm.io","crop_main":"0","check_in":"-1 day","save_history":"1","history_limit":"10","uikit_load":"1","uikit_min":"","uikit_style":""}'),
			);
			// Condition.
			$conditions = array(
				$db->quoteName('element') . ' = ' . $db->quote('com_questionsanswers')
			);
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$allDone = $db->execute();

			echo '<a target="_blank" href="https://www.vdm.io/" title="Questions and Answers">
				<img src="components/com_questionsanswers/assets/images/vdm-component.jpg"/>
				</a>';
		}
		// do any updates needed
		if ($type === 'update')
		{

			// Get The Database object
			$db = JFactory::getDbo();

			// Create the question_and_answer content type object.
			$question_and_answer = new stdClass();
			$question_and_answer->type_title = 'Questionsanswers Question_and_answer';
			$question_and_answer->type_alias = 'com_questionsanswers.question_and_answer';
			$question_and_answer->table = '{"special": {"dbtable": "#__questionsanswers_question_and_answer","key": "id","type": "Question_and_answer","prefix": "questionsanswersTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$question_and_answer->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "null","core_state": "published","core_alias": "null","core_created_time": "created","core_modified_time": "modified","core_body": "answer","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "access","core_params": "params","core_featured": "null","core_metadata": "metadata","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "metakey","core_metadesc": "metadesc","core_catid": "catid","core_xreference": "null","asset_id": "asset_id"},"special": {"question":"question","answer":"answer","answer_documents":"answer_documents","main_image":"main_image"}}';
			$question_and_answer->router = 'QuestionsanswersHelperRoute::getQuestion_and_answerRoute';
			$question_and_answer->content_history_options = '{"formFile": "administrator/components/com_questionsanswers/models/forms/question_and_answer.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","answer_documents","main_image"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","catid"],"displayLookup": [{"sourceColumn": "catid","targetTable": "#__categories","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "access","targetTable": "#__viewlevels","targetColumn": "id","displayColumn": "title"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"}]}';

			// Check if question_and_answer type is already in content_type DB.
			$question_and_answer_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($question_and_answer->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$question_and_answer->type_id = $db->loadResult();
				$question_and_answer_Updated = $db->updateObject('#__content_types', $question_and_answer, 'type_id');
			}
			else
			{
				$question_and_answer_Inserted = $db->insertObject('#__content_types', $question_and_answer);
			}

			// Create the question_and_answer category content type object.
			$question_and_answer_category = new stdClass();
			$question_and_answer_category->type_title = 'Questionsanswers Question_and_answer Catid';
			$question_and_answer_category->type_alias = 'com_questionsanswers.questions_and_answers.category';
			$question_and_answer_category->table = '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}';
			$question_and_answer_category->field_mappings = '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}';
			$question_and_answer_category->router = 'QuestionsanswersHelperRoute::getCategoryRoute';
			$question_and_answer_category->content_history_options = '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}';

			// Check if question_and_answer category type is already in content_type DB.
			$question_and_answer_category_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($question_and_answer_category->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$question_and_answer_category->type_id = $db->loadResult();
				$question_and_answer_category_Updated = $db->updateObject('#__content_types', $question_and_answer_category, 'type_id');
			}
			else
			{
				$question_and_answer_category_Inserted = $db->insertObject('#__content_types', $question_and_answer_category);
			}

			// Create the help_document content type object.
			$help_document = new stdClass();
			$help_document->type_title = 'Questionsanswers Help_document';
			$help_document->type_alias = 'com_questionsanswers.help_document';
			$help_document->table = '{"special": {"dbtable": "#__questionsanswers_help_document","key": "id","type": "Help_document","prefix": "questionsanswersTable","config": "array()"},"common": {"dbtable": "#__ucm_content","key": "ucm_id","type": "Corecontent","prefix": "JTable","config": "array()"}}';
			$help_document->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "title","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "content","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "null","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"title":"title","type":"type","groups":"groups","location":"location","admin_view":"admin_view","site_view":"site_view","not_required":"not_required","content":"content","article":"article","url":"url","target":"target","alias":"alias"}}';
			$help_document->router = 'QuestionsanswersHelperRoute::getHelp_documentRoute';
			$help_document->content_history_options = '{"formFile": "administrator/components/com_questionsanswers/models/forms/help_document.xml","hideFields": ["asset_id","checked_out","checked_out_time","version","not_required"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","type","location","not_required","article","target"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "article","targetTable": "#__content","targetColumn": "id","displayColumn": "title"}]}';

			// Check if help_document type is already in content_type DB.
			$help_document_id = null;
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('type_id')));
			$query->from($db->quoteName('#__content_types'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($help_document->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$help_document->type_id = $db->loadResult();
				$help_document_Updated = $db->updateObject('#__content_types', $help_document, 'type_id');
			}
			else
			{
				$help_document_Inserted = $db->insertObject('#__content_types', $help_document);
			}


			echo '<a target="_blank" href="https://www.vdm.io/" title="Questions and Answers">
				<img src="components/com_questionsanswers/assets/images/vdm-component.jpg"/>
				</a>
				<h3>Upgrade to Version 1.0.2 Was Successful! Let us know if anything is not working as expected.</h3>';
		}
		return true;
	}
}
