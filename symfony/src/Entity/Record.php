<?php

namespace App\Entity;

use App\Repository\RecordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecordRepository::class)
 */
class Record
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $codeZone;

    /**
     * @ORM\Column(type="integer")
     */
    private $skinBioSense;

    /**
     * @ORM\Column(type="float")
     */
    private $measure;

    /**
     * @ORM\ManyToOne(targetEntity=Test::class, inversedBy="records")
     * @ORM\JoinColumn(nullable=false)
     */
    private $test;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeZone(): ?int
    {
        return $this->codeZone;
    }

    public function setCodeZone(int $codeZone): self
    {
        $this->codeZone = $codeZone;

        return $this;
    }

    public function getSkinBioSense(): ?int
    {
        return $this->skinBioSense;
    }

    public function setSkinBioSense(int $skinBioSense): self
    {
        $this->skinBioSense = $skinBioSense;

        return $this;
    }

    public function getMeasure(): ?float
    {
        return $this->measure;
    }

    public function setMeasure(float $measure): self
    {
        $this->measure = $measure;

        return $this;
    }

    public function getTest(): ?Test
    {
        return $this->test;
    }

    public function setTest(?Test $test): self
    {
        $this->test = $test;

        return $this;
    }
}
