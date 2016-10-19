<?php

namespace TysonLT\LaravelRestEasy\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * 
 * @author Tyson
 */
class LaravelRestEasyProvider extends ServiceProvider {
	
	const PACKAGE_NAME = 'resteasy';
	
	/**
	 * 
	 */
	public function boot() {
		
		$this->loadViewsFrom(__DIR__ .'../../views', self::PACKAGE_NAME);

		$this->publishes([
			__DIR__ .'../../views' => resource_path('views/vendor/'. self::PACKAGE_NAME),
		]);
			
	}
	
}
