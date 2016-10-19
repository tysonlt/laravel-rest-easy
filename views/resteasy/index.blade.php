<?php
use TysonLT\LaravelRestEasy\Interfaces\Displayable;
use TysonLT\LaravelRestEasy\Interfaces\DocumentedModel;

?>
@extends("layouts.app")

@section("content")
		
	<div class="resteasy-generic-form-title">
		<h2 style="margin-top: 0px">{{ $controller->getResourceName() }}</h2>
	</div>
	
	<?php if (!($model instanceof Displayable)): ?>
		<B>Model <?php echo get_class($model) ?> must implement Displayable</B>
	<?php else: ?>
	
		<?php if ($model instanceof DocumentedModel): ?>
			<div class="resteasy-documented-model-general-documentation">
				<p>{{ $model->getGeneralDocumentation() }}</p>
			</div>
		<?php endif; ?>
	
		<br/>
	
		<div class="resteasy-index-button-bar" style="margin-bottom: 50px">
			@if($controller->allow('create', $model))
		        <a href="{{ $route }}/create" class="btn btn-success">New {{ $resourceName }}</a>
	    	@endif
			@yield('customActions')
		</div>
		
		<div id="data-table-container">
		    <table id="data-table" class="table table-bordered">

		        <thead>
		            @foreach($controller->getModelIndexFields() as $column => $display)
		                <th>{{ $display }}</th>
		            @endforeach
		            
		            @if($controller->isInteractive())
		            	<th style="width: 15%">Actions</th>
		            @endif
		        </thead>
		
		        <tbody>

		        @foreach($records as $record)
		            <tr>
		                @foreach($controller->getModelIndexFields() as $column => $display)
		                    <th>
		                        {{ $record->display($column) }}                        
		                    </th>
		                @endforeach
		                
		                @if($controller->isInteractive())
			                <td>
			                    @if($controller->allow('view', $record))
			                    	<a href="{{ $route }}/{{ $record->getKey() }}" class="btn btn-success pull-left" style="margin-right: 10px">View</a>
			                    @endif
			                    
			    				@if($controller->allow('edit', $record))            	
			                    	<a href="{{ $route }}/{{ $record->getKey() }}/edit" class="btn btn-success pull-left" style="margin-right: 10px">Edit</a>
			                    @endif
			                                        
			                    @if($controller->allow('delete', $record))
			                    	<a href="{{ $route }}/{{ $record->getKey() }}/action/confirm" class="btn btn-danger">Delete</a>
			                    @endif
			                    
			                </td>
			            @endif
			            
		            </tr>
		        @endforeach
		
		        </tbody>
		
		    </table>
		</div>
		
		@section('footerScripts')
			
	    <script>
	        $(document).ready(function() {
		        //disable initial sort
	        	$('#data-table').DataTable({
	        		"aaSorting": []
		        });
	        });
	    </script>
	    
	    @endsection
	    
	<?php endif; ?>

@stop