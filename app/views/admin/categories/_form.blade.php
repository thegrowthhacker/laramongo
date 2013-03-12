<?php
    if(isset($category))
        $f = array_merge( $category->attributes, Input::old() );
    else
        $f = array_merge( Input::old() );
?>

{{-- Since multiple line brackets are not yet implemented --}}
{{-- see: https://github.com/laravel/framework/issues/88  --}}
<?=
    Form::open([   
        'url' => URL::action(
            isset( $action ) ? $action : 'Admin\CategoriesController@store',
            isset( $category ) ? ['id'=>$category->id] : []
        ),
        'method'=> isset( $method ) ? $method : 'POST',
        'files'=>true,
        'class'=>'form-horizontal'
    ])
?>
    @if ( isset($category) )
        {{ HTML::image($category->imageUrl()) }}
    @endif
    
    <div class="control-group">
        {{ Form::label('name', 'Nome da Categoria', ['class'=>'control-label']) }}
        <div class="controls">
            {{ Form::text('name', array_get( $f,'name') ) }}
        </div>
    </div>

    <div class="control-group">
        {{ Form::label('description', 'Descrição', ['class'=>'control-label']) }}
        <div class="controls">
            {{ Form::textarea('description', array_get( $f,'description') ) }}
        </div>
    </div>

    <div class="control-group">
        {{ Form::label('image_file', 'Escolha uma imagem para a categoria', ['class'=>'control-label']) }}
        <div class="controls">
            {{ Form::file('image_file') }}
        </div>
    </div>

    {{ Form::hidden('active', array_get( $f,'active') ) }}

    @if ( Session::get('error') )
        <div class="alert alert-error">
            @if ( is_array(Session::get('error')) )
                {{ Session::get('error')[0] }}
            @else
                {{ Session::get('error') }}
            @endif
        </div>
    @endif

    <div class='form-actions'>
        {{ Form::button('Salvar categoria', ['type'=>'submit', 'class'=>'btn btn-primary'] ) }}

        @if ( isset($category) )
            {{ HTML::action( 'Admin\CategoriesController@destroy', 'Excluir', ['id'=>$category->id], ['data-method'=>'delete', 'class'=>'btn btn-danger'] ) }}
        @endif

        {{ HTML::action( 'Admin\CategoriesController@index', 'Cancelar', [], ['class'=>'btn'] ) }}
    </div>
{{ Form::close() }}
