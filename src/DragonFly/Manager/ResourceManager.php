<?php

namespace DragonFly\Manager;

use DragonFly\Base\Singleton;
use DragonFly\World\Color;
use DragonFly\Resource\Sprite;
use DragonFly\Resource\Frame;
use Exception;

class ResourceManager extends Manager
{
    /**
     * Maximum number of unique assets in game.
     */
    const MAX_SPRITES = 1000;

    /**
     * Delimiters used to parse sprite file.
     */
    const TOKEN_HEADER = 'HEADER';
    const TOKEN_BODY = 'BODY';
    const TOKEN_FOOTER = 'FOOTER';
    const TOKEN_FRAME = 'frames';
    const TOKEN_HEIGHT = 'height';
    const TOKEN_WIDTH = 'width';
    const TOKEN_COLOR = 'color';
    const TOKEN_BACKGROUND_COLOR = 'bgcolor';
    const TOKEN_SLOWDOWN = 'slowdown';
    const TOKEN_END = 'end';
    const TOKEN_VERSION = 'version';

    /**
     * The single instance of the class.
     *
     * @var Singleton
     */
    protected static $instance;

    /**
     * Count of number of loaded sprites.
     *
     * @var int
     */
    private $spriteCount;

    /**
     * Array of sprites.
     *
     * @var array
     */
    private $sprites;

    /**
     * Instantiate class and properties.
     */
    public function __construct()
    {
        parent::__construct();
        $this->spriteCount = 0;
        $this->sprites = [];
        $this->setType(self::TYPE_RESOURCE);
    }

    /**
     * Destrory the class and properties.
     */
    public function __destruct()
    {
        unset($this->spriteCount);
        unset($this->sprites);
        parent::__destruct();
    }

    /**
     * Convert the ResourceManager into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $resource = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $resource .= "\"spriteCount\":{$this->spriteCount}";
        $resource .= "\"sprites\":[";
        for ($i = 0; $i < $this->spriteCount; $i++) {
            $resource .= "{$this->sprites[$i]}";
            if ($i < $this->spriteCount - 1) {
                $resource .= ",";
            }
        }
        $resource .= "]}";
        return $resource;
    }

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    public static function getInstance(): Singleton
    {
        if (!(self::$instance instanceof ResourceManager)) {
            self::$instance = new ResourceManager;
        }
        return self::$instance;
    }

    /**
     * Get next line from file.
     *
     * @param resource $file
     * @return string
     * @throws Exception EOF or FALSE returned when reading line from file.
     */
    public function getLine($file): string
    {
        if (feof($file) || ($line = fgets($file)) === false) {
            throw new Exception("There is no more lines to read from file: {$file}");
        }
        return str_replace(PHP_EOL, '', $line);
    }

    /**
     * Find sprite with indicated label.
     *
     * @param string $label
     * @return Sprite
     * @throws Exception Sprite could not be found.
     */
    public function getSprite(string $label): Sprite
    {
        foreach($this->sprites AS $sprite) {
            if ($sprite->getLabel() === $label) {
                return $sprite;
            }
        }
        throw new Exception("Sprite does not exist: {$label}");
    }

    /**
     * Load sprite from file and assign indicated label to sprite.
     *
     * @param string $filePath
     * @param string $label
     * @return void
     * @throws Exception Could not find file, readData, matchLineInt, matchLineString, matchFrame exceptions.
     */
    public function loadSprite(string $filePath, string $label): void
    {
        if (!file_exists($filePath) || ($file = fopen($filePath, 'r')) === false) {
            throw new Exception("Could not locate sprite file: {$filePath}");
        }
        
        // PARSE HEADER
        $data = $this->readData($file, self::TOKEN_HEADER);
        $sprite = new Sprite($this->matchLineInt($data, self::TOKEN_FRAME));
        $sprite->setColor(new Color(Color::BACKGROUND[$this->matchLineStr($data, self::TOKEN_BACKGROUND_COLOR)], Color::FOREGROUND[$this->matchLineStr($data, self::TOKEN_COLOR)]));
        $sprite->setHeight($this->matchLineInt($data, self::TOKEN_HEIGHT));
        $sprite->setSlowdown($this->matchLineInt($data, self::TOKEN_SLOWDOWN));
        $sprite->setWidth($this->matchLineInt($data, self::TOKEN_WIDTH));

        // PARSE BODY
        $data = $this->readData($file, self::TOKEN_BODY);
        while (!empty($data)) {
            $sprite->addFrame($this->matchFrame($data, $sprite->getWidth(), $sprite->getHeight()));
        }

        // PARSE FOOTER
        $data = $this->readData($file, self::TOKEN_FOOTER);
        $sprite->setLabel($label);

        $this->sprites[] = $sprite;
        fclose($file);
    }

