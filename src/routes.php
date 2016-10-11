<?php
// Routes

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$client = new \Memcached();
$client->addServer('localhost', 11211);

$cache = new \MatthiasMullie\Scrapbook\Adapters\Memcached($client);

$app->get('/', function (Request $request, Response $response, $args) use ($cache) {
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/prices', function (Request $request, Response $response) use ($cache) {
    $data = [];

    if ($cache->get('prices')) {
        $data = $cache->get('prices');
    }

    return $this->renderer->render($response, 'prices.phtml', $data);
});

$app->get('/recount', function (Request $request, Response $response) use ($cache) {
    $data = [];

    if ($cache->get('recount')) {
        $data = $cache->get('recount');
    }

    return $this->renderer->render($response, 'recount.phtml', $data);
});

$app->get('/remains', function (Request $request, Response $response) use ($cache) {
    $data = [];

    if ($cache->get('remains')) {
        $data = $cache->get('remains');
    }

    return $this->renderer->render($response, 'remains.phtml', $data);
});

$app->post('/update/prices', function (Request $request, Response $response) use ($app, $cache) {
    $data = $request->getParsedBody();

    if (!empty($data))
    {
        $change = [];
        $prices = [];
        $discount = 0;

        if (isset($data['discount']))
        {
            $discount = $data['discount'];
        }

        if (isset($data['change']))
        {
            $change = $data['change'];
        }

        if ($cache->get('prices')) {
            $prices = $cache->get('prices');
        }

        $prices = combine($prices, $change);
        
        unset($_SESSION['update']);

        foreach ($prices as $product)
        {
            $status = 0;
            $result = Q(makeQuery($product['name']))->row();
            $id_product = 0;

            if (!empty($result))
            {
                $status = 1;
                $category = 'sportivnoe-pitanie';

                $id_product = $result['id_product'];
                $discount_price = updateProduct($result, $product, $discount);

                $link = 'http://www.rusport23.ru/' . $category . '/' . $id_product . '-' . $result['link_rewrite'] . '.html';
            }

            $_SESSION['update']['prices'][$product['number']] = [
                'id'                =>  $id_product,
                'status'            =>  $status,
                'link'              =>  $link,
                'discount_price'    =>  $discount_price
            ];
        }

        // exit(__($_SESSION['update']));

        return $response->withStatus(302)->withHeader('Location', '/prices');
    }
});

$app->post('/update/recount', function (Request $request, Response $response) use ($app, $cache) {
    $data = $request->getParsedBody();
    $files = $request->getUploadedFiles();

    // Продукты тут
    // ps_product - name id_product price (2376.000000) active
    // ps_product_lang - name id_product

    // ps_layered_price_index - id_product price_min price_max

    __($data);
    exit(__($files));
});

$app->post('/update/remains', function (Request $request, Response $response) use ($app, $cache) {
    $data = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    __($data);
    exit(__($files));
});

$app->post('/api/remains', function (Request $request, Response $response) use ($app, $cache) {
    $data = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    
    $dir = dirname(__DIR__) . '/runtime';

    if (empty($files['file'])) {
        throw new Exception('Expected a newfile');
    }

    $newfile = $files['file'];
    
    if ($newfile->getError() === UPLOAD_ERR_OK) {
        $uploadFileName = $newfile->getClientFilename();
        $newfile->moveTo($dir.'/'.$uploadFileName);

        $reader = new Spreadsheet_Excel_Reader();
        $reader->setOutputEncoding('UTF-8');
        $reader->read($dir.'/'.$uploadFileName);
        
        $sheets = $reader->sheets;
        
        $data = [];

        $last_key = end(array_keys($sheets[0]['cells']));

        foreach ($sheets[0]['cells'] as $key => $sheet) {
            if ($key > 11 && $key !== $last_key)
            {
                if (isset($sheet[2]) && !strstr($sheet[2], 'Сертификат №'))
                {
                    $data[] = prepare($sheet, [
                        2 => 'name',
                        3 => 'opening',
                        4 => 'parish',
                        5 => 'consumption', 
                        6 => 'balance' 
                    ]);
                }

            }
        }

        $cache->set('remains', calculate($data));

        return $response->withStatus(302)->withHeader('Location', '/remains');
    }
});

$app->post('/api/prices', function (Request $request, Response $response) use ($app, $cache) {
    $data = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    
    $dir = dirname(__DIR__) . '/runtime';

    if (empty($files['file'])) {
        throw new Exception('Expected a newfile');
    }
    
    $newfile = $files['file'];
    
    if ($newfile->getError() === UPLOAD_ERR_OK) {
        $uploadFileName = $newfile->getClientFilename();
        $newfile->moveTo($dir.'/'.$uploadFileName);

        $reader = new Spreadsheet_Excel_Reader();
        $reader->setOutputEncoding('UTF-8');
        $reader->read($dir.'/'.$uploadFileName);
        
        $sheets = $reader->sheets;

        $data = [];

        foreach ($sheets[0]['cells'] as $sheet) {
            if (array_keys_exists([2, 4, 21, 24, 26], array_keys($sheet))) {
                
                $data[] = prepare($sheet, [
                    4   => 'name',
                    21  => 'price' 
                ]);

            }
        }

        $cache->set('prices', calculate($data));

        return $response->withStatus(302)->withHeader('Location', '/prices');

        
        exit(__($data));
    }

    
    exit(__($files));
});

$app->post('/api/recount', function (Request $request, Response $response) use ($app, $cache) {
    $data = $request->getParsedBody();
    $files = $request->getUploadedFiles();
    
    $dir = dirname(__DIR__) . '/runtime';

    if (empty($files['file'])) {
        throw new Exception('Expected a newfile');
    }
    
    $newfile = $files['file'];
    
    if ($newfile->getError() === UPLOAD_ERR_OK) {
        $uploadFileName = $newfile->getClientFilename();
        $newfile->moveTo($dir.'/'.$uploadFileName);

        $reader = new Spreadsheet_Excel_Reader();
        $reader->setOutputEncoding('UTF-8');
        $reader->read($dir.'/'.$uploadFileName);
        
        $sheets = $reader->sheets;

        $data = [];

        foreach ($sheets[0]['cells'] as $sheet) {
            if (array_keys_exists([2, 7, 9, 13, 16, 24, 25], array_keys($sheet)) && !array_key_exists(8, array_keys($sheet))) {
                
                $data[] = prepare($sheet, [
                    2 => 'name',
                    7 => 'code',
                    16 => 'count',
                    20 => 'weight',
                    24 => 'price',
                    25 => 'total'
                ]);

            }
        }
        
        $cache->set('recount', calculate($data));

        // exit(__($data));
        // exit(__($sheets));

        return $response->withStatus(302)->withHeader('Location', '/recount');

        
        exit(__($data));
    }

    
    exit(__($files));
});

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});