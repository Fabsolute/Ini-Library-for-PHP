<?php

namespace Fabstract\INI\Constant;

class LineTypes
{
    const COMMENT = 'comment';
    const SECTION = 'section';
    const SETTING = 'setting';
    const EMPTY_LINE = 'empty_line';

    const ALL = [
        self::COMMENT,
        self::SECTION,
        self::SETTING,
        self::EMPTY_LINE
    ];
}
