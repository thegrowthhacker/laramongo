<?php

use Illuminate\Support\MessageBag;

class Category extends BaseModel implements Traits\ToTreeInterface {
    use Traits\HasImage, Traits\ToTree, Traits\ToSelect;

    /**
     * The database collection
     *
     * @var string
     */
    protected $collection = 'categories';

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = array(
        'name' => 'required',
    );

    /**
     * Attributes that will be generated by FactoryMuff
     */
    public static $factory = array(
        'name' => 'string',
        'parents' => array(),
        'shortDesc' => 'text',
        'description' => 'text',
        'template' => 'default',
        'productTemplate' => 'default'
    );

    protected $guarded = array(
        'image_file',
        '_id',
    );

    /**
     * Protected attribute containing the options of the tree
     *
     * @var array
     */
    static protected $treeOptions = array(
        'nodeView' => 'admin.categories._tree_node',
        'nodeName' => 'category'
    );

    /**
     * Reference to parent
     */
    public function parents()
    {
        return $this->referencesMany('Category','parents');
    }

    /**
     * Embedded characteristics
     */
    public function characteristics()
    {
        return $this->embedsMany('Characteristic','characteristics');
    }

    /**
     * A full ancestors tree
     */
    public function ancestors()
    {
        return $this->embedsMany('Category','ancestors');
    }

    /**
     * Return all the childs. Use carefully.
     *
     */
    public function childs()
    {
        return Category::where(['parents'=>$this->_id]);
    }

    /**
     * Verify if the model is valid
     *
     * @return bool
     */
    public function isValid()
    {
        $valid = parent::isValid();

        if( $valid )
        {
            // does a category with the same name and with different _id exists?
            $exists = Category::where(['name'=>$this->name, '_id'=>['$ne'=>$this->_id]])->count();

            if( $exists )
            {
                $this->errors = new MessageBag(['Já existe uma categoria com esse nome']);
                return false;
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if a category is visible or not. This takes a decision
     * assembling the following facts:
     * - hidden is not any sort of 'true'
     * - category has an _id
     */
    public function isVisible()
    {
        return 
            $this->hidden == false &&
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
     * Save the model to the database if it's valid
     * Before saving, build ancestor tree
     *
     * @return bool
     */
    public function save( $force = false )
    {

        if( $this->isValid() )
        {
            $this->buildAncestors();
            return parent::save( $force );

            foreach ($this->childs() as $child) {
                $child->buildAncestors();
                $child->save( $force );
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * Build ancestors tree within this category
     */
    public function buildAncestors()
    {
        unset($this->ancestors);
        if($this->parents())
        {
            $this->ancestors = $this->parents()->toArray();
        }
    }

    /**
     * Validate every product within this category. This may be used
     * in order to validate new characteristics that were included.
     */
    public function validateProducts()
    {
        foreach (Product::where(['category'=>(string)$this->_id]) as $product) {
            if(! $product->isValid())
            {
                $product->save(true);
            }
        }

        return true;
    }

    /**
     * Returns the ammount of products that this category have
     */
    public function productCount()
    {
        $productCount = Cache::rememberForever("category_".$this->_id."_prod_count", function()
            {
                return Product::where(['category'=>(string)$this->_id])->count();
            });

        return $productCount;
    }

    /**
     * Renders the menu
     *
     * @return string Html code of menu tree
     */
    public static function renderMenu()
    {
        $options = array(
            'nodeView' => 'layouts.website._menu_node',
            'nodeName' => 'category'
        );

        return static::renderTree( array(), $options );
    }

    /**
     * Return an array containing name, parent indexed
     * by _id. The purpose of this is to be used with 
     * laravel's Form::select
     *
     * @return array
     */
    public static function toOptions( $query = array() )
    {
        $all = static::where( $query );
        $result = array();

        foreach ($all as $item) {

            $displayedName = $item->name;

            $ancestor = $item;
            while( isset($ancestor->ancestors()[0]) )
            {
                $ancestor = $ancestor->ancestors()[0];
                $displayedName =  $ancestor->name.' > '.$displayedName;
            }

            $result[(string)$item->_id] = $displayedName;
        }

        return $result;
    }

}
