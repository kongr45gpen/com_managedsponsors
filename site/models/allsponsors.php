<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Electroservices Team 
/-------------------------------------------------------------------------------------------------------/

	@version		0.0.3
	@build			4th August, 2018
	@created		3rd August, 2018
	@package		Managed Sponsors
	@subpackage		allsponsors.php
	@author			kongr45gpen <http://helit.org/>	
	@copyright		Copyright (C) 2018. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
  ____  _____  _____  __  __  __      __       ___  _____  __  __  ____  _____  _  _  ____  _  _  ____ 
 (_  _)(  _  )(  _  )(  \/  )(  )    /__\     / __)(  _  )(  \/  )(  _ \(  _  )( \( )( ___)( \( )(_  _)
.-_)(   )(_)(  )(_)(  )    (  )(__  /(__)\   ( (__  )(_)(  )    (  )___/ )(_)(  )  (  )__)  )  (   )(  
\____) (_____)(_____)(_/\/\_)(____)(__)(__)   \___)(_____)(_/\/\_)(__)  (_____)(_)\_)(____)(_)\_) (__) 

/------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * Managedsponsors Model for Allsponsors
 */
class ManagedsponsorsModelAllsponsors extends JModelList
{
	/**
	 * Model user data.
	 *
	 * @var        strings
	 */
	protected $user;
	protected $userId;
	protected $guest;
	protected $groups;
	protected $levels;
	protected $app;
	protected $input;
	protected $uikitComp;

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Get the current user for authorisation checks
		$this->user = JFactory::getUser();
		$this->userId = $this->user->get('id');
		$this->guest = $this->user->get('guest');
		$this->groups = $this->user->get('groups');
		$this->authorisedGroups = $this->user->getAuthorisedGroups();
		$this->levels = $this->user->getAuthorisedViewLevels();
		$this->app = JFactory::getApplication();
		$this->input = $this->app->input;
		$this->initSet = true; 
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Get from #__managedsponsors_sponsor as a
		$query->select($db->quoteName(
			array('a.id','a.asset_id','a.name','a.catid','a.image','a.website','a.published','a.created_by','a.modified_by','a.created','a.modified','a.version','a.hits','a.ordering'),
			array('id','asset_id','name','catid','image','website','published','created_by','modified_by','created','modified','version','hits','ordering')));
		$query->from($db->quoteName('#__managedsponsors_sponsor', 'a'));

		// Get from #__categories as c
		$query->select($db->quoteName(
			array('c.id','c.asset_id','c.parent_id','c.lft','c.rgt','c.level','c.path','c.extension','c.title','c.alias','c.note','c.description','c.published','c.checked_out','c.checked_out_time','c.access','c.params','c.metadesc','c.metakey','c.metadata','c.created_user_id','c.created_time','c.modified_user_id','c.modified_time','c.hits','c.language','c.version'),
			array('categories_id','categories_asset_id','categories_parent_id','categories_lft','categories_rgt','categories_level','categories_path','categories_extension','categories_title','categories_alias','categories_note','categories_description','categories_published','categories_checked_out','categories_checked_out_time','categories_access','categories_params','categories_metadesc','categories_metakey','categories_metadata','categories_created_user_id','categories_created_time','categories_modified_user_id','categories_modified_time','categories_hits','categories_language','categories_version')));
		$query->join('LEFT', ($db->quoteName('#__categories', 'c')) . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('c.id') . ')');
		// Get where a.published is 1
		$query->where('a.published = 1');
		$query->order('c.lft ASC');
		$query->order('a.ordering ASC');

		// return the query object
		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$user = JFactory::getUser();
		// check if this user has permission to access item
		if (!$user->authorise('site.allsponsors.access', 'com_managedsponsors'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_MANAGEDSPONSORS_NOT_AUTHORISED_TO_VIEW_ALLSPONSORS'), 'error');
			// redirect away to the home page if no access allowed.
			$app->redirect(JURI::root());
			return false;
		}  
		// load parent items
		$items = parent::getItems();

		// Get the global params
		$globalParams = JComponentHelper::getParams('com_managedsponsors', true);

		// Insure all item fields are adapted where needed.
		if (ManagedsponsorsHelper::checkArray($items))
		{
			foreach ($items as $nr => &$item)
			{
				// Always create a slug for sef URL's
				$item->slug = (isset($item->alias) && isset($item->id)) ? $item->id.':'.$item->alias : $item->id;
			}
		} 


		// do a quick build of all edit links links
		if (isset($items) && $items)
		{
                        $categories = array();

			foreach ($items as $nr => &$item)
			{
				$canDo = ManagedsponsorsHelper::getActions('look',$item,'looks');
				if ($canDo->get('look.edit'))
				{
					$item->editLink = '<br /><br /><a class="uk-button uk-button-primary uk-width-1-1" href="';
					$item->editLink .= JRoute::_('index.php?option=com_managedsponsors&view=look&task=look.edit&id=' . $item->id);
					$item->editLink .= '"><i class="uk-icon-pencil"></i><span class="uk-hidden-small">';
					$item->editLink .= JText::_('COM_MANAGEDSPONSORS_EDIT_LOOK');
					$item->editLink .= '</span></a>';
				}
				else
				{
					$item->editLink = '';
				}
                                $categories[$item->categories_id][] = $item;
			}
                        $items = $categories;
                        usort($items, function($a, $b) { return $a[0]->categories_lft <=> $b[0]->categories_lft; });
		}

		// return items
		return $items;
	} 
  
}
