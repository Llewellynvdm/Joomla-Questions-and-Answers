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
	@build			2nd March, 2022
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

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Adapter\ComponentAdapter;
JHTML::_('bootstrap.renderModal');

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
	public function __construct(ComponentAdapter $parent) {}

	/**
	 * Called on installation
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(ComponentAdapter $parent) {}

	/**
	 * Called on uninstallation
	 *
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 */
	public function uninstall(ComponentAdapter $parent)
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
				// If successfully remove Question_and_answer add queued success message.
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
				// If successfully remove Question_and_answer add queued success message.
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
				// If successfully removed Question_and_answer add queued success message.
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
		$query->where( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.question_and_answer.category') );
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
			$question_and_answer_catid_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.question_and_answer.category') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__content_types'));
			$query->where($question_and_answer_catid_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer catid items
			$question_and_answer_catid_done = $db->execute();
			if ($question_and_answer_catid_done)
			{
				// If successfully remove Question_and_answer catid add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.question_and_answer.category) type alias was removed from the <b>#__content_type</b> table'));
			}

			// Remove Question_and_answer catid items from the contentitem tag map table
			$question_and_answer_catid_condition = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.question_and_answer.category') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__contentitem_tag_map'));
			$query->where($question_and_answer_catid_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer catid items
			$question_and_answer_catid_done = $db->execute();
			if ($question_and_answer_catid_done)
			{
				// If successfully remove Question_and_answer catid add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.question_and_answer.category) type alias was removed from the <b>#__contentitem_tag_map</b> table'));
			}

			// Remove Question_and_answer catid items from the ucm content table
			$question_and_answer_catid_condition = array( $db->quoteName('core_type_alias') . ' = ' . $db->quote('com_questionsanswers.question_and_answer.category') );
			// Create a new query object.
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__ucm_content'));
			$query->where($question_and_answer_catid_condition);
			$db->setQuery($query);
			// Execute the query to remove Question_and_answer catid items
			$question_and_answer_catid_done = $db->execute();
			if ($question_and_answer_catid_done)
			{
				// If successfully removed Question_and_answer catid add queued success message.
				$app->enqueueMessage(JText::_('The (com_questionsanswers.question_and_answer.category) type alias was removed from the <b>#__ucm_content</b> table'));
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
				// If successfully remove Help_document add queued success message.
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
				// If successfully remove Help_document add queued success message.
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
				// If successfully removed Help_document add queued success message.
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
			// If successfully removed questionsanswers add queued success message.
			$app->enqueueMessage(JText::_('All related items was removed from the <b>#__assets</b> table'));
		}

		// Get the biggest rule column in the assets table at this point.
		$get_rule_length = "SELECT CHAR_LENGTH(`rules`) as rule_size FROM #__assets ORDER BY rule_size DESC LIMIT 1";
		$db->setQuery($get_rule_length);
		if ($db->execute())
		{
			$rule_length = $db->loadResult();
			// Check the size of the rules column
			if ($rule_length < 5120)
			{
				// Revert the assets table rules column back to the default
				$revert_rule = "ALTER TABLE `#__assets` CHANGE `rules` `rules` varchar(5120) NOT NULL COMMENT 'JSON encoded access control.';";
				$db->setQuery($revert_rule);
				$db->execute();
				$app->enqueueMessage(JText::_('Reverted the <b>#__assets</b> table rules column back to its default size of varchar(5120)'));
			}
			else
			{

				$app->enqueueMessage(JText::_('Could not revert the <b>#__assets</b> table rules column back to its default size of varchar(5120), since there is still one or more components that still requires the column to be larger.'));
			}
		}

		// Set db if not set already.
		if (!isset($db))
		{
			$db = JFactory::getDbo();
		}
		// Set app if not set already.
		if (!isset($app))
		{
			$app = JFactory::getApplication();
		}
		// Remove Questionsanswers from the action_logs_extensions table
		$questionsanswers_action_logs_extensions = array( $db->quoteName('extension') . ' = ' . $db->quote('com_questionsanswers') );
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__action_logs_extensions'));
		$query->where($questionsanswers_action_logs_extensions);
		$db->setQuery($query);
		// Execute the query to remove Questionsanswers
		$questionsanswers_removed_done = $db->execute();
		if ($questionsanswers_removed_done)
		{
			// If successfully remove Questionsanswers add queued success message.
			$app->enqueueMessage(JText::_('The com_questionsanswers extension was removed from the <b>#__action_logs_extensions</b> table'));
		}

		// Set db if not set already.
		if (!isset($db))
		{
			$db = JFactory::getDbo();
		}
		// Set app if not set already.
		if (!isset($app))
		{
			$app = JFactory::getApplication();
		}
		// Remove Questionsanswers Question_and_answer from the action_log_config table
		$question_and_answer_action_log_config = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.question_and_answer') );
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__action_log_config'));
		$query->where($question_and_answer_action_log_config);
		$db->setQuery($query);
		// Execute the query to remove com_questionsanswers.question_and_answer
		$question_and_answer_action_log_config_done = $db->execute();
		if ($question_and_answer_action_log_config_done)
		{
			// If successfully removed Questionsanswers Question_and_answer add queued success message.
			$app->enqueueMessage(JText::_('The com_questionsanswers.question_and_answer type alias was removed from the <b>#__action_log_config</b> table'));
		}

		// Set db if not set already.
		if (!isset($db))
		{
			$db = JFactory::getDbo();
		}
		// Set app if not set already.
		if (!isset($app))
		{
			$app = JFactory::getApplication();
		}
		// Remove Questionsanswers Help_document from the action_log_config table
		$help_document_action_log_config = array( $db->quoteName('type_alias') . ' = '. $db->quote('com_questionsanswers.help_document') );
		// Create a new query object.
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__action_log_config'));
		$query->where($help_document_action_log_config);
		$db->setQuery($query);
		// Execute the query to remove com_questionsanswers.help_document
		$help_document_action_log_config_done = $db->execute();
		if ($help_document_action_log_config_done)
		{
			// If successfully removed Questionsanswers Help_document add queued success message.
			$app->enqueueMessage(JText::_('The com_questionsanswers.help_document type alias was removed from the <b>#__action_log_config</b> table'));
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
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(ComponentAdapter $parent){}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, ComponentAdapter $parent)
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
		// load the helper class
		JLoader::register('QuestionsanswersHelper', JPATH_ADMINISTRATOR . '/components/com_questionsanswers/helpers/questionsanswers.php');
		// check the version of QnA
		$manifest = QuestionsanswersHelper::manifest();
		if (isset($manifest->version) && strpos($manifest->version, '.') !== false)
		{
			$version = explode('.', $manifest->version);
			// Get a db connection.
			$db = JFactory::getDbo();
			// target version less then or equal to 1.0.3
			if (count($version) == 3 && $version[0] == 1 && $version[1] == 0 && $version[2] <= 4)
			{
				// we need to make a database correction
				$fix_categories = array(
					'com_questionsanswers.questions_and_answers' => 'com_questionsanswers.question_and_answer'
				);

					// targeted tables (to fix all places categories are mapped into Joomla)
					$fix_tables = array(
						'content_types' => array(
							'id' => 'type_id',
							'key' => 'type_alias',
							'suffix' => '.category'),
						'contentitem_tag_map' => array(
							'id' => 'type_id',
							'key' => 'type_alias',
							'suffix' => '.category'),
						'ucm_content' => array(
							'id' => 'core_content_id',
							'key' => 'core_type_alias',
							'suffix' => '.category'),
						'categories' => array(
							'id' => 'id',
							'key' => 'extension',
							'suffix' => '')
					);
					// the script that does the work
					foreach ($fix_categories as $fix => $category)
					{
						// loop over the targeted tables
						foreach ($fix_tables as $_table => $_update)
						{
							// Create a new query object.
							$query = $db->getQuery(true);
							// get all type_ids
							$query->select($db->quoteName($_update['id']));
							$query->from($db->quoteName('#__' . $_table));
							$query->where( $db->quoteName($_update['key']) . ' = ' . $db->quote($fix . $_update['suffix']));
							// Reset the query using our newly populated query object.
							$db->setQuery($query);
							$db->execute();
							if ($db->getNumRows())
							{

								// all these must be updated
								$ids = $db->loadColumn();
								// Fields to update.
								$fields = array(
									$db->quoteName($_update['key']) . ' = ' . $db->quote($category . $_update['suffix'])
								);
								// Conditions for which records should be updated.
								$conditions = array(
									$db->quoteName($_update['id']) . ' IN (' . implode(', ', $ids) . ')'
								);
								$query->update($db->quoteName('#__' . $_table))->set($fields)->where($conditions);
								$db->setQuery($query);
								$result = $db->execute();
								// on success
								if ($result)
								{
									$app->enqueueMessage("<p>Updated <b>#__$_table - " . $_update['key'] . "</b> from <b>$fix</b>" . $_update['suffix'] . " to <b>$category</b>" . $_update['suffix'] . "!</p>", 'Notice');
								}

							}
						}
					}
			}
		}
		}
		// do any install needed
		if ($type === 'install')
		{
		}
		// check if the PHPExcel stuff is still around
		if (File::exists(JPATH_ADMINISTRATOR . '/components/com_questionsanswers/helpers/PHPExcel.php'))
		{
			// We need to remove this old PHPExcel folder
			$this->removeFolder(JPATH_ADMINISTRATOR . '/components/com_questionsanswers/helpers/PHPExcel');
			// We need to remove this old PHPExcel file
			File::delete(JPATH_ADMINISTRATOR . '/components/com_questionsanswers/helpers/PHPExcel.php');
		}
		return true;
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install|update)
	 * @param   ComponentAdapter  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, ComponentAdapter $parent)
	{
		// get application
		$app = JFactory::getApplication();
		// We check if we have dynamic folders to copy
		$this->setDynamicF0ld3rs($app, $parent);
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
			$question_and_answer_category->type_alias = 'com_questionsanswers.question_and_answer.category';
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
			$help_document->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "title","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "content","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "null","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"title":"title","type":"type","groups":"groups","location":"location","admin_view":"admin_view","site_view":"site_view","alias":"alias","content":"content","article":"article","url":"url","target":"target"}}';
			$help_document->router = 'QuestionsanswersHelperRoute::getHelp_documentRoute';
			$help_document->content_history_options = '{"formFile": "administrator/components/com_questionsanswers/models/forms/help_document.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","type","location","article","target"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "article","targetTable": "#__content","targetColumn": "id","displayColumn": "title"}]}';

			// Set the object into the content types table.
			$help_document_Inserted = $db->insertObject('#__content_types', $help_document);


			// Install the global extension params.
			$query = $db->getQuery(true);
			// Field to update.
			$fields = array(
				$db->quoteName('params') . ' = ' . $db->quote('{"autorName":"Llewellyn van der Merwe","autorEmail":"joomla@vdm.io","crop_main":"0","check_in":"-1 day","save_history":"1","history_limit":"10","uikit_version":"2","uikit_load":"1","uikit_min":"","uikit_style":""}'),
			);
			// Condition.
			$conditions = array(
				$db->quoteName('element') . ' = ' . $db->quote('com_questionsanswers')
			);
			$query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$allDone = $db->execute();

			// Get the biggest rule column in the assets table at this point.
			$get_rule_length = "SELECT CHAR_LENGTH(`rules`) as rule_size FROM #__assets ORDER BY rule_size DESC LIMIT 1";
			$db->setQuery($get_rule_length);
			if ($db->execute())
			{
				$rule_length = $db->loadResult();
				// Check the size of the rules column
				if ($rule_length <= 7520)
				{
					// Fix the assets table rules column size
					$fix_rules_size = "ALTER TABLE `#__assets` CHANGE `rules` `rules` TEXT NOT NULL COMMENT 'JSON encoded access control. Enlarged to TEXT by JCB';";
					$db->setQuery($fix_rules_size);
					$db->execute();
					$app->enqueueMessage(JText::_('The <b>#__assets</b> table rules column was resized to the TEXT datatype for the components possible large permission rules.'));
				}
			}
			echo '<a target="_blank" href="https://www.vdm.io/" title="Questions and Answers">
				<img src="components/com_questionsanswers/assets/images/vdm-component.jpg"/>
				</a>';

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the questionsanswers action logs extensions object.
			$questionsanswers_action_logs_extensions = new stdClass();
			$questionsanswers_action_logs_extensions->extension = 'com_questionsanswers';

			// Set the object into the action logs extensions table.
			$questionsanswers_action_logs_extensions_Inserted = $db->insertObject('#__action_logs_extensions', $questionsanswers_action_logs_extensions);

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the question_and_answer action log config object.
			$question_and_answer_action_log_config = new stdClass();
			$question_and_answer_action_log_config->type_title = 'QUESTION_AND_ANSWER';
			$question_and_answer_action_log_config->type_alias = 'com_questionsanswers.question_and_answer';
			$question_and_answer_action_log_config->id_holder = 'id';
			$question_and_answer_action_log_config->title_holder = 'question';
			$question_and_answer_action_log_config->table_name = '#__questionsanswers_question_and_answer';
			$question_and_answer_action_log_config->text_prefix = 'COM_QUESTIONSANSWERS';

			// Set the object into the action log config table.
			$question_and_answer_Inserted = $db->insertObject('#__action_log_config', $question_and_answer_action_log_config);

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the help_document action log config object.
			$help_document_action_log_config = new stdClass();
			$help_document_action_log_config->type_title = 'HELP_DOCUMENT';
			$help_document_action_log_config->type_alias = 'com_questionsanswers.help_document';
			$help_document_action_log_config->id_holder = 'id';
			$help_document_action_log_config->title_holder = 'title';
			$help_document_action_log_config->table_name = '#__questionsanswers_help_document';
			$help_document_action_log_config->text_prefix = 'COM_QUESTIONSANSWERS';

			// Set the object into the action log config table.
			$help_document_Inserted = $db->insertObject('#__action_log_config', $help_document_action_log_config);
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
			$question_and_answer_category->type_alias = 'com_questionsanswers.question_and_answer.category';
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
			$help_document->field_mappings = '{"common": {"core_content_item_id": "id","core_title": "title","core_state": "published","core_alias": "alias","core_created_time": "created","core_modified_time": "modified","core_body": "content","core_hits": "hits","core_publish_up": "null","core_publish_down": "null","core_access": "null","core_params": "params","core_featured": "null","core_metadata": "null","core_language": "null","core_images": "null","core_urls": "null","core_version": "version","core_ordering": "ordering","core_metakey": "null","core_metadesc": "null","core_catid": "null","core_xreference": "null","asset_id": "asset_id"},"special": {"title":"title","type":"type","groups":"groups","location":"location","admin_view":"admin_view","site_view":"site_view","alias":"alias","content":"content","article":"article","url":"url","target":"target"}}';
			$help_document->router = 'QuestionsanswersHelperRoute::getHelp_documentRoute';
			$help_document->content_history_options = '{"formFile": "administrator/components/com_questionsanswers/models/forms/help_document.xml","hideFields": ["asset_id","checked_out","checked_out_time","version"],"ignoreChanges": ["modified_by","modified","checked_out","checked_out_time","version","hits"],"convertToInt": ["published","ordering","type","location","article","target"],"displayLookup": [{"sourceColumn": "created_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "modified_by","targetTable": "#__users","targetColumn": "id","displayColumn": "name"},{"sourceColumn": "article","targetTable": "#__content","targetColumn": "id","displayColumn": "title"}]}';

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
				<h3>Upgrade to Version 1.0.4 Was Successful! Let us know if anything is not working as expected.</h3>';

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the questionsanswers action logs extensions object.
			$questionsanswers_action_logs_extensions = new stdClass();
			$questionsanswers_action_logs_extensions->extension = 'com_questionsanswers';

			// Check if questionsanswers action log extension is already in action logs extensions DB.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__action_logs_extensions'));
			$query->where($db->quoteName('extension') . ' LIKE '. $db->quote($questionsanswers_action_logs_extensions->extension));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the action logs extensions table if not found.
			if (!$db->getNumRows())
			{
				$questionsanswers_action_logs_extensions_Inserted = $db->insertObject('#__action_logs_extensions', $questionsanswers_action_logs_extensions);
			}

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the question_and_answer action log config object.
			$question_and_answer_action_log_config = new stdClass();
			$question_and_answer_action_log_config->id = null;
			$question_and_answer_action_log_config->type_title = 'QUESTION_AND_ANSWER';
			$question_and_answer_action_log_config->type_alias = 'com_questionsanswers.question_and_answer';
			$question_and_answer_action_log_config->id_holder = 'id';
			$question_and_answer_action_log_config->title_holder = 'question';
			$question_and_answer_action_log_config->table_name = '#__questionsanswers_question_and_answer';
			$question_and_answer_action_log_config->text_prefix = 'COM_QUESTIONSANSWERS';

			// Check if question_and_answer action log config is already in action_log_config DB.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__action_log_config'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($question_and_answer_action_log_config->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$question_and_answer_action_log_config->id = $db->loadResult();
				$question_and_answer_action_log_config_Updated = $db->updateObject('#__action_log_config', $question_and_answer_action_log_config, 'id');
			}
			else
			{
				$question_and_answer_action_log_config_Inserted = $db->insertObject('#__action_log_config', $question_and_answer_action_log_config);
			}

			// Set db if not set already.
			if (!isset($db))
			{
				$db = JFactory::getDbo();
			}
			// Create the help_document action log config object.
			$help_document_action_log_config = new stdClass();
			$help_document_action_log_config->id = null;
			$help_document_action_log_config->type_title = 'HELP_DOCUMENT';
			$help_document_action_log_config->type_alias = 'com_questionsanswers.help_document';
			$help_document_action_log_config->id_holder = 'id';
			$help_document_action_log_config->title_holder = 'title';
			$help_document_action_log_config->table_name = '#__questionsanswers_help_document';
			$help_document_action_log_config->text_prefix = 'COM_QUESTIONSANSWERS';

			// Check if help_document action log config is already in action_log_config DB.
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__action_log_config'));
			$query->where($db->quoteName('type_alias') . ' LIKE '. $db->quote($help_document_action_log_config->type_alias));
			$db->setQuery($query);
			$db->execute();

			// Set the object into the content types table.
			if ($db->getNumRows())
			{
				$help_document_action_log_config->id = $db->loadResult();
				$help_document_action_log_config_Updated = $db->updateObject('#__action_log_config', $help_document_action_log_config, 'id');
			}
			else
			{
				$help_document_action_log_config_Inserted = $db->insertObject('#__action_log_config', $help_document_action_log_config);
			}
		}
		return true;
	}

	/**
	 * Remove folders with files
	 * 
	 * @param   string   $dir     The path to folder to remove
	 * @param   boolean  $ignore  The folders and files to ignore and not remove
	 *
	 * @return  boolean   True in all is removed
	 * 
	 */
	protected function removeFolder($dir, $ignore = false)
	{
		if (Folder::exists($dir))
		{
			$it = new RecursiveDirectoryIterator($dir);
			$it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			// remove ending /
			$dir = rtrim($dir, '/');
			// now loop the files & folders
			foreach ($it as $file)
			{
				if ('.' === $file->getBasename() || '..' ===  $file->getBasename()) continue;
				// set file dir
				$file_dir = $file->getPathname();
				// check if this is a dir or a file
				if ($file->isDir())
				{
					$keeper = false;
					if ($this->checkArray($ignore))
					{
						foreach ($ignore as $keep)
						{
							if (strpos($file_dir, $dir.'/'.$keep) !== false)
							{
								$keeper = true;
							}
						}
					}
					if ($keeper)
					{
						continue;
					}
					Folder::delete($file_dir);
				}
				else
				{
					$keeper = false;
					if ($this->checkArray($ignore))
					{
						foreach ($ignore as $keep)
						{
							if (strpos($file_dir, $dir.'/'.$keep) !== false)
							{
								$keeper = true;
							}
						}
					}
					if ($keeper)
					{
						continue;
					}
					File::delete($file_dir);
				}
			}
			// delete the root folder if not ignore found
			if (!$this->checkArray($ignore))
			{
				return Folder::delete($dir);
			}
			return true;
		}
		return false;
	}

	/**
	 * Check if have an array with a length
	 *
	 * @input	array   The array to check
	 *
	 * @returns bool/int  number of items in array on success
	 */
	protected function checkArray($array, $removeEmptyString = false)
	{
		if (isset($array) && is_array($array) && ($nr = count((array)$array)) > 0)
		{
			// also make sure the empty strings are removed
			if ($removeEmptyString)
			{
				foreach ($array as $key => $string)
				{
					if (empty($string))
					{
						unset($array[$key]);
					}
				}
				return $this->checkArray($array, false);
			}
			return $nr;
		}
		return false;
	}

	/**
	 * Method to set/copy dynamic folders into place (use with caution)
	 *
	 * @return void
	 */
	protected function setDynamicF0ld3rs($app, $parent)
	{
		// get the instalation path
		$installer = $parent->getParent();
		$installPath = $installer->getPath('source');
		// get all the folders
		$folders = Folder::folders($installPath);
		// check if we have folders we may want to copy
		$doNotCopy = array('media','admin','site'); // Joomla already deals with these
		if (count((array) $folders) > 1)
		{
			foreach ($folders as $folder)
			{
				// Only copy if not a standard folders
				if (!in_array($folder, $doNotCopy))
				{
					// set the source path
					$src = $installPath.'/'.$folder;
					// set the destination path
					$dest = JPATH_ROOT.'/'.$folder;
					// now try to copy the folder
					if (!Folder::copy($src, $dest, '', true))
					{
						$app->enqueueMessage('Could not copy '.$folder.' folder into place, please make sure destination is writable!', 'error');
					}
				}
			}
		}
	}
}
