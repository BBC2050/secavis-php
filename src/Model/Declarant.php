<?php

namespace CeeConnect\Secavis\Model;

class Declarant
{
    public string|null $nom = null;
    public string|null $nomNaissance = null;
    public string|null $prenom = null;
    public \DateTimeInterface|null $dateNaissance = null;

    public static function fromArray(array $data): self
    {
        $declarant = new Self();
        $declarant->nom = $data['nom'] ?? null;
        $declarant->nomNaissance = $data['nomNaissance'] ?? null;
        $declarant->prenom = $data['prenom'] ?? null;
        $declarant->dateNaissance = $data['dateNaissance'] ?? null;

        return $declarant;
    }

}
