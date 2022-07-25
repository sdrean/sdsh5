<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PurchaseTypeRepository")
 */
class PurchaseType
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $PurchaseName;


    /**
     * @ORM\Column(type="string", length=10)
     */
    private $StatColor;

    /**
     *
     * @ORM\Column(type="integer")
     */
    private $PurchaseOrder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPurchaseName(): ?string
    {
        return $this->PurchaseName;
    }

    public function setPurchaseName(string $PurchaseName): self
    {
        $this->PurchaseName = $PurchaseName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatColor()
    {
        return $this->StatColor;
    }

    public function setStatColor(string $StatColor): self
    {
        $this->StatColor = $StatColor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurchaseOrder()
    {
        return $this->PurchaseOrder;
    }
}
