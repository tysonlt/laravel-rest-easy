@extends('layouts.app')

@section('content')

    <div class="col-lg-4 col-lg-offset-4">

        <div>
            <h1 style="color:red">CONFIRM DELETE</h1>
        </div>

        <br/>

        <h5>
            Are you sure you want to delete {{ $modelName }} '{{ $modelDisplay }}'?
        </h5>

        <br/>

        {!! Form::open(['url' => $resourcePath, 'method' => 'DELETE']) !!}

        {!! Form::submit("Yes, blow '$modelDisplay' away forever", ['class' => 'btn btn-danger']) !!}
        <a href='{{ $returnPath }}' class='btn btn-success'>No, take me back</a>

        {!! Form::close() !!}

    </div>

@stop