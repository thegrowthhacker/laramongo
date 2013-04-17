<?php namespace Laramongo\TemplateBuilder;

use App, Lessy, Basset;

class TemplateBuilder
{
    protected $name = 'base';

    function __construct()
    {
        if( app()->environment() != 'production' )
        {
            Lessy::compileTree(
                'views/templates/'.$this->name.'/css',
                '../public/assets/css/templates/'.$this->name
            );

            Lessy::compileTree(
                'views/templates/'.$this->name.'/img',
                '../public/assets/img/templates/'.$this->name
            );
        }

        Basset::collection('website')
            ->requireTree('assets/css/templates/'.$this->name);
    }

    /**
     * Get the template name.
     * Important: The name is lowercase as if it was a directory
     *
     * @return string Template name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a View::make with the params specified. It also
     * sets the current template based in the $params passed to the
     * View
     *
     * @param  string  $view
     * @param  mixed   $data
     * @return Illuminate\View\View
     */
    public function make( $view, $params = array() )
    {
        $template = $this->setTemplateFor($params);

        return App::make('view')->make( 'templates.' .$template.'.'.$view, $params );
    }

    /**
     * This function will bind a TemplateBuilder to the Template
     * facade based in the $params array.
     *
     * For example:
     *     If the params array contains a key called 'product'
     *     and the value is a instanceOf Product. The template will
     *     be set based in the $product->category()->template
     *
     * @param array $params A set of variables that were passed to the view
     */
    public function setTemplateFor( $params )
    {
        $templateName = '';

        foreach ($params as $param) {

            if($param instanceOf \Category)
            {
                $templateName = $param->template;
            }
            elseif( $param instanceOf \Product)
            {
                $templateName = $param->category()->template;
            }
        }

        if(! $templateName)
            $templateName = 'base';

        // This way, each template should have a Templates\Templatename\TemplateBuildler class
        App::bind('Template', 'Templates\\'.ucfirst($templateName).'\TemplateBuilder');

        return $templateName;
    }
}
