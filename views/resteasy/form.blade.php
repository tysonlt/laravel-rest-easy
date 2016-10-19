<?php
use TysonLT\LaravelRestEasy\Interfaces\Displayable;
use TysonLT\LaravelRestEasy\Interfaces\DocumentedModel;

?>
@extends('layouts.app')

@section('content')
	
	<div class="row">
	
		<div class="col-xs-12 col-md-6">
	
			<?php if ($model instanceof Displayable): ?>
				<div class="resteasy-generic-form-title">
					<h2><?php echo $model->getKey() ? 'Edit' : 'Create' ?> {{ $controller->getResourceName() }}</h2>
				</div>
			<?php endif; ?>
			
			<?php if ($model instanceof DocumentedModel): ?>
			<div class="resteasy-documented-model-detailed-documentation">
				<p>{{ $model->getDetailedDocumentation() }}</p>
			</div>
			<?php endif; ?>
			
			<br/>
		
		    @if ($errors->count())
		        <div class="alert alert-danger">
		            <ul>
		                @foreach ($errors->all() as $error)
		                    <li>{{ $error }}</li>
		                @endforeach
		            </ul>
		        </div>
		    @endif
		    
		    @yield('form')
	    
	    </div>
	    
	</div>

@stop