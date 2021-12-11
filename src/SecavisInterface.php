<?php

namespace CeeConnect\Secavis;

use CeeConnect\Secavis\Exception\BadRequestException;
use CeeConnect\Secavis\Exception\ServiceUnavailableException;

interface SecavisInterface
{
    /**
     * @param Request
     * @return Response
     * @throws BadRequestException|ServiceUnavailableException
     */
    public static function execute(Request $request): Response;
}
