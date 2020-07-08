<?php

namespace DragonFly\Manager;

use DragonFly\Base\Singleton;
use DragonFly\Manager\Manager;
use Exception;

class LogManager extends Manager
{
    /**
     * Log Directory and File.
     */
    const LOG_DIRECTORY = __DIR__ . '/../../../log/';
    const LOG_FILE_NAME = '_dragonfly.log';

    /**
     * Log Message Types.
     */
    const MESSAGE_TYPE_ERROR = 'ERROR';
    const MESSAGE_TYPE_INFO = 'INFO';
    const MESSAGE_TYPE_WARNING = 'WARNING';
    const MESSAGE_TYPES = [self::MESSAGE_TYPE_ERROR, self::MESSAGE_TYPE_INFO, self::MESSAGE_TYPE_WARNING];

    /**
     * Log file resource.
     *
     * @var resource
     */
    private $file;

    /**
     * True if flush to disk after each write.
     *
     * @var boolean
     */
    private $flush;

    private $warning;

    /**
     * The single instance of the class.
     *
     * @var Singleton
     */
    protected static $instance;

    /**
     * Instantiate class and properties.
     */
    protected function __construct()
    {
        
        if (!is_dir(self::LOG_DIRECTORY) && !mkdir(self::LOG_DIRECTORY, 0755, true)) {
            throw new Exception(sprintf('Could not create the log directory: %s', self::LOG_DIRECTORY));
        }
        
        $this->eventCount = 0;
        $this->events = [];
        $this->interested = [];
        $this->setFlush();
        $this->setType(self::TYPE_LOG);
        $this->started = false;
        $this->warning = false;

        $fileName = self::LOG_DIRECTORY . date('Ymd') . self::LOG_FILE_NAME;
        $this->file = fopen($fileName, 'a');
        foreach($this->splashMessage() AS $line) {
            $this->info($line);
        }
    }

    /**
     * Destrory the class and properties.
     */
    public function __destruct()
    {
        $this->closeFile();
        unset($this->file);
        unset($this->flush);
        parent::__destruct();
    }

    /**
     * Convert the manager into a string (json).
     *
     * @return string
     */
    public function __toString(): string
    {
        if (!isset($this->flush)) {
            return '';
        }
        $manager = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $manager .= "'flush': {$this->flush},";
        $manager .= sprintf("'file': '%s'", stream_get_meta_data($this->file)['uri']);
        $manager .= "}";
        return $manager;;
    }

    /**
     * Write error to logfile. Supports sprintf formatting of strings.
     *
     * @param ...$args
     * @return integer Number of bytes written, -1 if error.
     */
    public function error(...$args): int
    {
        try {
            return $this->writeLog($this->formatMessage($args, self::MESSAGE_TYPE_ERROR));
        }
        catch (Exception $e) {
            return $this->writeLog($this->formatMessage([$e], self::MESSAGE_TYPE_ERROR));
        }
    }

    /**
     * Return the one and only instance of the class.
     *
     * @return Singleton
     */
    public static function getInstance(): Singleton
    {
        if (!(self::$instance instanceof LogManager)) {
            self::$instance = new LogManager;
        }
        return self::$instance;
    }

    /**
     * Write info to logfile. Supports sprintf formatting of strings.
     *
     * @param ...$args
     * @return integer Number of bytes written, -1 if error.
     */
    public function info(...$args): int
    {
        try {
            return $this->writeLog($this->formatMessage($args, self::MESSAGE_TYPE_INFO));
        }
        catch (Exception $e) {
            return $this->error($e);
        }
    }

    /**
     * Set flush of the log file after each write.
     *
     * @param boolean $flush
     * @return void
     */
    public function setFlush($flush = true): void
    {
        $this->flush = $flush;
    }

