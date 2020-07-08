<?php

namespace Transistor\Objects;

class Collector extends Pin
{
    public function setBNegative(bool $bNegative = false): void
    {
        $this->b_negative = $bNegative;
        if ($this->b_negative) {
            parent::setBNegative($bNegative);
            if ($bNegative && $this->getBPositive()) {
                foreach ($this->wires['negative'] AS $wire) {
                    $wire->getPosition()->setY($wire->getPosition()->getY() - 1);
                    $wire->setPower(true);
                }
                $this->wires['negative'][] = new Wire($this->position->getX(), $this->position->getY() - 1, true, true);
                
            }
        }
    }
}
