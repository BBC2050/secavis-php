<?php

require __DIR__ . '../vendor/autoload.php';

use CeeConnect\Secavis\Secavis;
use CeeConnect\Secavis\Request;

$request = new Request();
$request->referenceAvis = 'XXXXXXXXXXXXX';
$request->numeroFiscal = 'XXXXXXXXXXXXX';

$response = Secavis::execute($request);

var_dump($response);

echo $response->html->html;
