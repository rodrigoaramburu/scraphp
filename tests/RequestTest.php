<?php

declare(strict_types=1);

use ScraPHP\Request;

test('deve criar uma requição get', function(){

    $request = Request::create(url: 'http://locahost/page1.php');

    expect( $request->url())->toBe('http://locahost/page1.php');
    expect( $request->isGet())->toBeTrue();
});

test('deve criar uma requição explicitamente get', function(){
    
    $request = Request::create(url: 'http://locahost/page1.php')->get();
    
    expect( $request->url())->toBe('http://locahost/page1.php');
    expect( $request->isGet())->toBeTrue();
});

test('deve criar uma requição post', function(){
    
    $request = Request::create(url: 'http://locahost/page1.php')->post();
    
    expect( $request->url())->toBe('http://locahost/page1.php');
    expect( $request->isPost())->toBeTrue();
});


test('deve permiter adicionar o body', function(){
    $data = [
        'campo1' => 'valor1',
        'campo2' => 'valor2',
    ];
    $request = Request::create(url: 'http://locahost/page1.php')->post()->body($data);

    expect($request->getBody())->toBe($data);
});


test('deve contar erros',function(){

    $request = Request::create(url: 'http://locahost/page1.php');

    $request->failCountIncrement();
    $request->failCountIncrement();

    expect($request->failCount())->toBe(2);
});