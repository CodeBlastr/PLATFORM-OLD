<?php
/**
 * Attribute Group Model
 *
 * Handles the grouping of attributes (think of attributes as form fields).  Attribute Group is literally the model that these attributes belong to, and has a type so that one model can have multiple attributes for different types. 
 *
 * PHP versions 5
 *
 * Zuha(tm) : Business Management Applications (http://zuha.com)
 * Copyright 2009-2010, Zuha Foundation Inc. (http://zuhafoundation.org)
 *
 * Licensed under GPL v3 License
 * Must retain the above copyright notice and release modifications publicly.
 *
 * @copyright     Copyright 2009-2010, Zuha Foundation Inc. (http://zuha.com)
 * @link          http://zuha.com Zuha� Project
 * @package       zuha
 * @subpackage    zuha.app.models
 * @since         Zuha(tm) v 0.0.1
 * @license       GPL v3 License (http://www.gnu.org/licenses/gpl.html) and Future Versions
 */
class AttributeGroup extends AppModel {

	var $name = 'AttributeGroup';	
	var $validate = array(
		'name' => array('notempty'),
		'model' => array('notempty'),
	);
	
	var $userField = array(); # Used to define the creator table field (typically creator_id)
	var $userLevel = false; # Used to define if this model requires record level user access control?
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $hasMany = array(
		'Attribute' => array(
			'className' => 'Attribute',
			'foreignKey' => 'attribute_group_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
		'Enumeration' => array(
			'className' => 'Enumeration',
			'foreignKey' => 'enumeration_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	

/**
 * Finds the attribute group.
 *
 * @param {model}		The model the attribute group belongsTo.
 * @param {typeId}		A limiter or predefined field which can be used to change the attributes that in the end get displayed. Refer to the enumerations table for id numbers.
 */
	function getAttributeGroup($model, $typeId = null) {
		$attributeGroup = $this->find('first', array(
			'conditions' => array(
				'AttributeGroup.model' => $model,
				'AttributeGroup.enumeration_id' => $typeId,
				),
			));
		
		return $attributeGroup;
	}

	
}
?>