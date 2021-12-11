<?php

namespace CeeConnect\Secavis\Model;

class Declaration
{
    public string|null $annee = null;
    public \DateTimeInterface|null $dateRecouvrement = null;
    public \DateTimeInterface|null $dateEtablissement = null;
    public float|int|null $parts = null;
    public string|null $situationFamille = null;
    public int|null $personnesCharge = null;
    public float|null $revenuBrut = null;
    public float|null $revenuImposable = null;
    public float|null $montantImpotBrut = null;
    public float|null $montantImpot = null;
    public float|null $revenusFiscalReference = null;
    public string|null $adresse = null;
    public string|null $codePostal = null;
    public string|null $commune = null;

    /** @var array|Declarant[] */
    public array $declarants = [];

    public static function getAnneeFromReferenceAvis(string $referenceAvis): string
    {
        return (string) \substr((new \DateTime())->format('Y'), 0, 2) . \substr($referenceAvis, 0, 2);
    }

    public static function fromArray(array $data): self
    {
        $declaration = new Self();
        $declaration->annee = $data['annee'] ?? null;
        $declaration->dateRecouvrement = $data['dateRecouvrement'] ?? null;
        $declaration->dateEtablissement = $data['dateEtablissement'] ?? null;
        $declaration->parts = $data['parts'] ?? null;
        $declaration->situationFamille = $data['situationFamille'] ?? null;
        $declaration->personnesCharge = $data['personnesCharge'] ?? null;
        $declaration->revenuBrut = $data['revenuBrut'] ?? null;
        $declaration->revenuImposable = $data['revenuImposable'] ?? null;
        $declaration->montantImpotBrut = $data['montantImpotBrut'] ?? null;
        $declaration->montantImpot = $data['montantImpot'] ?? null;
        $declaration->revenusFiscalReference = $data['revenusFiscalReference'] ?? null;
        $declaration->adresse = $data['adresse'] ?? null;
        $declaration->codePostal = $data['codePostal'] ?? null;
        $declaration->commune = $data['commune'] ?? null;

        return $declaration;
    }

}
