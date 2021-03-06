<?php

class testCategoryProvider extends testObjectProvider
{
    public static $model = 'Category';

    protected function valid_leaf_category()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000001' ),
            'name' => 'Ferramentas específicas',
            'slug' => 'ferramentas_especificas',
            'type' => 'leaf',
            'parents' => [
                testCategoryProvider::saved('valid_parent_category')->_id
            ],
            'description' => 'Ferramentas com finalidades específicas',
            'characteristics' => [
                testCharacteristicProvider::attributesFor('valid_numeric_characteristic'),
                testCharacteristicProvider::attributesFor('valid_option_characteristic'),
            ]
        ];
    }

    protected function another_valid_leaf_category()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000002' ),
            'name' => 'Ferramentas detalhadas',
            'slug' => 'ferramentas_detalhadas',
            'type' => 'leaf',
            'parents' => [
                testCategoryProvider::saved('valid_parent_category')->_id
            ],
            'description' => 'Ferramentas detalhadas',
            'characteristics' => [
                testCharacteristicProvider::attributesFor('valid_numeric_characteristic'),
                testCharacteristicProvider::attributesFor('valid_decimal_characteristic'),
            ]
        ];
    }

    protected function invalid_leaf_category()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000003' ),
            'name' => '',
            'slug' => '',
            'type' => 'leaf',
            'parents' => [
                testCategoryProvider::saved('valid_parent_category')->_id
            ],
            'description' => 'A chave de entrade sem nome!',
            'characteristics' => [
                testCharacteristicProvider::attributesFor('valid_numeric_characteristic'),
                testCharacteristicProvider::attributesFor('valid_decimal_characteristic'),
            ]
        ];
    }

    protected function valid_parent_category()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000004' ),
            'name' => 'Ferramentas',
            'slug' => 'ferramentas',
            'parents' => [],
            'description' => 'Ferramentas em geral',
        ];
    }

    protected function another_valid_parent_category()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000005' ),
            'name' => 'Equipamentos',
            'slug' => 'equipamentos',
            'parents' => [],
            'description' => 'Equipamentos em geral',
        ];
    }

    protected function valid_department()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000006' ),
            'name' => 'Coisas',
            'slug' => 'coisas',
            'parents' => [],
            'description' => 'Departamento cheio de coisas',
        ];
    }

    protected function hidden_leaf_category()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000007' ),
            'name' => 'Brocas',
            'slug' => 'brocas',
            'type' => 'leaf',
            'parents' => [
                testCategoryProvider::saved('valid_parent_category')->_id,
                testCategoryProvider::saved('another_valid_parent_category')->_id
            ],
            'description' => 'Brocas com finalidades específicas',
            'characteristics' => [
                testCharacteristicProvider::attributesFor('valid_numeric_characteristic'),
                testCharacteristicProvider::attributesFor('valid_option_characteristic'),
            ],
            'hidden' => 1
        ];
    }

    protected function valid_lixeiras_category()
    {
        return [
            '_id' => new MongoId( '4af9f23d8ead0e1d32000008' ),
            'name' => 'Lixeiras',
            'slug' => 'lixeiras',
            'type' => 'leaf',
            'parents' => [],
            'description' => 'Lixeiras e etc',
        ];
    }
}
