@foreach ($products as $product)
    <a href='{{ URL::action('ProductsController@show', ['id'=>$product->_id]) }}'>
        <div class='product_tile'>
            <h3>{{ $product->name }}</h3>
            <img style='background-image: url({{ $product->imageUrl(3) }})'>
            @include('templates.base.products._price')
        </div>
    </a>
@endforeach
