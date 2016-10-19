<?php

namespace TysonLT\LaravelRestEasy\Interfaces;

/**
 * A model that can represent itself with a user-friendly display name.
 *
 * @package App\Models\Interfaces
 */
interface Displayable {

    /**
     * Returns a string representation of this model.
     *
     * Invoke with '$model->displayName'.
     *
     * @return mixed
     */
    public function getDisplayNameAttribute();

    /**
     * Which column is used to create the display name
     *
     * @return mixed
     */
    public function getDisplayAttributeKey();

    /**
     * Return a key/value array of displayable attributes.
     *
     * Key: Column name
     * Value: User-friendly column name
     *
     * @return array
     */
    public function getDisplayableAttributes();
    
    /**
     * Display the value of this column.
     * 
     * Attempt to detect relationships and return
     * the displayName attribute of the linked model.
     * 
     * @param string $column
     */
    public function display($column);
    
    /**
     * Return the friendly name of the model.
     */
    public function getModelName();
    
}