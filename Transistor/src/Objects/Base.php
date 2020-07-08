<?php

namespace Transistor\Objects;

class Base extends Pin
{
    public function setBNegative(bool $bNegative = false): void
    {
        parent::setBNegative($bNegative);
        if ($this->getBNegative()) {
            $this->setBPositive();
        }
    }

    public function setBPositive(bool $bPositive = false): void
    {
        parent::setBPositive($bPositive);
        if ($this->getBPositive()) {
            $this->setBNegative();
        }
    }
}
