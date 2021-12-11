<?php

namespace CeeConnect\Secavis\Utils;

use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    private const DECLARATION_MAPPING = [
        [
            'key' => 'Adresse déclarée au 1',
            'property' => 'adresse',
            'callback' => 'createStringFromString'
        ],
        [
            'key' => null,
            'property' => 'codePostal',
            'callback' => 'createCodePostalFromString'
        ],
        [
            'key' => null,
            'property' => 'commune',
            'callback' => 'createCommuneFromString'
        ],
        [
            'key' => 'Date de mise en recouvrement de l\'avis d\'impôt',
            'property' => 'dateRecouvrement',
            'callback' => 'createDateFromString'
        ],
        [
            'key' => 'Date d\'établissement',
            'property' => 'dateEtablissement',
            'callback' => 'createDateFromString'
        ],
        [
            'key' => 'Nombre de part(s)',
            'property' => 'parts',
            'callback' => 'createNumberFromString'
        ],
        [
            'key' => 'Situation de famille',
            'property' => 'situationFamille',
            'callback' => 'createStringFromString'
        ],
        [
            'key' => 'Nombre de personne(s) à charge',
            'property' => 'personnesCharge',
            'callback' => 'createNumberFromString'
        ],
        [
            'key' => 'Revenu brut global',
            'property' => 'revenuBrut',
            'callback' => 'createNumberFromString'
        ],
        [
            'key' => 'Revenu imposable',
            'property' => 'revenuImposable',
            'callback' => 'createNumberFromString'
        ],
        [
            'key' => 'Impôt sur le revenu net avant corrections',
            'property' => 'montantImpotBrut',
            'callback' => 'createNumberFromString'
        ],
        [
            'key' => 'Montant de l\'impôt',
            'property' => 'montantImpot',
            'callback' => 'createNumberFromString'
        ],
        [
            'key' => 'Revenu fiscal de référence',
            'property' => 'revenusFiscalReference',
            'callback' => 'createNumberFromString'
        ]
    ];

    private const DECLARANT_MAPPING = [
        [
            'key' => 'Nom',
            'property' => 'nom',
            'callback' => 'createCapitalizeStringFromString'
        ],
        [
            'key' => 'Nom de naissance',
            'property' => 'nomNaissance',
            'callback' => 'createCapitalizeStringFromString'
        ],
        [
            'key' => 'Prénom(s)',
            'property' => 'prenom',
            'callback' => 'createCapitalizeStringFromString'
        ],
        [
            'key' => 'Date de naissance',
            'property' => 'dateNaissance',
            'callback' => 'createDateFromString'
        ]
    ];

    /**
     * @param string HTML
     * @return string|null ViewState
     */
    public static function parsePreRequest(string $html): ?string
    {
        $crawler = new Crawler($html);
        $nodes = $crawler->filter('input[name="javax.faces.ViewState"]');

        if ($nodes->count() > 0) {
            return $nodes->first()->attr('value');
        }
        return null;
    }

    /**
     * @param string HTML
     * @return array|null data
     */
    public static function parseRequest(string $html): ?array
    {
        $declaration = self::mapDeclaration();
        $declarants = self::mapDeclarant();

        $crawler = new Crawler(\str_replace(["\t", "\n"], '', $html));
        $rows = $crawler->filter('#principal table > tbody tr');

        $previousPropertyName = null;
        $propertyKey = null;
        $propertyName = null;

        foreach ($rows as $row) {
            /** @var \DOMElement  */
            $row = $row;

            // Liste de teoutes les cellules du tableau
            $cells = $row->getElementsByTagName('td');

            // Parcours toutes les cellules du tableau
            for ($i = 0; $i < $cells->count(); $i++) {
                /** @var \DOMElement  */
                $cell = $cells->item($i);

                $content = $cell->childNodes->count() === 0 ? $cell->textContent : $cell->firstChild->textContent;

                if ($i === 0) {
                    $previousPropertyName = $propertyName;
                    $propertyName = null;
                    $propertyKey = null;

                    if (empty($content)) {
                        continue;
                    }
                    if (\in_array($content, self::reduceMappingByKey(self::DECLARATION_MAPPING))) {
                        $propertyKey = \array_search($content, self::reduceMappingByKey(self::DECLARATION_MAPPING));
                        $propertyName = self::DECLARATION_MAPPING[$propertyKey]['property'];
                        
                        continue;
                    }
                    if (\in_array($content, self::reduceMappingByKey(self::DECLARANT_MAPPING))) {
                        $propertyKey = \array_search($content, self::reduceMappingByKey(self::DECLARANT_MAPPING));
                        $propertyName = self::DECLARANT_MAPPING[$propertyKey]['property'];
                        
                        continue;
                    }
                }

                if (empty($content)) {
                    continue;
                }

                if (\in_array($propertyName, self::reduceMappingByProperty(self::DECLARANT_MAPPING))) {
                    $mapping = self::DECLARANT_MAPPING[$propertyKey];

                    $callback = $mapping['callback'];
                    $declarants[$mapping['property']][] = self::$callback($content);

                    continue;
                }
                if (\in_array($propertyName, self::reduceMappingByProperty(self::DECLARATION_MAPPING))) {
                    $mapping = self::DECLARATION_MAPPING[$propertyKey];

                    $callback = $mapping['callback'];
                    $declaration[$mapping['property']] = self::$callback($content);

                    continue;
                }
                if ($previousPropertyName === 'adresse') {
                    // Extraction du code postal
                    $mappingKey = \array_search('codePostal', self::reduceMappingByProperty(self::DECLARATION_MAPPING));
                    $mapping = self::DECLARATION_MAPPING[$mappingKey];

                    $callback = $mapping['callback'];
                    $declaration[$mapping['property']] = self::$callback($content);

                    // Extraction de la commune
                    $mappingKey = \array_search('commune', self::reduceMappingByProperty(self::DECLARATION_MAPPING));
                    $mapping = self::DECLARATION_MAPPING[$mappingKey];

                    $callback = $mapping['callback'];
                    $declaration[$mapping['property']] = self::$callback($content);

                    continue;
                }
            }
        }    
        return [ 'declaration' => $declaration, 'declarants' => $declarants ];
    }

    private static function reduceMappingByKey(array $mapping): array
    {
        return \array_map(function($item){
            return $item['key'];
        }, $mapping);
    }

    private static function reduceMappingByProperty(array $mapping): array
    {
        return \array_map(function($item){
            return $item['property'];
        }, $mapping);
    }

    private static function mapDeclaration(): array
    {
        return \array_map(function($item) {
            return null;
        }, \array_flip(self::reduceMappingByProperty(self::DECLARATION_MAPPING)));
    }

    private static function mapDeclarant(): array
    {
        return \array_map(function($item) {
            return [];
        }, \array_flip(self::reduceMappingByProperty(self::DECLARANT_MAPPING)));
    }

    private static function createCodePostalFromString(?string $string): ?string
    {
        return empty($string) ? null : \substr(\trim($string), 0, 5);
    }

    private static function createCommuneFromString(?string $string): ?string
    {
        return empty($string) ? null : \substr(\trim($string), 6);
    }

    private static function createStringFromString(?string $string): ?string
    {
        return empty($string) ? null : \trim($string);
    }

    private static function createCapitalizeStringFromString(?string $string): ?string
    {
        return empty($string) ? null : \trim(\ucwords(\strtolower($string), " \t\r\n\f\v\-'"));
    }

    private static function createDateFromString(?string $string): ?\DateTimeInterface
    {
        return empty($string) ? null : \DateTime::createFromFormat('d/m/Y', \trim($string));
    }

    private static function createNumberFromString(?string $string): ?float
    {
        if ($string === 'Non imposable') {
            return (float) 0;
        }
        return empty($string)
            ? null
            : (float) \str_replace(',', '.', \preg_replace('/[^0-9\,\.]/', '', $string));
    }

}
