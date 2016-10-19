<?php
use TysonLT\LaravelRestEasy\Interfaces\Displayable;
use TysonLT\LaravelRestEasy\Interfaces\DocumentedModel;

?>
@extends('layouts.app')

@section('content')
	
	<?php if ($model instanceof Displayable): ?>
	<div class="resteasy-generic-form-title">
		<h2>View {{ $controller->getResourceName() }}</h2>
	</div>
	<?php endif; ?>
	
	<?php if ($model instanceof DocumentedModel): ?>
	<div class="resteasy-documented-model-detailed-documentation">
		<p>{{ $model->getDetailedDocumentation() }}</p>
	</div>
	<?php endif; ?>
	
	@yield('aboveContent')
	
    <table class="table table-bordered table-striped table-condensed resteasy-view-table">

        <thead>
            <tr>
                <th width="180px">Field</th>
                <th>Value</th>
            </tr>
        </thead>

        <tbody>
        @foreach($controller->getModelViewFields() as $name => $title)
            <tr valign="top">
                <td><b>{{$title}}</b></td><td>{{ $model->display($name) }}</td>
            </tr>
        @endforeach
        </tbody>

    </table>
    
    @yield('belowContent')

    <br/>
                    
    	@if($controller->allow('edit', $model))
        	<a href="{{ $route }}/{{ $model->getKey() }}/edit" class="btn btn-success pull-left" style="margin-right: 10px">Edit</a>
        @endif
                                        
        @if($controller->allow('delete', $model))
        	<a href="{{ $route }}/{{ $model->getKey() }}/action/confirm" class="btn btn-danger" style="margin-right: 10px">Delete</a>
        @endif
    
    	<a href="{{ $cancelUrl }}" class="btn btn-success">Back</a>
                
    @yield('belowButtons')
        

@stop