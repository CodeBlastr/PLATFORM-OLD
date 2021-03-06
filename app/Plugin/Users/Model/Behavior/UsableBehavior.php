<?php
App::uses('ModelBehavior', 'Model');
App::uses('CakeSession', 'Model/Datasource');
class UsableBehavior extends ModelBehavior {

	public $model = '';
	public $foreignKey = '';
	public $defaultRole = '';
	public $userData = array();
	public $superAdminRoleId = 1;
	public $restrictRedirect = false;

/**
 * @param Model $Model
 * @param array $settings
 * @return bool|void
 */
	public function setup(Model $Model, $settings = array()) {
		$this->defaultRole = !empty($settings['defaultRole']) ? $settings['defaultRole'] : null;
		$this->superAdminRoleId = defined('__USERS_SUPER_ADMIN_ROLE_ID') ? __USERS_SUPER_ADMIN_ROLE_ID : $this->superAdminRoleId;
        return true;
	}

/**
 * @param Model $Model
 * @param array $options
 * @return bool|mixed
 */
	public function beforeSave(Model $Model, $options = array()) {
		// remove habtm user data and give it to the afterSave() function
		if (!empty($Model->data['User']['User'])) {
			$this->userData = $Model->data;
			unset($Model->data['User']['User']);
        }
		// this was removed during a conflict resolution, was not sure if it should be here or be removed
		// if(!empty($Model->data['Used'])){
			// $this->userData = $Model->data;
			// unset($Model->data['Used']);
		// }
		$Model->data = $this->getChildContacts($Model);
		return true;
	}


/**
 * Update the find methods so that we check against the used table
 * that the current user is part of this item being searched.
 *
 * @todo I'm semi-sure that the big query this makes could be optimized better.
 * An OR and a NOT IN in one query isn't exactly high performance.
 * But after 9 hours coming up with that we'll leave optimization for another day.
 *
 * @param Model $Model Model class triggering this callback
 * @param array $queryData An array specifying the conditions for the query to be triggered.
 * @return array|bool
 */
	public function beforeFind(Model $Model, $queryData) {
		$authUser = CakeSession::read('Auth.User');
		//$userRole = $authUser['user_role_id']; (this uncommented breaks our tests)
		$userId = $authUser['id'];

		$UsersGroup = ClassRegistry::init('Users.UsersUserGroup');
		$groupIds = isset($userId) ? Set::extract('/UsersUserGroup/user_group_id', $UsersGroup->find('all', array('conditions' => array('UsersUserGroup.user_id' => $userId)))) : null;


		if (!empty($userId) /*&& $userRole != $this->superAdminRoleId*/ && empty($queryData['nocheck'])) {
			// this tells us whether the result would have returned something if UsableBehavior wasn't used
			$queryData['nocheck'] = true;
			// $originalSearchCount = $Model->find('count', $queryData);
			$originalSearchCount = 0;
			if ($originalSearchCount > 0) : $this->restrictRedirect = true; endif;
			
			/*# this allows you to bypass the logged in user check (nocheck should equal the user id)
			$userQuery = !empty($queryData['nocheck']) ? "Used.user_id = '{$queryData['nocheck']}'" : "Used.user_id = '{$userId}'";
			*/ // left because I don't know where nocheck was used
			
			
			/* output the new query // left for reference as its a pretty cool query
			$queryData['joins'] = array(array(
				'table' => 'used',
				'alias' => 'Used',
				'type' => 'LEFT',
				'conditions' => array(
					"Used.foreign_key = {$Model->alias}.id",
					"Used.model = '{$Model->alias}'",
					$userQuery,
				),
			));*/ 
			
			$Dbo = $Model->getDataSource();
			
			// First find users with access
			$subQuery = $Dbo->buildStatement(array(
				//'fields' => array('`User2`.`id`'),
				'fields' => array('Used.foreign_key'),
				'table' => 'used',
				'alias' => 'Used',
				'limit' => null,
				'offset' => null,
				'joins' => array(),
				'conditions' => array(
					'Used.model' => "{$Model->alias}",
					'Used.user_id' => $userId,
					),
				'order' => null,
				'group' => null
				), $Model);
			$subQuery = "`{$Model->alias}`.`id` IN (" . $subQuery . ")";
			$orStatment[] = '('.$Dbo->expression($subQuery)->value.')' ;
			
			// First model records that aren't accessed controlled
			$subQuery2 = $Dbo->buildStatement(array(
				//'fields' => array('`User2`.`id`'),
				'fields' => array('Used.foreign_key'),
				'table' => 'used',
				'alias' => 'Used',
				'limit' => null,
				'offset' => null,
				'joins' => array(),
				'conditions' => array(
					'Used.model' => "{$Model->alias}",
					'Used.foreign_key = ' . $Model->alias . '.id',
					),
				'order' => null,
				'group' => null
				), $Model);
			$subQuery2 = "`{$Model->alias}`.`id` NOT IN (" . $subQuery2 . ")";
			$orStatment[] = '(' . $Dbo->expression($subQuery2)->value . ')';
			
			// I wish there was a fucking comment about why this is here!!!!!
			// if(!empty($groupIds)){
				// $subQuery3 = $Dbo->buildStatement(array(
					// //'fields' => array('`User2`.`id`'),
					// 'fields' => array('Used.foreign_key'),
					// 'table' => 'used',
					// 'alias' => 'Used',
					// 'limit' => null,
					// 'offset' => null,
					// 'joins' => array(),
					// 'conditions' => array(
						// 'Used.model' => "{$Model->alias}",
						// 'Used.user_group_id IN ('. implode(',',$groupIds) .')',
					// ),
					// 'order' => null,
					// 'group' => null
				// ), $Model);
// 
				// $subQuery3 =  "`{$Model->alias}`.`id` IN (" . $subQuery3 . ")";
				// $orStatment[] = '(' . $Dbo->expression($subQuery3)->value . ')';
// 
			// }

			//$newQueryData2['conditions'][]['OR'] = array('('.$subQueryExpression->value.')', '('.$subQueryExpression2->value.')','('.$subQueryExpression3->value.')');
			$newQueryData['conditions'][]['OR'] = $orStatment;
			$queryData = Set::merge($queryData, $newQueryData);


			/* Example of the query we're running here.
			SELECT `Project`.`id`
			FROM `projects` AS `Project` 
			WHERE `Project`.`is_archived` = '0' 
			AND (
				 ((
				 	`Project`.`id` IN (
						SELECT `Used`.`foreign_key` 
						FROM used AS Used 
						WHERE `Used`.`model` = 'Project' 
						AND `Used`.`user_id` = 1 )
				 )) OR ((
					`Project`.`id` NOT IN (
						SELECT `Used`.`foreign_key` 
						FROM used AS Used 
						WHERE `Used`.`model` = 'Project' )
				 ))
				) 
			LIMIT 25
			*/	
		}
		return $queryData;
	}
	public function beforeFindUserGroupIds(Model $Model, $queryData) {
		$authUser = CakeSession::read('Auth.User');
		//$userRole = $authUser['user_role_id']; (this uncommented breaks our tests)
		$userId = $authUser['id'];
		$User = ClassRegistry::init('Users.User');
		debug($User->find('first', array('conditions' => array('User.id' => $userId), 'contain' => array('UserGroup'))));
		if (!empty($userId) /*&& $userRole != $this->superAdminRoleId*/ && empty($queryData['nocheck'])) {
			// this tells us whether the result would have returned something if UsableBehavior wasn't used
			$queryData['nocheck'] = true;
			// $originalSearchCount = $Model->find('count', $queryData);
			$originalSearchCount = 0;
			if ($originalSearchCount > 0) : $this->restrictRedirect = true; endif;

			/*# this allows you to bypass the logged in user check (nocheck should equal the user id)
			$userQuery = !empty($queryData['nocheck']) ? "Used.user_id = '{$queryData['nocheck']}'" : "Used.user_id = '{$userId}'";
			*/ // left because I don't know where nocheck was used


			/* output the new query // left for reference as its a pretty cool query
			$queryData['joins'] = array(array(
				'table' => 'used',
				'alias' => 'Used',
				'type' => 'LEFT',
				'conditions' => array(
					"Used.foreign_key = {$Model->alias}.id",
					"Used.model = '{$Model->alias}'",
					$userQuery,
				),
			));*/

			$Dbo = $Model->getDataSource();

			// First find users with access
			$subQuery = $Dbo->buildStatement(array(
				//'fields' => array('`User2`.`id`'),
				'fields' => array('Used.foreign_key'),
				'table' => 'used',
				'alias' => 'Used',
				'limit' => null,
				'offset' => null,
				'joins' => array(),
				'conditions' => array(
					'Used.model' => "{$Model->alias}",
					'Used.user_group_id' => $userId,
				),
				'order' => null,
				'group' => null
			), $Model);
			$subQuery = "`{$Model->alias}`.`id` IN (" . $subQuery . ")";
			$subQueryExpression = $Dbo->expression($subQuery);
			// First model records that aren't accessed controlled
			$subQuery2 = $Dbo->buildStatement(array(
				//'fields' => array('`User2`.`id`'),
				'fields' => array('Used.foreign_key'),
				'table' => 'used',
				'alias' => 'Used',
				'limit' => null,
				'offset' => null,
				'joins' => array(),
				'conditions' => array(
					'Used.model' => "{$Model->alias}",
					'Used.foreign_key = ' . $Model->alias . '.id',
				),
				'order' => null,
				'group' => null
			), $Model);
			$subQuery2 = "`{$Model->alias}`.`id` NOT IN (" . $subQuery2 . ")";
			$subQueryExpression2 = $Dbo->expression($subQuery2);


			$newQueryData['conditions'][]['OR'] = array('('.$subQueryExpression->value.')', '('.$subQueryExpression2->value.')');
			$queryData = Set::merge($queryData, $newQueryData);

			/* Example of the query we're running here.
			SELECT `Project`.`id`
			FROM `projects` AS `Project`
			WHERE `Project`.`is_archived` = '0'
			AND (
				 ((
				 	`Project`.`id` IN (
						SELECT `Used`.`foreign_key`
						FROM used AS Used
						WHERE `Used`.`model` = 'Project'
						AND `Used`.`user_id` = 1 )
				 )) OR ((
					`Project`.`id` NOT IN (
						SELECT `Used`.`foreign_key`
						FROM used AS Used
						WHERE `Used`.`model` = 'Project' )
				 ))
				)
			LIMIT 25
			*/
		}
		return $queryData;
	}
	

/**
 * Redirects to restricted if beforeFind emptied the results that would have otherwise not been empty
 * Adds pseudo field to denote whether the records is_used or not
 *
 * @param Model $Model Model class triggering this callback
 * @param mixed $results
 * @param bool $primary The data returned from the find query
 * @return mixed
 */
	public function afterFind(Model $Model, $results, $primary = false) {
		if(empty($results) && str_replace('/', '', $_SERVER['REQUEST_URI']) != 'usersusersrestricted' && $this->restrictRedirect) { 
			header("Location: /users/users/restricted");
			exit;
		}
		if (!empty($results)) {
			$Model->bindModel(
    	    	array('hasMany' => array(
        	       	'Used' => array(
            	       	'className' => 'Users.Used',
						'foreignKey' => 'foreign_key',
		        	    )
	            	)));
			$i=0;
			foreach ($results as $result) {
				if (!empty($result[$Model->alias][$Model->primaryKey])) {
					$count = $Model->Used->find('count', array(
						'conditions' => array(
							'Used.foreign_key' => $result[$Model->alias][$Model->primaryKey],
							'Used.model' => $Model->alias,
							),
						));
					$results[$i][$Model->alias]['__used'] = $count;
				}
				$i = $i + 1;
			}
		}
		return $results;
	}
	
