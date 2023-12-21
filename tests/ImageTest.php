<?php


declare(strict_types=1);

use ScraPHP\Image;

test('instanciate Image', function () {

    $image = new Image(
        rawUri: 'https://example.com/imagem.jpg',
        baseUri: 'https://example.com',
        alt: 'Imagem de Exemplo',
        width: 200,
        height: 150
    );

    expect($image)
        ->source()->toBe('https://example.com/imagem.jpg')
        ->rawUri()->toBe('https://example.com/imagem.jpg')
        ->alt()->toBe('Imagem de Exemplo')
        ->width()->toBe(200)
        ->height()->toBe(150);

});


test('get image uri without domain', function () {

    $image = new Image(
        rawUri: '/imgs/imagem.jpg',
        baseUri: 'https://example.com',
        alt: 'Imagem de Exemplo'
    );

    expect($image->source())->toBe('https://example.com/imgs/imagem.jpg');

});


test('get uri with a relative uri', function () {
    $image = new Image(
        rawUri: 'imagem.jpg',
        baseUri: 'https://example.com/category/posts/index.html',
        alt: 'Imagem de Exemplo'
    );

    expect($image->source())->toBe('https://example.com/category/posts/imagem.jpg');

});