    /**
     * Startup LogManager.
     *
     * @return void 
     * @throws Exception File could not be opened.
     */
    public function startUp(): void
    { 
        if (!is_resource($this->file)) {
            $fileName = self::LOG_DIRECTORY . date('Ymd') . self::LOG_FILE_NAME;
            $this->file = fopen($fileName, 'a');
            if ($this->file === false) {
                throw new Exception("Could not open log file: {$fileName}");
            }
        }
        parent::startUp();
    }

    /**
     * Write warning to logfile. Supports sprintf formatting of strings.
     *
     * @param ...$args
     * @return integer Number of bytes written, -1 if error.
     */
    public function warning(...$args): int
    {
        try {
            if ($this->warning) {
                return $this->writeLog($this->formatMessage($args, self::MESSAGE_TYPE_WARNING));
            }
            return 0;
        }
        catch (Exception $e) {
            return $this->error($e);
        }
    }

    /**
     * Close log file resource.
     *
     * @return void
     */
    private function closeFile(): void
    {
        if (is_resource($this->file) && !fclose($this->file)) {
            throw new Exception('Could not close the file');
        }
        $this->file = null;
    }

    /**
     * Format exception for writing to log file.
     *
     * @param Exception $exception
     * @return string
     */
    private function formatException(Exception $exception): string
    {
        return sprintf("Exception: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}%sStack trace:%s{$exception->getTraceAsString()}", PHP_EOL, PHP_EOL);
    }

    /**
     * Format message for writing to log file.
     *
     * @param array $args
     * @param string $messageType
     * @return string
     * @throws Exception Argument 1 is not a string or Exception.
     * @throws Exception Message type is not within the listing of valid message types.
     */
    private function formatMessage(array $args, string $messageType): string
    {
        if (!isset($args[0]) || (!is_string($args[0]) && !($args[0] instanceof Exception))) {
            throw new Exception("Invalid argument 1 supplied, must be an instance of string or Exception.");
        }
        if (!in_array($messageType, self::MESSAGE_TYPES)) {
            throw new Exception("Invalid log message type: {$messageType}");
        }

        $message = "{$messageType}: %s";
        if ($args[0] instanceof Exception) {
            $message = sprintf($message, $this->formatException($args[0]));
        }
        else {
            $message = sprintf($message, $args[0]);
            unset($args[0]);
            $message = vsprintf($message, $args);
        }

        return $message;
    }

    /**
     * Splash message displayed each time the engine is started.
     *
     * @return array
     */
    private function splashMessage(): array
    {
        $splash[] = "*****************************************************";
        $splash[] = "*****************************************************";
        $splash[] = "*      ____                              ______     *";
        $splash[] = "*     / __ \                            / __/ /     *";
        $splash[] = "*    / / / /___ ___  ____  ____  ____  / /_/ /_  __ *";
        $splash[] = "*   / / / / __/ __ `/ __ `/ __ \/ __ \/ __/ / / / / *";
        $splash[] = "*  / /_/ / / / /_/ / /_/ / /_/ / / / / / / / /_/ /  *";
        $splash[] = "* /_____/_/  \__,_/\__, /\____/_/ /_/_/ /_/\__, /   *";
        $splash[] = "*                 ___/ /                  ___/ /    *";
        $splash[] = "*                /____/                  /____/     *";
        $splash[] = "*****************************************************";
        $splash[] = "* Run time: " . date('Y-m-d H:i:s') . "                     *";
        $splash[] = "*****************************************************";
        $splash[] = "*****************************************************";
        return $splash;
    }

    /**
     * Write to logfile. Supports sprintf formatting of strings.
     *
     * @param ...$args
     * @return integer Number of bytes written, -1 if error.
     */
    private function writeLog(string $message): int
    {
        if (!isset($this->file) || !is_resource($this->file)) {
            throw new Exception("Log file not set and open for writing: {$this}");
        }
        $message = sprintf('[%s] %s%s', date('Y-m-d H:i:s'), $message, PHP_EOL);
        $byteAmount = fwrite($this->file, $message, strlen($message));
        if ($this->flush) {
            fflush($this->file);
        }
        return $byteAmount;
    }
}
