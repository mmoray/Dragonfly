<?php

namespace DragonFly\Manager;
require_once __DIR__ . '/../../vendor/autoload.php';

use DragonFly\Base\Singleton;
use DragonFly\Base\Utility;
use DragonFly\World\Color;
use DragonFly\World\Vector;
use Exception;

class DisplayManager extends Manager
{
    const CHARACTER_EMPTY = ' ';
    const WINDOW_HORIZONTAL_CHAR_DEFAULT = 100;
    const WINDOW_HORIZONTAL_PIXEL_DEFAULT = 1000;
    const WINDOW_VERTICAL_CHAR_DEFAULT = 30;
    const WINDOW_VERTICAL_PIXEL_DEFAULT = 1000;
    const WINDOW_BACKGROUND_COLOR_DEFAULT = Color::BACKGROUND[Color::BLACK];
    const WINDOW_FOREGROUND_COLOR_DEFAULT = Color::FOREGROUND[Color::WHITE];

    /**
     * Default color.
     *
     * @var Color
     */
    private $defaultColor;

    /**
     * Horizontal spaces in the window.
     *
     * @var int
     */
    private $horizontalChars;

    /**
     * Horizontal pixels in the window.
     *
     * @var int
     */
    private $horizontalPixels;

    /**
     * The single instance of the class.
     *
     * @var Singleton
     */
    protected static $instance;

    /**
     * Multidimentional array representation of screen.
     *
     * @var array
     */
    private $screen;

    /**
     * Vertical spaces in the window.
     *
     * @var int
     */
    private $verticalChars;

    /**
     * Vertical pixels in the window.
     *
     * @var int
     */
    private $verticalPixels;

    /**
     * Instantiate class and properties.
     */
    protected function __construct()
    {
        parent::__construct();
        $this->defaultColor = new Color(self::WINDOW_BACKGROUND_COLOR_DEFAULT, self::WINDOW_FOREGROUND_COLOR_DEFAULT);
        $this->horizontalChars = self::WINDOW_HORIZONTAL_CHAR_DEFAULT;
        $this->horizontalPixels = self::WINDOW_HORIZONTAL_PIXEL_DEFAULT;
        $this->setType(self::TYPE_DISPLAY);
        $this->verticalChars = self::WINDOW_VERTICAL_CHAR_DEFAULT;
        $this->verticalPixels = self::WINDOW_VERTICAL_PIXEL_DEFAULT;
        $this->screen = [];
        for($i = 0; $i < $this->getVertical(); $i++) {
            $this->screen[$i] = [];
        }
        $this->clearScreen();
    }

    /**
     * Destroy class and properties.
     */
    public function __destruct()
    {
        unset($this->defaultColor);
        for ($i = 0; $i < $this->getVertical(); $i++) {
            for ($j = 0; $j < $this->getHorizontal(); $j++) {
                unset($this->screen[$i][$j]);
            }
            unset($this->screen[$i]);
        }
        unset($this->horizontalChars);
        unset($this->horizontalPixels);
        unset($this->verticalChars);
        unset($this->verticalPixels);
        unset($this->screen);
        parent::__destruct();
    }

    /**
     * Convert the DisplayManager into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $display = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $display .= "\"defaultColor\":{$this->defaultColor},";
        $display .= "\"horizontalChars\":{$this->getHorizontal()},";
        $display .= "\"horizontalPixels\":{$this->getHorizontalPixels()},";
        $display .= "\"screen\":[";
        for ($i = 0; $i < $this->getVertical(); $i++) {
            $display .= "[";
            for ($j = 0; $j < $this->getHorizontal(); $j++) {
                $display .= "\"{$this->screen[$i][$j]}\"";
                if ($j < $this->getHorizontal() - 1) {
                    $display .= ",";
                }
            }
            if ($i < $this->getVertical() - 1) {
                $display .= ",";
            }
        }
        $display .= "],";
        $display .= "\"verticalChars\":{$this->getVertical()},";
        $display .= "\"verticalPixels\":{$this->getVerticalPixels()},";
        $display .= "}";
        return $display;
    }

    /**
     * Clear the screen.
     *
     * @return void
     */
    public function clearScreen(): void
    {
        for ($i = 0; $i < $this->getVertical(); $i++) {
            for ($j = 0; $j < $this->getHorizontal(); $j++) {
                $this->screen[$i][$j] = $this->defaultColor->applyColor(self::CHARACTER_EMPTY);
            }
        }
    }

    /**
     * Draw a character at window location (x, y) with color.
     *
     * @param Vector $worldPosition
     * @param string $char
     * @param Color $color
     * @throws Exception Horizontal coordinate is out of bounds.
     * @throws Exception Vertical coordinate is out of bounds.
     * @return void
     */
    public function drawChar(Vector $worldPosition, string $char, Color $color = null): void
    {
        /*
        if ($worldPosition->getX() < 0 || $worldPosition->getX() >= $this->getHorizontal()) {
            throw new Exception(sprintf('Characeter horizontal coordinate is out of bounds: {character: %s, x-position: %s}', $char, $worldPosition->getX()));
        }
        if ($worldPosition->getY() < 0 || $worldPosition->getY() >= $this->getVertical()) {
            throw new Exception(sprintf('Characeter vertical coordinate is out of bounds: {character: %s, y-position: %s}', $char, $worldPosition->getY()));
        }
        */
        $screenPosition = Utility::worldToView($worldPosition);
        if ($screenPosition->getX() >= 0 && $screenPosition->getX() < $this->horizontalChars && $screenPosition->getY() >= 0 && $screenPosition->getY() < $this->verticalChars) {
            $this->screen[$screenPosition->getY()][$screenPosition->getX()] = is_null($color) ? $this->defaultColor->applyColor($char) : $color->applyColor($char);
        }
    }

    /**
     * Return windows horizontal maximum (in characters).
     *
     * @return integer
     */
    public function getHorizontal(): int
    {
        return $this->horizontalChars;
    }

    /**
     * Return windows horizontal maximum (in pixels).
     *
     * @return integer
     */
    public function getHorizontalPixels(): int
    {
        return $this->horizontalPixels;
    }

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    public static function getInstance(): Singleton
    {
        if (!(self::$instance instanceof DisplayManager)) {
            self::$instance = new DisplayManager;
        }
        return self::$instance;
    }

    /**
     * Return windows vertical maximum (in characters).
     *
     * @return integer
     */
    public function getVertical(): int
    {
        return $this->verticalChars;
    }

    /**
     * Return windows vertical maximum (in pixels).
     *
     * @return integer
     */
    public function getVerticalPixels(): int
    {
        return $this->verticalPixels;
    }

    /**
     * Render current screen buffer.
     *
     * @return void
     */
    public function render(): void
    {
        $lines = [];
        for($i = 0; $i < $this->verticalChars; $i++) {
            $lines[] = implode('', $this->screen[$i]);
        }
        system('clear');
        echo implode(PHP_EOL, $lines);
    }
    
    /**
     * Shutdown DisplayManager.
     *
     * @return void
     */
    public function shutDown(): void
    {
        parent::shutDown();
        system('clear');
    }

    /**
     * Clear the console, ready for text based display.
     *
     * @return void
     */
    public function startUp(): void
    {
        parent::startUp();
        system('clear');
        $this->render();
    }
}