    /**
     * Match frame lines until "end", clearing all from data.
     *
     * @param array $data
     * @param integer $width
     * @param integer $height
     * @return Frame
     * @throws Exception Frame line width or height does not match respective setting.
     */
    private function matchFrame(array &$data, int $width, int $height): Frame
    {
        $lines = [];
        foreach($data AS $index => $line) {
            if ($line === self::TOKEN_END) {
                unset($data[$index]);
                break;
            }
            if (strlen($line) !== $width) {
                throw new Exception("Frame line width does not match sprite width.");
            }
            $lines[] = $line;
            unset($data[$index]);
        }
        if (count($lines) !== $height) {
            throw new Exception("Frame line height does not match sprite height.");
        }
        return new Frame($width, $height, implode('', $lines));
    }

    /**
     * Match token in array of lines.
     * Remove line from array.
     *
     * @param array $data
     * @param string $token
     * @return string Corresponding value.
     */
    private function matchLineInt(array &$data, string $token): int
    {
        return intval($this->matchLineStr($data, $token));
    }

    /**
     * Match token in array of lines.
     * Remove line from array.
     *
     * @param array $data
     * @param string $token
     * @return string Corresponding value.
     * @throws Exception Token was not found.
     */
    private function matchLineStr(array &$data, string $token): string
    {
        foreach ($data AS $index => $line) {
            if (strpos($line, $token) === 0) {
                $value = substr($line, strlen($token) + 1);
                unset($data[$index]);
                return $value;
            }
        }
        throw new Exception("Token does not exist within dataset: {$token}");
    }

    /**
     * Read next section of data from file.
     *
     * @param resource $file
     * @param string $delimiter
     * @return array
     * @throws Exception Start of section missing, no data present, or EOF.
     */
    private function readData($file, string $delimiter): array
    {
        $data = [];
        $begin = "<{$delimiter}>";
        $end = "</{$delimiter}>";

        $line = $this->getLine($file);
        if ($line !== $begin) {
            var_dump($line);
            throw new Exception("Start of section is missing: {$begin}");
        }

        $line = $this->getLine($file);
        while($line !== $end) {
            $data[] = $line;
            $line = $this->getLine($file);
        }

        if (empty($data)) {
            throw new Exception("No data present for section: {$delimiter}");
        }
        return $data;
    }

    /**
     * Shutdown Manager.
     *
     * @return void
     */
    public function shutDown(): void
    {
        parent::shutDown();
        for ($i = $this->spriteCount - 1; $i >= 0; $i--) {
            unset($this->sprites[$i]);
        }
        $this->spriteCount = 0;
    }

    /**
     * Startup Manager.
     *
     * @return void
     * @throws Exception DisplayManager has not been started.
     */
    public function startUp(): void
    {
        if (!DisplayManager::getInstance()->isStarted()) {
            throw new Exception("Can not start the {$this->getType()}");
        }
        parent::startUp();
        $this->spriteCount = 0;
    }

    /**
     * Unload sprite with indicated label.
     *
     * @param string $label
     * @return void 
     * @throws Exception Sprite could not be found.
     */
    public function unloadSprite(string $label): void
    {
        foreach ($this->sprites AS $index => $sprite) {
            if ($label === $sprite->getLabel()) {
                unset($this->sprites[$index]);
                return;
            }
        }
        throw new Exception("Sprite does not exist: {$label}");
    }
}
