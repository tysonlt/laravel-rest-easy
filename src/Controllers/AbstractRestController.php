<?php

namespace TysonLT\LaravelRestEasy\Controllers;

use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use TysonLT\LaravelRestEasy\Interfaces\Displayable;

/**
 * TODO: pull the persistence code out into a persistence strategy (like import)
 * 
 * @package App\Http\Controllers
 */
abstract class AbstractRestController extends Controller {

	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
	
	protected $redirectToViewAfterCreate = false;
	
    protected $indexViewName = 'index';
    protected $editViewName = 'edit';
    protected $viewViewName = 'view';
    protected $confirmDeleteViewName = 'resteasy.confirm';

    /** @var Whether to check the request for defaults to apply to new models */
    protected $requestDefaultsForNewModel = true;
    
    /**
     * Return the path that this resource was mapped to,
     * including prefixes.
     *
     * @return mixed
     */
    protected abstract function getRoutePath();

    /**
     * Return the model class to use for this resource.
     *
     * @return mixed
     */
    protected abstract function getModelClass();

    /**
     * Which fields to display in the index page.
     * 
     * Defaults to $model->getDisplayableAttributes().
     * return array A list of displayable attributes and their titles.
     */
    public function getModelIndexFields() {
    	return $this->newModel()->getDisplayableAttributes();
    }
    
    /**
     * Which fields to display on the detail view page.
     *
     * Defaults to $model->getDisplayableAttributes().
     * return array A list of displayable attributes and their titles.
     */    
    public function getModelViewFields() {
    	return $this->newModel()->getDisplayableAttributes();
    }
    
    /**
     * Controls which buttons are shown on the index page.
     * 
     * Defaults to ['create', 'view', 'edit', 'delete'], which 
     * is all of the available methods.
     * 
     * @return array Array of allowed methods ('create', 'view', 'edit', 'delete')
     */
    protected function getAllowedMethods() {
    	return ['create', 'view', 'edit', 'delete'];
    }
    
    /**
     * A friendly name for this resource.
     *
     * Defaults to Str::title($this->getResource())
     */
    public function getResourceName() {
        return Str::title($this->getResource());
    }

    /**
     * List websites
     *
     * @return mixed
     */
    public function index()
    {
        $records = $this->listModels();
        
        if (request()->wantsJson()) {
        	return $records;
        } else {
	        return view(
	            $this->getViewPath($this->indexViewName),
	            $this->buildIndexViewData($records)
	        );
        }
    }

    /**
     * @param $class
     * @return mixed
     */
    protected function listModels() {
        return $this->buildListQuery()->get();
    }

    /**
     * Returns the query used by listModels().
     */
    protected function buildListQuery() {
    	$class = $this->getModelClass();
    	$query = $class::orderBy($this->getOrderByAttribute(), $this->getOrderByDirection());
    	return $query;
    }
    
    /**
     * Defaults to display attribute name.
     */
    protected function getOrderByAttribute() {
    	$class = $this->getModelClass();
    	$model = new $class;
    	$order = $model->getDisplayAttributeKey();
    	if (is_array($order)) {
    		$order = array_first($order);
    	}
    	return $order;
    }

    /**
     * 
     */
    protected function getOrderByDirection() {
    	return 'asc';
    }
    
    /**
     * Create a new model.
     */
    protected function newModel() {
    	$class = $this->getModelClass();
    	return new $class();
    }
    
    /**
     * Turn route into a view path.
     *
     * @param $viewName
     * @param \Illuminate\Routing\Route $route
     * @return string
     */
    protected function getViewPath($viewName, $route = null) {

    	$viewPath = null;
    	$triedViewPaths = [];        

        if (null == $route) {
            $route = Route::current();
        }

        //default to resource path
        $baseViewPath = $this->getBaseViewPath();

        //check if custom view exists
        $viewPath = $baseViewPath .'.'. $viewName;

        //if not found, try lower-casing the path
        if (false == view()->exists($viewPath)) {
        	$triedViewPaths[] = $viewPath;
        	$viewPath = strtolower($baseViewPath) .'.'. $viewName;        	
        }
        
        //if not found, use generic view
        if (false == view()->exists($viewPath)) {
        	$triedViewPaths[] = $viewPath;
        	$viewPath = "resteasy.$viewName";
        }
		
        //give some help
        if (false == view()->exists($viewPath)) {
        	$triedViewPaths[] = $viewPath;
        	throw new \InvalidArgumentException("Could not find view path, tried: ". join(', ', $triedViewPaths));
        }

        return $viewPath;

    }
    
