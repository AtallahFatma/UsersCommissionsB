<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commission
 *
 * @ORM\Table(name="commission", indexes={
 * @ORM\Index(name="idMerchant", columns={"idMerchant"}),
 * @ORM\Index(name="idUser", columns={"idUser"})})
 * @ORM\Entity
 */
class Commission
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="cashback", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $cashback;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Merchant
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Merchant")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idMerchant", referencedColumnName="id")
     * })
     */
    private $idmerchant;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idUser", referencedColumnName="id")
     * })
     */
    private $iduser;


}

