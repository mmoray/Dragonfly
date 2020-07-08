<?php

namespace DragonFly\Event;

class KeyboardEvent extends Event
{
    const KEY_AMPERSAND = 38;
    const KEY_ASTERISK = 42;
    const KEY_AT = 64;
    const KEY_CARET = 94;
    const KEY_CLOSE_PARENTHESES = 41;
    const KEY_COMMA = 44;
    const KEY_DOLLAR = 36;
    const KEY_EQUAL = 61;
    const KEY_ESCAPE = 27;
    const KEY_EXCLAMATION = 33;
    const KEY_GRAVE_ACCENT = 96;
    const KEY_HASH = 35;
    const KEY_LOWER_A = 97;
    const KEY_LOWER_B = 98;
    const KEY_LOWER_C = 99;
    const KEY_LOWER_D = 100;
    const KEY_LOWER_E = 101;
    const KEY_LOWER_F = 102;
    const KEY_LOWER_G = 103;
    const KEY_LOWER_H = 104;
    const KEY_LOWER_I = 105;
    const KEY_LOWER_J = 106;
    const KEY_LOWER_K = 107;
    const KEY_LOWER_L = 108;
    const KEY_LOWER_M = 109;
    const KEY_LOWER_N = 110;
    const KEY_LOWER_O = 111;
    const KEY_LOWER_P = 112;
    const KEY_LOWER_Q = 113;
    const KEY_LOWER_R = 114;
    const KEY_LOWER_S = 115;
    const KEY_LOWER_T = 116;
    const KEY_LOWER_U = 117;
    const KEY_LOWER_V = 118;
    const KEY_LOWER_W = 119;
    const KEY_LOWER_X = 120;
    const KEY_LOWER_Y = 121;
    const KEY_LOWER_Z = 122;
    const KEY_MINUS = 45;
    const KEY_NUM0 = 48;
    const KEY_NUM1 = 49;
    const KEY_NUM2 = 50;
    const KEY_NUM3 = 51;
    const KEY_NUM4 = 52;
    const KEY_NUM5 = 53;
    const KEY_NUM6 = 54;
    const KEY_NUM7 = 55;
    const KEY_NUM8 = 56;
    const KEY_NUM9 = 57;
    const KEY_OPEN_PARENTHESES = 40;
    const KEY_PERCENT = 37;
    const KEY_PERIOD = 46;
    const KEY_PLUS = 43;
    const KEY_RETURN = 10;
    const KEY_SLASH = 47;
    const KEY_SPACE = 32;
    const KEY_TAB = 9;
    const KEY_TILDE = 126;
    const KEY_UNDEFINED = 0;
    const KEY_UNDERSCORE = 95;
    const KEY_UPPER_A = 65;
    const KEY_UPPER_B = 66;
    const KEY_UPPER_C = 67;
    const KEY_UPPER_D = 68;
    const KEY_UPPER_E = 69;
    const KEY_UPPER_F = 70;
    const KEY_UPPER_G = 71;
    const KEY_UPPER_H = 72;
    const KEY_UPPER_I = 73;
    const KEY_UPPER_J = 74;
    const KEY_UPPER_K = 75;
    const KEY_UPPER_L = 76;
    const KEY_UPPER_M = 77;
    const KEY_UPPER_N = 78;
    const KEY_UPPER_O = 79;
    const KEY_UPPER_P = 80;
    const KEY_UPPER_Q = 81;
    const KEY_UPPER_R = 82;
    const KEY_UPPER_S = 83;
    const KEY_UPPER_T = 84;
    const KEY_UPPER_U = 85;
    const KEY_UPPER_V = 86;
    const KEY_UPPER_W = 87;
    const KEY_UPPER_X = 88;
    const KEY_UPPER_Y = 89;
    const KEY_UPPER_Z = 90;
    const KEYS = [
        self::KEY_AMPERSAND => self::KEY_AMPERSAND,
        self::KEY_ASTERISK => self::KEY_ASTERISK,
        self::KEY_AT => self::KEY_AT,
        self::KEY_CARET => self::KEY_CARET,
        self::KEY_CLOSE_PARENTHESES => self::KEY_CLOSE_PARENTHESES,
        self::KEY_COMMA => self::KEY_COMMA,
        self::KEY_DOLLAR => self::KEY_DOLLAR,
        self::KEY_EQUAL => self::KEY_EQUAL,
        self::KEY_ESCAPE => self::KEY_ESCAPE,
        self::KEY_EXCLAMATION => self::KEY_EXCLAMATION,
        self::KEY_GRAVE_ACCENT => self::KEY_GRAVE_ACCENT,
        self::KEY_HASH => self::KEY_HASH,
        self::KEY_LOWER_A => self::KEY_LOWER_A,
        self::KEY_LOWER_B => self::KEY_LOWER_B,
        self::KEY_LOWER_C => self::KEY_LOWER_C,
        self::KEY_LOWER_D => self::KEY_LOWER_D,
        self::KEY_LOWER_E => self::KEY_LOWER_E,
        self::KEY_LOWER_F => self::KEY_LOWER_F,
        self::KEY_LOWER_G => self::KEY_LOWER_G,
        self::KEY_LOWER_H => self::KEY_LOWER_H,
        self::KEY_LOWER_I => self::KEY_LOWER_I,
        self::KEY_LOWER_J => self::KEY_LOWER_J,
        self::KEY_LOWER_K => self::KEY_LOWER_K,
        self::KEY_LOWER_L => self::KEY_LOWER_L,
        self::KEY_LOWER_M => self::KEY_LOWER_M,
        self::KEY_LOWER_N => self::KEY_LOWER_N,
        self::KEY_LOWER_O => self::KEY_LOWER_O,
        self::KEY_LOWER_P => self::KEY_LOWER_P,
        self::KEY_LOWER_Q => self::KEY_LOWER_Q,
        self::KEY_LOWER_R => self::KEY_LOWER_R,
        self::KEY_LOWER_S => self::KEY_LOWER_S,
        self::KEY_LOWER_T => self::KEY_LOWER_T,
        self::KEY_LOWER_U => self::KEY_LOWER_U,
        self::KEY_LOWER_V => self::KEY_LOWER_V,
        self::KEY_LOWER_W => self::KEY_LOWER_W,
        self::KEY_LOWER_X => self::KEY_LOWER_X,
        self::KEY_LOWER_Y => self::KEY_LOWER_Y,
        self::KEY_LOWER_Z => self::KEY_LOWER_Z,
        self::KEY_MINUS => self::KEY_MINUS,
        self::KEY_NUM0 => self::KEY_NUM0,
        self::KEY_NUM1 => self::KEY_NUM1,
        self::KEY_NUM2 => self::KEY_NUM2,
        self::KEY_NUM3 => self::KEY_NUM3,
        self::KEY_NUM4 => self::KEY_NUM4,
        self::KEY_NUM5 => self::KEY_NUM5,
        self::KEY_NUM6 => self::KEY_NUM6,
        self::KEY_NUM7 => self::KEY_NUM7,
        self::KEY_NUM8 => self::KEY_NUM8,
        self::KEY_NUM9 => self::KEY_NUM9,
        self::KEY_OPEN_PARENTHESES => self::KEY_OPEN_PARENTHESES,
        self::KEY_PERCENT => self::KEY_PERCENT,
        self::KEY_PERIOD => self::KEY_PERIOD,
        self::KEY_PLUS => self::KEY_PLUS,
        self::KEY_RETURN => self::KEY_RETURN,
        self::KEY_SLASH => self::KEY_SLASH,
        self::KEY_SPACE => self::KEY_SPACE,
        self::KEY_TAB => self::KEY_TAB,
        self::KEY_TILDE => self::KEY_TILDE,
        self::KEY_UNDEFINED => self::KEY_UNDEFINED,
        self::KEY_UNDERSCORE => self::KEY_UNDERSCORE,
        self::KEY_UPPER_A => self::KEY_UPPER_A,
        self::KEY_UPPER_B => self::KEY_UPPER_B,
        self::KEY_UPPER_C => self::KEY_UPPER_C,
        self::KEY_UPPER_D => self::KEY_UPPER_D,
        self::KEY_UPPER_E => self::KEY_UPPER_E,
        self::KEY_UPPER_F => self::KEY_UPPER_F,
        self::KEY_UPPER_G => self::KEY_UPPER_G,
        self::KEY_UPPER_H => self::KEY_UPPER_H,
        self::KEY_UPPER_I => self::KEY_UPPER_I,
        self::KEY_UPPER_J => self::KEY_UPPER_J,
        self::KEY_UPPER_K => self::KEY_UPPER_K,
        self::KEY_UPPER_L => self::KEY_UPPER_L,
        self::KEY_UPPER_M => self::KEY_UPPER_M,
        self::KEY_UPPER_N => self::KEY_UPPER_N,
        self::KEY_UPPER_O => self::KEY_UPPER_O,
        self::KEY_UPPER_P => self::KEY_UPPER_P,
        self::KEY_UPPER_Q => self::KEY_UPPER_Q,
        self::KEY_UPPER_R => self::KEY_UPPER_R,
        self::KEY_UPPER_S => self::KEY_UPPER_S,
        self::KEY_UPPER_T => self::KEY_UPPER_T,
        self::KEY_UPPER_U => self::KEY_UPPER_U,
        self::KEY_UPPER_V => self::KEY_UPPER_V,
        self::KEY_UPPER_W => self::KEY_UPPER_W,
        self::KEY_UPPER_X => self::KEY_UPPER_X,
        self::KEY_UPPER_Y => self::KEY_UPPER_Y,
        self::KEY_UPPER_Z => self::KEY_UPPER_Z
    ];

    /**
     * Key value.
     *
     * @var integer
     */
    private $value;

    /**
     * Instantiate class and properties.
     *
     * @param int $value
     */
    public function __construct(...$params)
    {
        $this->value = isset($params[0]) && is_numeric($params[0]) && in_array($params[0], self::KEYS) ? $params[0] : self::KEY_UNDEFINED;
        parent::__construct(self::EVENT_KEYBOARD);
    }

    /**
     * Destory class and propereties.
     */
    public function __destruct()
    {
        unset($this->value);
        parent::__destruct();
    }

    /**
     * Convert the KeyboardEvent into a string (JSON).
     *
     * @return string
     */
    public function __toString(): string
    {
        $keyboard = sprintf("%s,", substr(parent::__toString(), 0, -1));
        $keyboard .= "\"value\":{$this->getValue()}";
        $keyboard .= "}";
        return $keyboard;
    }

    /**
     * Get key from event.
     *
     * @return integer
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Set key in event.
     *
     * @param integer $value
     * @return void
     */
    public function setValue(int $value): void
    {
        $this->value = in_array($value, self::KEYS) ? $value : self::KEY_UNDEFINED;
    }
}
