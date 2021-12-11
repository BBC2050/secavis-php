<?php

namespace CeeConnect\Secavis\Utils;

class Formater
{
    public const BASE_URL = 'https://cfsmsp.impots.gouv.fr';

    /**
     * @param string HTML de la page
     * @return string HTML modifiée de la page
     */
    public static function formatHtml(string $html): string
    {
        return \str_replace('/secavis', self::BASE_URL . '/secavis', $html);
    }

}
