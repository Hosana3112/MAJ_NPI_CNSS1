<?php

namespace App\Entity;

use App\Repository\PersPhysTraitementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersPhysTraitementRepository::class)]
class PersPhysTraitement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $NumCNSS = null;

    #[ORM\Column(length: 200)]
    private ?string $NomCNSS = null;

    #[ORM\Column(length: 250)]
    private ?string $PrenomCNSS = null;

    #[ORM\Column(length: 100)]
    private ?string $npi = null;

    #[ORM\Column(length: 250)]
    private ?string $NomANIP = null;

    #[ORM\Column(length: 200)]
    private ?string $PrenomANIP = null;

    #[ORM\Column(length: 100)]
    private ?string $statut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE,nullable:true)]
    private ?\DateTime $Date_Naissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Motif = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $Datetraitement = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $datedemande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumCNSS(): ?string
    {
        return $this->NumCNSS;
    }

    public function setNumCNSS(string $NumCNSS): static
    {
        $this->NumCNSS = $NumCNSS;

        return $this;
    }

    public function getNomCNSS(): ?string
    {
        return $this->NomCNSS;
    }

    public function setNomCNSS(string $NomCNSS): static
    {
        $this->NomCNSS = $NomCNSS;

        return $this;
    }

    public function getPrenomCNSS(): ?string
    {
        return $this->PrenomCNSS;
    }

    public function setPrenomCNSS(string $PrenomCNSS): static
    {
        $this->PrenomCNSS = $PrenomCNSS;

        return $this;
    }

    public function getnpi(): ?string
    {
        return $this->npi;
    }

    public function setnpi(string $npi): static
    {
        $this->npi = $npi;

        return $this;
    }

    public function getNomANIP(): ?string
    {
        return $this->NomANIP;
    }

    public function setNomANIP(string $NomANIP): static
    {
        $this->NomANIP = $NomANIP;

        return $this;
    }

    public function getPrenomANIP(): ?string
    {
        return $this->PrenomANIP;
    }

    public function setPrenomANIP(string $PrenomANIP): static
    {
        $this->PrenomANIP = $PrenomANIP;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateNaissance(): ?\DateTime
    {
        return $this->Date_Naissance;
    }

    public function setDateNaissance(? \DateTime $Date_Naissance): static
    {
        $this->Date_Naissance = $Date_Naissance;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->Motif;
    }

    public function setMotif(?string $Motif): static
    {
        $this->Motif = $Motif;

        return $this;
    }

    public function getDatetraitement(): ?\DateTime
    {
        return $this->Datetraitement;
    }

    public function setDatetraitement(?\DateTime $Datetraitement): static
    {
        $this->Datetraitement = $Datetraitement;

        return $this;
    }

    public function getDatedemande(): ?\DateTime
    {
        return $this->datedemande;
    }

    public function setDatedemande(\DateTime $datedemande): static
    {
        $this->datedemande = $datedemande;

        return $this;
    }
}