	private function _saveManyUsers($data){
		foreach ($data as $user) {
			//$users[]['id'] = $user['user_id']; // before cakephp 2.0 upgrade
			$users[]['id'] = !empty($user['user_id']) ? $user['user_id'] : $user['id'];
		}

		return $users;
	}

	private function _saveHasAndBelongsToManyUsers($data){
		foreach ($data as $userId) {
			$users[]['id'] = $userId;
		}
		return $users;
	}

	private function _saveUserGroupIds($data){

		foreach ($data as $groupId ) {
			$users[]['user_group_id'] = $groupId;
		}
		return $users;
	}

/**
 * @param Model $Model
 * @return array
 */
	private function _saveUsersInUserGroup($Model){
		$userGroups = $Model->UserGroup->find('all', array(
			'conditions' => array(
				'UserGroup.id' => $Model->data[$Model->alias]['user_group_id'],
			),
			'contain' => array(
				'User' => array(
					'fields' => 'User.id',
				),
			),
		));
		foreach ($userGroups as $userGroup) {
			if(!empty($userGroup['User'])) {
				$users = !empty($users) ? array_merge($userGroup['User'], $users) : $userGroup['User'];
			}
		}
		return $users;
	}
	

/**
 * Callback used to save related users,
 * into the used table, with the proper relationship.
 * @param Model $Model
 * @param bool $created
 * @param array $options
 * @return bool|void
 */
	public function afterSave(Model $Model, $created, $options = array()) {
		// get current users using, so that we can merge and keep duplicates out later
		$currentUsers = $this->findUsedUsers($Model, $Model->data[$Model->alias]['id'], 'all');
		$path = '/id';
		$users = array();//init $users variable

		// this is if we have a hasMany list of users coming in.
		if (!empty($Model->data['User'][0])) {
			$users = $this->_saveManyUsers($Model->data['User']);

		}
		
		// this is if we have a habtm list of users coming in.
		if (!empty($this->userData['User']['User'][0])) {
			$users = $this->_saveHasAndBelongsToManyUsers($this->userData['User']['User']);

		}
		/**
		 * this one different from $Model->alias['user_group_id']
		 * it does not add every user in the user group.
		 *
		 */

		if(!empty($Model->data['Used']['user_group_id'])){
			$users = $this->_saveUserGroupIds($Model->data['Used']['user_group_id']);
			$path = '/user_group_id';
		} elseif (!empty($Model->data[$Model->alias]['user_group_id'])) {
			// this is if its a user group we need to look up.
			// add all of the team members to the used table
			$users = $this->_saveUsersInUserGroup($Model);
		}
		// gets rid of duplicate users from two arrays... @todo: maybe move this to its own function if its needed again
		if (!empty($users)) {
			$users = Set::extract($path, $users);
			$currentUsers = Set::extract('/User/id', $currentUsers);
			$users = array_diff($users, $currentUsers);
			if(count($users) > 0){
				$Used = ClassRegistry::init('Users.Used');
				foreach ($users as $user) {
					$data['Used'][$path == '/id' ? 'user_id' : 'user_group_id'] = $user;
					$data['Used']['foreign_key'] = $Model->id;
					$data['Used']['model'] = $Model->alias;
					$data['Used']['role'] = $this->defaultRole;
					$Used->create();
					$Used->save($data);

				}
			}
		}
	}
	

/**
 * finds used objects based on the userId specified and model asking for this function.
 * uses standard find() parameters after userId
 * @param Model $Model
 * @param null $userId
 * @param string $type
 * @param array $params
 * @return array
 */
	public function findUsedObjects(&$Model, $userId = null, $type = 'list', $params = array()) {
		$joins = array('joins' => array(array(
			'table' => 'used',
			'alias' => 'Used',
			'type' => 'INNER',
			'conditions' => array(
				"Used.user_id = {$userId}",
				"Used.model = '{$Model->alias}'",
				"Used.foreign_key = {$Model->alias}.id",
				),
			)));
		// note : Last changed this based on adding a user here : /projects/projects/people/{id}
		// make sure it still works there if changed.
		$params = !empty($params) ? array_merge($joins, $params) : $joins;
		// we can do a simple find with the model, because beforeFind of usable limits the results by user
		$results = $Model->find($type, $params);
		if (!empty($results)) { 
			return $results;
		} else {
			return array();
		}
	}
	
	
/**
 *  finds users based on the foreign_key specified and model asking for this function.
 *	uses standard find() parameters after foreignKey
 * @param Model $Model
 * @param null $foreignKey
 * @param string $type
 * @param null $params
 * @return array
 */
	public function findUsedUsers(&$Model, $foreignKey = null, $type = 'list', $params = null) {
		$joins = array('joins' => array(array(
			'table' => 'used',
			'alias' => 'Used',
			'type' => 'INNER',
			'conditions' => array(
				"Used.foreign_key = '{$foreignKey}'",
				"Used.model = '{$Model->alias}'",
				"Used.user_id = User.id",
				),
			)));
		$params = !empty($params) ? array_merge($joins, $params) : $joins;
		
		$Model->bindModel(array(
			'hasMany' => array(
				'Used' => array(
					'className' => 'Users.Used',
					'foreignKey' => 'foreign_key',
					),
				),
			));
		$results = $Model->Used->User->find($type, $params);
		if (!empty($results)) { 
			return $results;
		} else {
			return array();
		}
	}
	
	

/**
 * Add a used user to an object
 *
 *
 * @param Model $Model
 * @param $data | array requires $data['Used']['foreign_key'], $data['Used']['user_id'], $data['Used']['model']
 * @return bool
 * @throws Exception
 */
	public function addUsedUser($Model, $data) {
		$Model->bindModel(
        	array('hasMany' => array(
               	'Used' => array(
                   	'className' => 'Users.Used',
					'foreignKey' => 'foreign_key',
		            )
	            )));
		try {
			$Model->Used->saveAll($data);
			return true;
		} catch (Exception $e) {
			$message = strpos($e->getMessage(), 'key') ? __('User is already involved') : $e->getMessage();
			throw new Exception($message);
		}
	}
	
	
/** 
 * Remove used users from the object
 */
	public function removeUsedUser($Model, $userId = null, $foreignKey = null) {
		if ($Model->Used->deleteAll(array('Used.user_id' => $userId, 'Used.foreign_key' => $foreignKey))) { 
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Remove alsed users from the object
 */
	public function removeUsed($Model, $foreignKey = null) {
		if ($Model->Used->deleteAll(array('Used.foreign_key' => $foreignKey))) { 
			return true;
		} else {
			return false;
		}
	}
	
/**
 * Find child contacts of a parent contact and add them to the data user list
 */
	public function getChildContacts($Model) {
		if (!empty($Model->data[$Model->alias]['contact_id']) && $Model->data[$Model->alias]['contact_all_access']) {
			// add all of the companies people to the used table
			// note, if the model has contact_id, then it should belongTo Contact
			$contacts = $Model->Contact->Employer->find('first', array(
				'conditions' => array(
					'Employer.id' => $Model->data[$Model->alias]['contact_id'],
					),
				'contain' => array(
					'Employee' => array(
						'User',
						),
					),
				));
			foreach ($contacts['Employee'] as $contact) {
				if(!empty($contact['User'])) {
					$users[] = $contact['User'];
				}
			}
		}
		
		if (!empty($users)) {
			$i=0;
			foreach ($users as $user) {
				$Model->data['User'][$i]['user_id'] = $user['id'];
				$Model->data['User'][$i]['model'] = $Model->name;
				$Model->data['User'][$i]['role'] = $this->defaultRole;
				$i++;
			}
		}
		return $Model->data;
	}

/**
 * Privatize method
 * 
 * Create a used record for the purpose of making it private.
 *
 * @param object
 * @param array
 * @access public
 * @return bool
 */
	public function privatize($Model, $data) {
		// if a user is not provided, then the logged in user will be the only allowed user. 
		$data['Used']['user_id'] = !empty($data['Used']['user_id']) ? $data['Used']['user_id'] : CakeSession::read('Auth.User.id');
		
		// if a user is not provided, then the logged in user will be the only allowed user. 
		$data['Used']['foreign_key'] = !empty($data['Used']['foreign_key']) ? $data['Used']['foreign_key'] : $Model->id;
		
		// if a user is not provided, then the logged in user will be the only allowed user. 
		$data['Used']['model'] = !empty($data['Used']['model']) ? $data['Used']['model'] : $Model->alias;
		
		try {
			$this->addUsedUser($Model, $data);
			return true;
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

/**
 * Publicize method
 * 
 * Delete all used records for a particular record. Thereby making it publicly available.
 *
 * @param object
 * @param array
 * @access public
 * @return bool
 */
	public function publicize($Model, $data) {		
		// if a foreignKey is not provided, then the most recent model id will be used
		if (!empty($data['Used']['foreign_key'])) {
			// if a model is not provided then use the current model alias
			$data['Used']['model'] = !empty($data['Used']['model']) ? $data['Used']['model'] : $Model->alias;
		
			$Model->bindModel(
        		array('hasMany' => array(
                	'Used' => array(
                    	'className' => 'Users.Used',
						'foreignKey' => 'foreign_key',
		                )
	    	        )));
			
			if ($Model->Used->deleteAll(array('Used.foreign_key' => $data['Used']['foreign_key']))) { 
				return true;
			} else {
				throw new Exception(__('Could not publicize'));
			}
		} else {
			throw new Exception(__('Foreign required'));
		}
	}
	
}
