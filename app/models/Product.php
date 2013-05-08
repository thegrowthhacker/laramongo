<?php

use Illuminate\Support\MessageBag;
use Laramongo\SearchEngine\Searchable;

class Product extends BaseModel implements Searchable {
    use Traits\ToPopover;
    use Traits\Searchable;

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
        '_id'       => 'required',
        'name'      => 'required',
        'category'  => 'required',
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
        $imageFile = '';

        if ($this->image) {
            while(! isset($this->image[$img]))
                $img--;

            if(isset($this->image[$img]))
            {
                $imageFile = $this->image[$img];
            }
        }

        if( $imageFile )
        {
            return URL::to(Asset::url('uploads/img/products/' . $imageFile));
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
    public function isValid($force = false)
    {
        if(parent::isValid($force))
        {
            $result = true;

            $category = $this->category();

            if( $category )
            {
                foreach ($category->characteristics() as $charac) {

                    if(isset($this->characteristics[clean_case($charac->name)]))
                    {
                        if (! $charac->validate($this->characteristics[clean_case($charac->name)]))
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

        if( $this->isValid( $force ) )
        {
            $this->searchEngineIndex();
            unset($this->state);
            $result = parent::save( $force );
            $this->grabImages();
            return $result;
        }
        elseif( $force )
        {
            $this->state = 'invalid';
            $result = parent::save( $force );
            $this->grabImages();
            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
     * Get the price of the product by region. If the region parameter
     * is null, it will be filled with the 'region' key in the user's
     * session.
     * It returns the price array containing the 'base_price' and the
     * 'promotional_price'.
     * 
     * @param  string $region Region slug
     * @return array  Prices array (base_price and promotional_price)
     */
    public function getPrice( $region = null )
    {
        $result = array();

        if(! $region)
            $region = Session::get('region');

        $to_price = array_get(array_get($this->price,$region),'to_price', 0);
        $from_price = array_get(array_get($this->price,$region),'from_price', 0);

        $result['base_price'] = $from_price;
        $result['promotional_price'] = $to_price;

        return $result;
    }

    /**
     * Overwrites the Mongolid\Model delete method in order
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

    /**
     * Grab images from the original website
     * @return array Images array
     */
    public function grabImages()
    {
        if(! isset($this->image )){
            $images = ImageGrabber::grab($this);
            $this->image = $images;
            $this->save( true );

            return $images;
        }
    }
}
