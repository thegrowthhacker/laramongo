<?php

use Illuminate\Support\MessageBag;

class Product extends BaseModel {

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
        if( file_exists(app_path().'/../public/assets/img/products/'.$this->id.'_'.$img.'_'.$size.'.jpg') )
        {
            return URL::to('assets/img/products/'.$this->id.'_'.$img.'_'.$size.'.jpg');
        }
        else
        {
            return URL::to('assets/img/products/default.png');
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

}
