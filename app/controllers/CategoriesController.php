<?php

class CategoriesController extends BaseController {

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show($id)
    {
        $category = Category::first($id);

        if(! $category || ! $category->isVisible())
        {
            return Redirect::action('HomeController@index')
                ->with( 'flash', 'Categoria não encontrada' );
        }

        if($category->type == 'leaf')
        {
            $page = Input::get('page') ?: 1;

            if(Config::get('search_engine.enabled'))
            {
                $searchEngine = App::make(Config::get('search_engine.engine'));
                $searchEngine->connect();
                $searchEngine->facetSearch( $category, Input::get('filters') );

                $products = Product::where(
                    ['_id'=>[
                        '$in'=>array_map('intval', $searchEngine->getIdOfHits())
                    ]]
                )
                    ->limit(12)
                    ->skip( ($page-1)*12 );
            }
            else
            {
                $products = Product::where(['category'=>(string)$category->_id, 'deactivated'=>null])
                    ->limit(12)
                    ->skip( ($page-1)*12 );
            }           

            $parameters = array(
                'category' => $category,
                'products'=> $products,
                'total_pages'=> round($products->count()/12),
                'page'=> $page,
                'facets'=> (isset($searchEngine)) ? $searchEngine->getFacetResult() : array()
            );

            if( Input::get('ajax') || Request::ajax() )
            {
                // For ajax request, don't return the layout or the complete view
                return Template::make('categories._paginate', $parameters);
            }
            else
            {
                // For non ajax requests, return the layout with the view embeded
                $this->layout->content = Template::make('categories.show', $parameters);
            }
        }
        else
        {
            $subCategories = Category::where(['parents'=>$category->_id, 'hidden'=>['$ne'=>'true']]);

            $this->layout->content =
                Template::make('categories.subcategories',
                    array(
                        'category' => $category,
                        'subCategories' => $subCategories
                    )
                );
        }
    }
}
