<?php

return array(
    'origin_url' => array(
        'product' => 'http://www.leroymerlin.com.br/resources/images/products/product/{lm}_{angle}_{size}.jpg',
        'chave_entrada' => 'http://www.leroymerlin.com.br/resources/images/chaveEntrada/chave-entrada-{lm}.jpg'
    ),
    'destination_url' => 'public/uploads/img/{collection}/{lm}_{angle}_{size}.jpg',
    'proxy' => 'http://10.56.22.70',
    'proxy_port' => '8080',
    'user' => 'central\lalves:leroymerlin1',
    'image' => array(
        'sizes' => array('150', '300', '600'),
        'angles' => array('1', '2', '3')
    )
);
