<?php

namespace Fabstract\INI;

use Fabstract\Component\Assert\Assert;
use Fabstract\INI\Constant\LineTypes;

class INI
{
    /** @var Line */
    private $lines = [];
    /** @var Line */
    private $first_line = null;

    /**
     * INI constructor.
     * @param string $content
     */
    private function __construct($content)
    {
        Assert::isString($content, 'content');
        $this->parse($content);
    }

    /**
     * @param string $file_name
     * @return INI
     */
    public static function fromFile($file_name)
    {
        $file_content = file_get_contents($file_name);
        return new static($file_content);
    }

    /**
     * @param string $content
     * @return INI
     */
    public static function fromString($content)
    {
        return new static($content);
    }

    /**
     * @param string $name
     * @return Line|null
     */
    public function getSectionLine($name)
    {
        $line = $this->first_line;
        while ($line !== null) {
            if ($line->getType() === LineTypes::SECTION) {
                if ($line->getValue() === $name) {
                    return $line;
                }
            }
            $line = $line->getAfter();
        }

        return null;
    }

    public function getSettingLine($section_name, $setting_name)
    {
        $section_line = $this->getSectionLine($section_name);
        if ($section_line !== null) {
            return $section_line->getSettingLine($setting_name);
        }

        return null;
    }

    /**
     * @param string $content
     */
    private function parse($content)
    {
        $lines = preg_split('/$\R?^/m', $content);
        /** @var Line $previous_line */
        $previous_line = null;
        foreach ($lines as $str_line) {
            $line = Line::parse($str_line);
            if ($previous_line === null) {
                $this->first_line = $line;
            } else {
                $previous_line->insertAfter($line);
            }

            $previous_line = $line;
            $this->lines[] = $line;
        }
    }

    public function __toString()
    {
        if ($this->first_line !== null) {
            return (string)$this->first_line;
        }

        return '';
    }

    public function write($file_name)
    {
        $content = (string)$this;
        echo $content;
        file_put_contents($file_name, $content);
    }
}
