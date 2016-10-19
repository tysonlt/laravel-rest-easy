<?php

namespace TysonLT\LaravelRestEasy\Interfaces;

/**
 * Retrieve some basic documentation to display to the user.
 *  
 * @author Tyson
 */
interface DocumentedModel {
	
	/**
	 * Get documentation describing what this model does and how to use it.
	 * 
	 * This information is generally displayed on the index page.
	 */
	public function getGeneralDocumentation();
	
	/**
	 * Specific information describing the fields for this model.
	 * 
	 * Generally displayed at the top of the edit form.
	 */
	public function getDetailedDocumentation();
	
	/**
	 * Get documentation for a specific field.
	 * 
	 * @param string $field The name of the model field (matches the database column name)
	 * @return string|null The field documentation if any, or null.
	 */
	public function getFieldDocumentation($field);
	
}