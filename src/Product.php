<?php

namespace App;

use JsonSerializable;

class Product implements JsonSerializable
{
    private string $title;

    private float $price;

    private string $imageUrl;

    private int $capacityMB;

    private string $colour;

    private string $availabilityText;

    private bool $isAvailable;

    private ?string $shippingText;

    private ?string $shippingDate;

    /**
     * Returns string value of the object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->title." ".$this->colour;
    }

    /**
     * Returns mixed data which can be serialized by json_encode
     *
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @param int $capacityMB
     */
    public function setCapacityMB(int $capacityMB): void
    {
        $this->capacityMB = $capacityMB;
    }

    /**
     * @param string $colour
     */
    public function setColour(string $colour): void
    {
        $this->colour = $colour;
    }


    /**
     * @param string $availabilityText
     */
    public function setAvailabilityText(string $availabilityText): void
    {
        $this->availabilityText = $availabilityText;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * @param string|null $shippingText
     */
    public function setShippingText(?string $shippingText): void
    {
        $this->shippingText = $shippingText;
    }

    /**
     * @param string|null $shippingDate
     */
    public function setShippingDate(?string $shippingDate): void
    {
        $this->shippingDate = $shippingDate;
    }


}
