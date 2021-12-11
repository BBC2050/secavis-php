<?php

namespace CeeConnect\Secavis;

use CeeConnect\Secavis\Api\Secavis as Api;
use CeeConnect\Secavis\Exception\BadRequestException;
use CeeConnect\Secavis\Model\Declaration;
use CeeConnect\Secavis\Model\Declarant;
use CeeConnect\Secavis\Model\Html;
use CeeConnect\Secavis\Utils\Formater;
use CeeConnect\Secavis\Utils\Parser;

class Secavis implements SecavisInterface
{
    /**
     * @inheritdoc
     */
    public static function execute(Request $request): Response
    {
        if (!preg_match('/^\w{13,14}$/', $request->referenceAvis)) {
            throw new BadRequestException();
        }
        if (!preg_match('/^\d{13,13}$/', $request->numeroFiscal)) {
            throw new BadRequestException();
        }

        $html = Api::get($request->numeroFiscal, $request->referenceAvis);

        $response = new Response();
        $response->declaration = self::declaration($request, $html);
        $response->html = self::html($html);

        return $response;
    }

    private static function declaration(Request $request, string $html): Declaration
    {
        $parseData = Parser::parseRequest($html);

        $declaration = Declaration::fromArray($parseData['declaration'] ?? []);
        $declaration->annee = Declaration::getAnneeFromReferenceAvis($request->referenceAvis);

        $declaration->declarants[] = Declarant::fromArray(
            \array_map(function($item) {
                return $item ? \current($item) : null;
            }, $parseData['declarants'])
        );

        if ($parseData['declarants']['nom'] && \count($parseData['declarants']['nom']) > 1) {
            $declaration->declarants[] = Declarant::fromArray(
                \array_map(function($item) {
                    return $item[1] ?? null;
                }, $parseData['declarants'])
            );
        }

        return $declaration;
    }

    private static function html(string $content): Html
    {
        $html = new Html();
        $html->html = Formater::formatHtml($content);
        $html->lien = Api::BASE_URL;

        return $html;
    }

}
