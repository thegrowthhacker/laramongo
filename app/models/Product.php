<?php

use Illuminate\Support\MessageBag;

class Product extends BaseModel {
    use Traits\ToPopover;

    /**
     * The database collection
     *
     * @var string
     */
    protected $collection = 'products';

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = array(
        'name'      => 'required',
        'category'     => 'required',
    );

    /**
     * Setable with the fill method
     */
    public $fillable = array();

    /**
     * Attributes that will be generated by FactoryMuff
     */
    public static $factory = array(
        'name' => 'text',
        'category' => 'factory|Category',
        'shortDesc' => 'text',
        'description' => 'text',
        'active' => true,
    );

    /**
     * View that are gonna be rendered by the ToPopover trait
     */
    protected $popoverView = 'admin.products._popover';

    /**
     * Reference to category
     */
    public function category()
    {
        return $this->referencesOne('Category','category');
    }

    /**
     * Return image URL
     *
     * @return string
     */
    public function imageUrl( $img = 1, $size = 300 )
    {
        if( false )
        {
            return URL::to(Asset::url('uploads/img/products/'.$this->_id.'_'.$img.'_'.$size.'.jpg'));
        }
        else
        {
            return URL::to(Asset::url('assets/img/products/default.png'));
        }
    }

    /**
     * Overwrites the isValid method in order to make sure that the characteristics
     * are valid
     */
    public function isValid()
    {
        if(parent::isValid())
        {
            $result = true;

            foreach ($this->category()->characteristics() as $charac) {

                if(isset($this->details[clean_case($charac->name)]))
                {
                    if (! $charac->validate($this->details[clean_case($charac->name)]))
                    {
                        if(! $this->errors)
                        {
                            $this->errors = new MessageBag;
                        }
                        $this->errors->add($charac->name, "Valor inválido para caracteristica '$charac->name'");
                        $result = false;
                    }
                }
            }

            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
     * Determines if a product is visible or not. This takes a decision
     * assembling the following facts:
     * - state is not 'invalid'
     * - deactivate is not any sort of 'true'
     * - product has an _id
     */
    public function isVisible()
    {
        return
            $this->state != 'invalid' &&
            $this->deactivated == false &&
            $this->lm != false;
    }

    /**
     * Simply set the deactivated attribute to true
     */
    public function deactivate()
    {
        $this->deactivated = true;
    }

    /**
     * Simply unset the deactivated attribute
     */
    public function activate()
    {
        unset($this->deactivated);
    }

    /**
     * Overwrites the save metod to save anyway but to mark the
     * product as invalid
     *
     */
    public function save( $force = false )
    {
        $this->lm = (string)$this->_id;

        if( $this->isValid() )
        {
            unset($this->state);
            return parent::save();
        }
        elseif( $force )
        {
            $this->state = 'invalid';
            return parent::save( true );
        }
        else
        {
            return false;
        }
    }

    /**
     * Overwrites the Mongoloid\Model delete method in order
     * to clean references to the resource
     */
    public function delete()
    {
        // Check if its part of a conjugated product
        $affectedProduct = ConjugatedProduct::first(
            ['conjugated'=>$this->_id]
        );

        if($affectedProduct)
        {
            $this->errors = new MessageBag([
                'Produto compõe um conjugado','Não é possível excluir um '.
                'produto que faz parte de um conjugado. Esse produto faz parte'.
                'do conjugado '.$affectedProduct->_id.'. Remova o produto do'.
                'conjugado antes de exclui-lo.'
            ]);

            return false;
        }
        else
        {
            return parent::delete();
        }
    }

    /**
     * Polymorph into ConjugatedProduct if the conjugated
     * is defined
     *
     * return mixed $instance
     */
    public function polymorph( $instance )
    {
        if( $instance->conjugated != null )
        {
            $conjProduct = new ConjugatedProduct;

            $conjProduct->parseDocument( $instance->attributes );
            return $conjProduct;
        }
        else
        {
            return $instance;
        }
    }
}
