<?php

namespace DragonFly\Base;

use DragonFly\Manager\WorldManager;
use DragonFly\World\Box;
use DragonFly\World\Vector;
use DragonFly\World\WorldObject;

class Utility
{
    /**
     * Return true if positions are within 1 space of each other.
     *
     * @param Vector $position1
     * @param Vector $position2
     * @return boolean
     */
    public static function positionsIntersect(Vector $position1, Vector $position2): bool
    {
        if (abs($position1->getX() - $position2->getX()) <= 1 && abs($position1->getY() - $position2->getY()) <= 1) {
            return true;
        }
        return false;
    }

    /**
     * Return true if boxes intersect, otherwise false.
     *
     * @param Box $box1
     * @param Box $box2
     * @return boolean
     */
    public static function boxesIntersect(Box $box1, Box $box2): bool
    {
        // BOX 1 UPPER LEFT AND LOWER RIGHT CORNERS
        $b1x1 = $box1->getCorner()->getX();
        $b1x2 = $b1x1 + $box1->getHorizontal();
        $b1y1 = $box1->getCorner()->getY();
        $b1y2 = $b1y1 + $box1->getVertical();

        // BOX 2 UPPER LEFT AND LOWER RIGHT CORNERS
        $b2x1 = $box2->getCorner()->getX();
        $b2x2 = $b2x1 + $box2->getHorizontal();
        $b2y1 = $box2->getCorner()->getY();
        $b2y2 = $b2y1 + $box2->getVertical();

        $xOverlap = ($b2x1 <= $b1x1 && $b1x1 <= $b2x2) || ($b1x1 <= $b2x1 && $b2x1 <= $b1x2);
        $yOverlap = ($b2y1 <= $b1y1 && $b1y1 <= $b2y2) || ($b1y1 <= $b2y1 && $b2y1 <= $b1y2);

        return $xOverlap && $yOverlap;
    }

    /**
     * Undocumented function
     *
     * @param WorldObject $object
     * @return Box
     */
    public static function getWorldBox(WorldObject $object, Vector $where = null): Box
    {
        $tempBox = new Box($object->getBox()->getCorner(), $object->getBox()->getHorizontal(), $object->getBox()->getVertical());
        if (is_null($where)) {
            $where = $object->getPosition()->subtract(new Vector($object->getBox()->getHorizontal() / 2, $object->getBox()->getVertical() / 2));
        }
        $tempBox->setCorner($tempBox->getCorner()->add(new Vector($where->getX(), $where->getY())));
        return $tempBox;
    }

    public static function worldToView(Vector $worldPosition): Vector
    {
        $viewOrigin = WorldManager::getInstance()->getView()->getCorner();
        return new Vector($worldPosition->getX() - $viewOrigin->getX(), $worldPosition->getY() - $viewOrigin->getY());
    }
}
