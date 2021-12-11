<?php

namespace CeeConnect\Secavis\Api;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use CeeConnect\Secavis\Exception\BadRequestException;
use CeeConnect\Secavis\Exception\ServiceUnavailableException;
use CeeConnect\Secavis\Utils\Parser;

class Secavis
{
    /**
     * URL de base du service SECAVIS
     */
    public const BASE_URL = 'https://cfsmsp.impots.gouv.fr';

    /**
     * @param string
     * @param string
     * 
     * @return null|string HTML
     * 
     * @throws ServiceUnavailableException
     * @throws BadRequestException
     */
    public static function get(string $numeroFiscal, string $referenceAvis): null|string
    {
        /** @var Response */
        $preRespone = Secavis::preRequest();

        if ($preRespone->getStatusCode() !== 200) {
            throw new ServiceUnavailableException($preRespone->getContent());
        }
        $html = $preRespone->getContent();
        $viewState = Parser::parsePreRequest($html);

        if (empty($viewState)) {
            throw new \RuntimeException('Erreur lors de la récupération de la valeur ViewsState.');
        }

        /** @var Response */
        $response = Secavis::request($viewState, $numeroFiscal, $referenceAvis);

        if ($response->getStatusCode() !== 200) {
            throw new ServiceUnavailableException($response->getContent());
        }

        return $response->getContent();
    }

    private static function preRequest(): ResponseInterface
    {
        $client = HttpClient::create();
        return $client->request('GET', self::BASE_URL . '/secavis/');
    }

    private static function request(null|string $viewState, string $numeroFiscal, string $referenceAvis): ResponseInterface
    {
        $formFields = self::mapFormData();
        $formFields['j_id_7:spi'] = $numeroFiscal;
        $formFields['j_id_7:num_facture'] = $referenceAvis;
        $formFields['javax.faces.ViewState'] = $viewState;

        $formData = new FormDataPart($formFields);

        $client = HttpClient::create();

        return $client->request('POST',  self::BASE_URL . '/secavis/faces/commun/index.jsf', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);
    }

    private static function mapFormData(): array
    {
        return [
            'j_id_7:spi' => null,
            'j_id_7:num_facture' => null,
            'j_id_7:j_id_l' => 'Valider',
            'j_id_7_SUBMIT' => '1',
            'javax.faces.ViewState' => null
        ];
    }

}
