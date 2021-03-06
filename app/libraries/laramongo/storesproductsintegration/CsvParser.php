<?php namespace Laramongo\StoresProductsIntegration;

use Keboola\Csv\CsvFile;
use Log;

class CsvParser {

    /**
     * Saves a $line into the database using the model StoreProduct.
     * 
     * @param  array $line An array containing the column name as the key of each value.
     * @return boolean     Success of failure.
     */
    public function parseLine($line)
    {
        $line = array_change_key_case($line, CASE_LOWER);
        $storeProduct = StoreProduct::first($line['lm']);

        if(!$storeProduct)
            $storeProduct = new StoreProduct;

        $storeProduct->_id = $line['lm'];
        $storeProduct->unit = strtolower(array_get($line,'unidade'));
        $storeProduct->pack = (float)strtolower(array_get($line,'embalagem'));

        // Grab and edit the stores array
        $stores = $storeProduct->stores;

        $storeSlug = $this->getStoreNameById(array_get($line,'cod_filial'));
        $stores[$storeSlug] = [
            'top' => (int)array_get($line,'top'),
            'promotional_price' => (float)array_get($line,'prc_promocional'),
            'background_section' => (float)array_get($line,'prc_fnd_secao'),
            'recommended_retail_price' => (float)array_get($line,'prc_aconselhado')
        ];

        // Set the stores array to the new values
        $storeProduct->stores = $stores;

        return $storeProduct->save();
    }

    public function parseFile($filename, $delimiter = ';', $skip = 0)
    {
        $filename = app_path().$filename;
        $keboola = new CsvFile( $filename, $delimiter );

        \Log::info('CsvParser: file '.$filename.' will be parsed...');

        $headers = array();
        $linesProcessed = 0;

        foreach ($keboola as $line) {

            // DON'T REMOVE THIS SLEEP
            // This will make sure that the file parsing will not eat 100% of the CPU
            //usleep(380);

            // Set the headers
            if( empty($headers) )
            {
                $headers = $line;
                continue;
            }

            if($skip)
            {
                $skip--;
                continue;
            }

            foreach ($line as $key => $value) {
                if(is_numeric($value))
                    $line[$key] = (float)$value;
            }

            $line = array_combine($headers, $line);
            $result = $this->parseLine($line);

            if($result != true)
            {
                Log::error('CsvParser::parseFile() - Error when parsing the following line: '.json_encode($line));
            }

            $linesProcessed++;
            if($linesProcessed % 5000 == 0)
            {
                Log::info('CsvParser: '.$linesProcessed.' lines parsed...');   
            }
        }

        \Log::info('CsvParser: parsing finished. '.$filename.' .');

        return true;
    }

    /**
     * Gets the slug of a store by it's ID
     * 
     * @param  integer $store_id The id of the store
     * @return string            Store slug
     */
    public function getStoreNameById( $store_id )
    {
        $stores = [
            1 => 'interlagos',
            2 => 'rib_preto',
            3 => 'campinas',
            4 => 'contagem',
            5 => 'raposo_tav',
            7 => 'rio_norte',
            8 => 'tiete',
            9 => 'curitiba',
            10 => 's_caetano',
            11 => 'rio_barra',
            12 => 'morumbi',
            13 => 'brasilia',
            15 => 'sjcampos',
            16 => 'rjafet',
            17 => 'bangu',
            18 => 'goiana',
            19 => 'poa',
            20 => 'bh_sul',
            21 => 'niteroi',
            22 => 'anhanguera',
            23 => 'taguatinga',
            24 => 'centernort',
            26 => 'jacarepagu',
            27 => 'sorocaba',
            28 => 'brasilia_n',
            29 => 'uberlandia',
            32 => 's_leopoldo',
            33 => 'londrina',
            34 => 'sj_r_preto',
            35 => 'curitibatu',
            36 => 'bh_norte',
        ];

        return array_get($stores, $store_id);
    }
}
