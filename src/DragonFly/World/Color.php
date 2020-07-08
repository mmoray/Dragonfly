<?php

namespace DragonFly\World;

use DragonFly\Manager\DisplayManager;
use Exception;

class Color
{
    const BLACK = 'black';
    const BLUE = 'blue';
    const BROWN = 'brown';
    const CYAN = 'cyan';
    const DARK_GREY = 'dark_grey';
    const GREEN = 'green';
    const LIGHT_BLUE = 'light_blue';
    const LIGHT_CYAN = 'light_cyan';
    const LIGHT_GREEN = 'light_green';
    const LIGHT_GREY = 'light_grey';
    const LIGHT_MAGENTA = 'light_magenta';
    const LIGHT_RED = 'light_red';
    const MAGENTA = 'magenta';
    const RED = 'red';
    const RESET = 0;
    const WHITE = 'white';
    const YELLOW = 'yellow';

    const BACKGROUND = [
        self::BLACK => "40m",
        self::RED => "41m",
        self::GREEN => "42m",
        self::YELLOW => "43m",
        self::BLUE => "44m",
        self::MAGENTA => "45m",
        self::CYAN => "46m",
        self::LIGHT_GREY => "47m",
        self::RESET => "\e[0m"
    ];

    const FOREGROUND = [
        self::BLACK => "\e[0;30;",
        self::DARK_GREY => "\e[1;30;",
        self::RED => "\e[0;31;",
        self::LIGHT_RED => "\e[1;31;",
        self::GREEN => "\e[0;32;",
        self::LIGHT_GREEN => "\e[1;32;",
        self::BROWN => "\e[0;33;",
        self::YELLOW => "\e[1;33;",
        self::BLUE => "\e[0;34;",
        self::LIGHT_BLUE => "\e[1;34;",
        self::MAGENTA => "\e[0;35;",
        self::LIGHT_MAGENTA => "\e[1;35;",
        self::CYAN => "\e[0;36;",
        self::LIGHT_CYAN => "\e[1;36;",
        self::LIGHT_GREY => "\e[0;37;",
        self::WHITE => "\e[1;37;",
    ];

    /**
     * Background color code.
     *
     * @var string
     */
    private $background;

    /**
     * Foreground color code.
     *
     * @var string
     */
    private $foreground;

    /**
     * Instantiate class and properties.
     *
     * @param string $background
     * @param string $foreground
     */
    public function __construct(string $background, string $foreground)
    {
        $this->setBackground($background);
        $this->setForeground($foreground);
    }

    /**
     * Convert the Color into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $color = "{";
        $color .= sprintf("\"background\": \"%s\",", array_search($this->getBackground(), self::BACKGROUND));
        $color .= sprintf("\"foreground\": \"%s\"", array_search($this->getForeground(), self::FOREGROUND));
        $color .= "}";
        return $color;
    }

    /**
     * Apply color to content and reset back to system default.
     *
     * @param string $content
     * @return string
     */
    public function applyColor(string $content): string
    {
        return sprintf("%s%s$content%s", $this->foreground, $this->background, self::BACKGROUND[self::RESET]);
    }

    /**
     * Get background color value.
     *
     * @return string
     */
    public function getBackground(): string
    {
        return $this->background;
    }

    /**
     * Get foreground color value.
     *
     * @return string
     */
    public function getForeground(): string
    {
        return $this->foreground;
    }

    /**
     * Set background color value.
     *
     * @param string $background
     * @return void
     * @throws Exception Invalid background color supplied.
     */
    public function setBackground(string $background): void
    {
        if (!in_array($background, self::BACKGROUND)) {
            throw new Exception("Invalid background color supplied: {$background}");
        }
        $this->background = $background;
    }

    /**
     * Set foreground color value.
     *
     * @param string $foreground
     * @return void
     * @throws Exception Invalid foreground color supplied.
     */
    public function setForeground(string $foreground): void
    {
        if (!in_array($foreground, self::FOREGROUND)) {
            throw new Exception(sprintf('Invalid Foreground Color: %s', $foreground));
        }
        $this->foreground = $foreground;
    }
}
