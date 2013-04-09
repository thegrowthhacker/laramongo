<?php

class Content extends BaseModel {

    /**
     * The database collection
     *
     * @var string
     */
    protected $collection = 'contents';

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = array(
        'name' => 'required',
        'slug' => 'required',
        'kind' => 'required',
    );

    /**
     * Validation rules
     *
     * @var array
     */
    public static $factory = array(
        'name' => 'text',
    );

    /**
     * The products attached to the content
     */
    public function products()
    {
        return $this->referencesMany('Product','products');
    }

    /**
     * Determines if a content is visible or not. This takes a decision
     * assembling the following facts:
     * - hidden is not any sort of 'true'
     * - content has an _id
     */
    public function isVisible()
    {
        return 
            $this->hidden == false &&
            $this->approved == true &&
            $this->_id != false;
    }

    /**
     * Simply set the hidden attribute to true
     */
    public function hide()
    {
        $this->hidden = true;
    }

    /**
     * Simply unset the hidden attribute
     */
    public function unhide()
    {
        unset($this->hidden);
    }

    /**
     * Polymorph into ArticleContent if the kind is equals
     * to 'article'
     *
     * return mixed $instance
     */
    public function polymorph( $instance )
    {
        if( $instance->kind = 'article' )
        {
            $article = new ArticleContent;

            $article->parseDocument( $instance->attributes );
            return $article;
        }
        else
        {
            return $instance;
        }
    }

    /**
     * Overwrites the setAttribute method in order to
     * explode a string into array before setting the
     * tags attribute
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if($key == 'tags')
        {
            if(is_string($value))
            {
                $value = array_map('trim',explode(",",$value));
            }
        }

        return parent::setAttribute($key, $value);
    }

}
