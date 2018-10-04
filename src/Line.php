<?php

namespace Fabstract\INI;

use Fabstract\Component\Assert\Assert;
use Fabstract\INI\Constant\LineTypes;

class Line
{
    /** @var string */
    private $type = null;
    /** @var string */
    private $key = null;
    /** @var string */
    private $value = null;

    /** @var Line */
    private $before = null;
    /** @var Line */
    private $after = null;

    /**
     * Line constructor.
     * @param string $type
     * @param string $value
     * @param string|null $key
     */
    private function __construct($type, $value, $key = null)
    {
        $this->setType($type);
        $this->setValue($value);
        $this->setKey($key);
    }

    /**
     * @param string $type
     * @param string $value
     * @param string $key
     * @return Line
     */
    public static function create($type, $value, $key = null)
    {
        return new static($type, $value, $key);
    }

    /**
     * @param string $line_content
     * @return Line
     */
    public static function parse($line_content)
    {
        Assert::isString($line_content);
        $line_content = trim($line_content);
        if ($line_content === '') {
            static::create(LineTypes::EMPTY_LINE, '');
        }

        if ($line_content[0] === ';' || $line_content[0] === '#') {
            static::create(LineTypes::COMMENT, substr($line_content, 1, strlen($line_content) - 1));
        }

        preg_match("/^\[(.+?)\]$/", $line_content, $section_output);
        if (count($section_output) !== 0) {
            static::create(LineTypes::SECTION, trim($section_output[1]));
        }

        preg_match("/^(.+?)=(.+?)$/", $line_content, $settings_output);
        if (count($settings_output) !== 0) {
            $key = trim($settings_output[1]);
            $value = trim($settings_output[2]);
            static::create(LineTypes::SETTING, $value, $key);
        }

        throw new \ParseError("unexpected content " . $line_content);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Line
     */
    public function setType($type)
    {
        Assert::isInArray($type, LineTypes::ALL, true, 'type');
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return Line
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Line
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return Line
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * @param Line $before
     * @return Line
     */
    public function setBefore($before)
    {
        $this->before = $before;
        return $this;
    }

    /**
     * @return Line
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @param Line $after
     * @return Line
     */
    public function setAfter($after)
    {
        $this->after = $after;
        return $this;
    }

    /**
     * @param Line $line
     * @return Line
     */
    public function insertAfter($line)
    {
        $old_after = $this->getAfter();
        if ($old_after !== null) {
            $old_after->setBefore($line);
        }
        $this->setAfter($line);
        $line->setBefore($this);
        $line->setAfter($old_after);
        return $this;
    }

    /**
     * @param Line $line
     * @return Line
     */
    public function insertBefore($line)
    {
        $old_before = $this->getBefore();
        if ($old_before !== null) {
            $old_before->setAfter($line);
        }
        $this->setBefore($line);
        $line->setAfter($this);
        $line->setBefore($old_before);
        return $this;
    }

    /**
     * @param string $name
     * @return Line|null
     */
    public function getSettingLine($name)
    {
        if ($this->getType() === LineTypes::SECTION) {
            $line = $this;
            while ($line !== null) {
                $line = $line->getAfter();
                if ($line === null || $line->getType() === LineTypes::SECTION) {
                    break;
                }

                if ($line->getType() === LineTypes::COMMENT || $line->getType() === LineTypes::EMPTY_LINE) {
                    continue;
                }

                if ($line->getKey() === $name) {
                    return $line;
                }
            }
        }

        return null;
    }

    public function __toString()
    {
        switch ($this->type) {
            case LineTypes::EMPTY_LINE:
                $content = PHP_EOL;
                break;
            case LineTypes::COMMENT:
                $content = ';' . $this->value . PHP_EOL;
                break;
            case LineTypes::SECTION:
                $content = '[' . $this->value . ']' . PHP_EOL;
                break;
            case LineTypes::SETTING:
                $content = $this->key . '=' . $this->value . PHP_EOL;
                break;
            default:
                $content = '';
        }

        if ($this->getAfter() === null) {
            return $content;
        } else {
            return $content . $this->getAfter();
        }
    }
}