    /**
     * 
     */
    protected function getBaseViewPath() {
    	return str_replace('/', '.', trim($this->getRoutePath(), '/'));
    }

    /**
     * @param $routePrefix
     * @param string $routeDelimiter Default '\\'
     * @return string Package fragment.
     */
    protected function buildSubPackage($routePrefix, $routeDelimiter = '\\') {
        $result = null;
        foreach (explode('/', $routePrefix) as $part) {
            if (false == empty($part)) {
                $result .= Str::title($part) . $routeDelimiter;
            }
        }
        return $result;
    }

    /**
     * @param Route $route
     * @return string
     */
    protected function getResource($route = null) {

        if (null == $route) {
            $route = Route::current();
        }

        //get uri without prefix
        $uri = substr($route->getUri(), strlen($route->getPrefix()));

        //get just the resource part
        $pos = strpos($uri, '/');
        if (false !== $pos) {
            $uri = substr($uri, 0, $pos);
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function show($id)
    {
    	$model = $this->loadModel($id);
    	if (request()->wantsJson()) {
    		return $model;
    	} else {
	        return view(
	            $this->getViewPath($this->viewViewName),
	            $this->buildFormViewData($model, null, null)
	        );
    	}
    }

    /**
     * Form to create a new item
     */
    public function create()
    {
        return $this->form(
            $this->fillNewModelFromRequest($this->newModel()),
            url($this->getRoutePath()),
            'post'
        );
    }

    /**
     * Form to edit exiting model.
     *
     * @return string
     */
    public function edit($id)
    {
        return $this->form(
            $this->loadModel($id),
            url($this->getRoutePath() . "/$id"),
            'put'
        );
    }

    /**
     * @param $id
     * @param null $class
     * @return mixed
     */
    protected function loadModel($id) {
		$class = $this->getModelClass();
        return $class::findOrFail($id);
    }

    /**
     * @param null $id
     * @param null $class
     * @return Displayable
     */
    protected function loadOrCreateModel($id = null) {

        if (null == $id) {
            $model = $this->newModel();
        } else {
            $model = $this->loadModel($id);
        }

        return $model;

    }

    /**
     * There might be defaults in the request.
     * @param $model
     */
    protected function fillNewModelFromRequest($model) {
    	if ($this->requestDefaultsForNewModel) {
    		$model->fill(request()->all());
    	}
    	return $model;
    }

    /**
     * @param Displayable $model
     * @param $formAction
     * @param $formMethod
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function form(Displayable $model, $formAction, $formMethod)
    {
        return view(
            $this->getViewPath($this->editViewName),
            $this->buildFormViewData($model, $formAction, $formMethod)
        );
    }

    /**
     * Allows custom REST-style actions to be performed on a model.
     * 
     * @param id $id
     * @param string $action
     */
    public function action($id, $action) {
    	return call_user_func([$this, $action], $id);
    }
    
    /**
     * Returns true if the controller supports the requested action
     * and the user is allowed to perform it.
     * 
     * @param string $action
     * @param bool $checkGuard
     */
    public function allow($action, Model $model) { 	
    	return in_array($action, $this->getAllowedMethods()) && Auth::user()->can($action, $model);
    }
    
    /**
     * Returns !empty($this->getAllowedMethods());
     */
    public function isInteractive() {
    	return false == empty($this->getAllowedMethods());
    }
    
    /**
     * @param $id
     * @return View
     */
    public function confirm($id) {

        $model = $this->loadModel($id);

        return view($this->confirmDeleteViewName, $this->buildConfimDeleteViewData($model));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {
        return $this->storeOrUpdate($request, null);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id) {    	
        return $this->storeOrUpdate($request, $id);
    }

    /**
     * Update a model, or create a new one if ID is null.
     *
     * @param Request $request
     * @param null $id
     * @param null $class
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function storeOrUpdate(Request $request, $id = null) {
            	
    	//get the model
    	$model = $this->loadOrCreateModel($id);
    	
    	//validate the request
    	$this->validateRequest($request, $model);
    	
    	//populate it from request
        $this->populate($model, $request);
        
        //try to save it
        $this->saveModel($model);
        
        //redirect if successful
        if ($this->redirectToViewAfterCreate) {
        	$view = redirect($this->getRoutePath() .'/'. $model->getKey());
        } else {
        	$view = redirect($this->getRoutePath());
        }
        
        //return 
        return $view->with('success', $model->displayName .' has been saved.');
        
    }

    /**
     * Validate the request.
     * 
     * Default impl validates display attribute(s).
     * 
     * @param Request $request
     * @param Model $model
     * 
     * @throws ValidationException
     */
    private function validateRequest(Request $request, Model $model) {

    	$this->validate(
    		$request, 
    		$this->getValidationRules($request, $model),
    		$this->getValidationMessages($request, $model)
    	);
    	
    }
    

    /**
     * Return the rules.
     *
     * @param Request $request
     * @param Model $model
     */
    protected function getValidationRules(Request $request, Model $model) {

    	$rules = [];
    	$nameAttributes = [];
    	
    	if ($model instanceof Displayable) {
    		$nameAttributes = $model->getDisplayAttributeKey();
    		if (false == is_array($nameAttributes)) {
    			$nameAttributes = [$nameAttributes];
    		}
    	}
    	
    	foreach ($nameAttributes as $nameAttribute) {
    		$rules[$nameAttribute] = 'required';
    	}
    	
    	return $rules;
    	
    }
    
    /**
     * Return the messages.
     *
     * @param Request $request
     * @param Model $model
     */
    protected function getValidationMessages(Request $request, Model $model) {
    	return [];
    }
        
    /**
     * Populate model from request.
     *
     * @param Displayable $model
     * @param Request $request
     */
    protected function populate(Displayable $model, Request $request) {
        $model->fill($request->all());
    }

    /**
     * Save the model.
     *
     * @param $model
     */
    protected function saveModel($model) {
        $model->save();
    }

    /**
     * @param $id
     * @return string
     */
    public function destroy($id) {

        $resourceName = Str::title($this->getResource());
        $class = $this->getModelClass();

        $model = $class::findOrFail($id);
        $name = $model->display_name;
        $model->delete();

        return redirect($this->getRoutePath())->with(
            $this->getDeleteSuccessMessageFlashKey(),
            $this->buildDeleteMessage($resourceName, $name)
        );

    }

    /**
     * @return string
     */
    protected function getDeleteSuccessMessageFlashKey() {
        return 'status';
    }

    /**
     * @param $resourceName
     * @param $name
     * @return string
     */
    protected function buildDeleteMessage($resourceName, $name) {
        return "$resourceName '$name' has been deleted.";
    }
    
    /**
     * @param $modelClass
     * @param $records
     * @return array
     */
    protected function buildIndexViewData($records, $modelClass = null) {
    	if (null == $modelClass) {
    		$modelClass = $this->getModelClass();
    	}
    	
    	return $this->buildFormViewData(new $modelClass) + ['records' => $records];
    	
    }

    /**
     * @param $model
     * @param $formAction
     * @param $formMethod
     * @return array
     */
    protected function buildFormViewData($model, $formAction = '', $formMethod = '') {
        return [
            'model' => $model,
            'formAction' => $formAction,
            'formMethod' => $formMethod,
        	'route' => $this->getRoutePath(),
        	'user' => Auth::user(),
        	'controller' => $this,
        	'resource' => $this->getResource(),
            'resourceName' => $this->getResourceName(),
        	'allowed' => $this->getAllowedMethods(),
        	'cancelUrl' => $this->getRoutePath(),
        ];
    }

    /**
     * @param Displayable $model
     * @return array
     */
    protected function buildConfimDeleteViewData(Displayable $model) {
        return [
            'modelName' => $this->getResource(),
            'modelDisplay' => $model->displayName,
            'resourcePath' => $this->getRoutePath() .'/'. $model->getAttribute('id'),
            'returnPath' => $this->getRoutePath(),
            'resourceName' => $this->getResourceName()
        ];
    }

}