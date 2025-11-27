<?php

namespace App\Entity;

use App\Repository\PersPhysRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersPhysRepository::class)]
class PersPhys
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $mat_pers = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_pers = null;

    #[ORM\Column(length: 255)]
    private ?string $pnom_pers = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateNaiss = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $npi = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numCNSS = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatPers(): ?string
    {
        return $this->mat_pers;
    }

    public function setMatPers(string $mat_pers): static
    {
        $this->mat_pers = $mat_pers;

        return $this;
    }

    public function getNomPers(): ?string
    {
        return $this->nom_pers;
    }

    public function setNomPers(string $nom_pers): static
    {
        $this->nom_pers = $nom_pers;

        return $this;
    }

    public function getPnomPers(): ?string
    {
        return $this->pnom_pers;
    }

    public function setPnomPers(string $pnom_pers): static
    {
        $this->pnom_pers = $pnom_pers;

        return $this;
    }

    public function getDateNaiss(): ?\DateTime
    {
        return $this->dateNaiss;
    }

    public function setDateNaiss(\DateTime $dateNaiss): static
    {
        $this->dateNaiss = $dateNaiss;

        return $this;
    }

    public function getNpi(): ?string
    {
        return $this->npi;
    }

    public function setNpi(?string $npi): static
    {
        $this->npi = $npi;

        return $this;
    }

    public function getNumCNSS(): ?string
    {
        return $this->numCNSS;
    }

    public function setNumCNSS(?string $numCNSS): static
    {
        $this->numCNSS = $numCNSS;

        return $this;
    }

}
