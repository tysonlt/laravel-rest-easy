<?php

namespace TysonLT\LaravelRestEasy\Traits;

use App\Interfaces\Displayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;

/**
 * Base class for models that support user-friendly display.
 * 
 * @author Tyson
 */
trait DisplayableTrait {

	protected $displayMaxLength = 80;	
	protected $truncatedMessage = '...';
	
    /**
     * Returns a string representation of this model.
     *
     * @return mixed
     */
    public function getDisplayNameAttribute()
    {
        $value = null;
        $key = $this->getDisplayAttributeKey();

        if (is_array($key)) {
            foreach ($key as $k) {
                $value .= $this->getAttributeValue($k) .' ';
            }
        } else {
            $value = $this->getAttributeValue($key);
        }

        return trim($value);
    }

    /**
     * Which column is used to create the display name.
     *
     * Tries 'title' and the 'name', which are found on most entities.
     *
     * @return string|array If array, all keys will have their values concatenated with a space.
     */
    public function getDisplayAttributeKey()
    {
        if ($this->hasAttribute('title')) {
            return 'title';
        } else {
            return 'name';
        }
    }

    /**
     * Default impl attempts to create nice names from getVisible()
     */
    public function getDisplayableAttributes()
    {
        $result = [];
        foreach ($this->getVisible() as $attribute) {

        	//default unprocessed
        	$display = $attribute;
        	
            //filter out id
            if ('_id' == substr($display, -3)) {
                $display = substr($display, 0, -3);
            }

            //replace _ with space
            $display = str_replace('_', ' ', $display);
            
            //title
            $display = Str::title($display);
            
            $result[$attribute] = $display;
        }

        return $result;

    }
    
    /**
     * Process the column name a bit for displaying.
     * 
     * @param string $column
     */
    public function getDisplayableColumnName($column) {
    	
    	$result = $column;
    	
    	//if this is an id, assume a linked model
    	if (substr($result, -3) == '_id') {
    		$result = substr($result, 0, -3);
    	}
    	
    	return $result;
    }
    
    /**
     * Display the value of this column.
     * 
     * Linked records will be displayed if the column
     * name ends with '_id'.
     * 
     * @param string  $column
     */
    public function display($column) {
    	
    	$result = $this->getAttribute($column);
    	
    	//if this is an id column, display linked record
    	if (substr($column, -3) == '_id') {
    		
    		//is this a displayable linked model?
    		$linked = substr($column, 0, -3);    		

    		//fetch displayable and get display name
    		if ($this->{$linked} instanceof Displayable) {
    			$result = $this->{$linked}->displayName;
    		}
    		
    	}
    	
    	//check for inverse relationships
		if ($result instanceof Collection) {
			$names = [];
			foreach ($result as $row) {
				$names[] = $row instanceof Displayable ? $row->displayName : (string) $row;
			}
			$result = join(', ', $names);
		}
    	
    	if (strlen($result) > $this->displayMaxLength) {
			$result = substr($result, 0, $this->displayMaxLength) . $this->truncatedMessage;
		}
		
    	return $result;
    	
    }

    /**
     * Whether the attribute exists.
     *
     * @param $attr
     * @return mixed
     */
    public function hasAttribute($attr)
    {
        return array_key_exists($attr, $this->attributes);
    }

    /**
     * Supplies a default of ['id', $this->getDisplayAttributeKey(), 'created_at'].
     *
     * Used to create auto-generated index views.
     *
     * @return array
     */
    public function getVisible()
    {
        $visible = parent::getVisible();
        if (empty($visible)) {
        	$display_keys = $this->getDisplayAttributeKey();
        	if (false == is_array($display_keys)) {
        		$display_keys = [$display_keys];
        	}
            $visible = ['id'] + $display_keys + ['created_at'];
        }
        return $visible;
    }

    /**
     * Return the name of this model.
     * 
     * If $modelName is not defined in the calling class, the 
     * short class name is returned.
     */
    public function getModelName() {
    	return 
    		isset($this->modelName) ? 
    		$this->modelName :
    		ucwords(Str::snake((new \ReflectionClass($this))->getShortName(), ' '));
    }
    
}