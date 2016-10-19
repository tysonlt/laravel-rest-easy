<?php

namespace TysonLT\LaravelRestEasy\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Scans the model for 'onXxx' methods and registers matching model events.
 * 
 * @author Tyson
 */
trait EventAware {

	/**
	 * Scan for events.
	 * 
	 * Called in boot() to ensure it doesn't load multiple times
	 */
	protected static function boot() {
		self::scanEvents();
	}
	
	/**
	 * Look for named event handlers in the calling model.
	 */
	protected static function scanEvents() {
		
		$modelClass = static::class;
		$model = new static;
		
		//list all the available events in this model
		foreach ($model->getObservableEvents() as $eventName) {
	
			//build our listener name (eg. event 'saved' is handled by 'onSaved')
			$listenerMethodName = self::buildListenerMethodName($eventName);

			//if the listener method exists, register the event
			if (method_exists($model, $listenerMethodName)) {
				
				//register the event
				call_user_func(
					[$modelClass, $eventName], //MyModel::saved(...) 
					[$model, $listenerMethodName] //$model->onSaved(...)
				);
				
				//Log::debug(sprintf("Registered %s() for %s::%s", $listenerMethodName, $modelClass, $eventName));
				
			}
			
		}
		
	}
	
	/**
	 * Turns 'saved' into 'onSaved'.
	 * 
	 * @param string $eventName
	 */
	protected static function buildListenerMethodName($eventName) {
		return 'on' . Str::ucfirst($eventName);
	}
	
}