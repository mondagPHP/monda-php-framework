<?php

namespace framework\string;

/**
 * Class StringBuffer
 * @package core\string
 */
class StringBuffer
{
    private $strMap = [];

    public function __construct(?string $str = null)
    {
        if ($str !== null) {
            $this->append($str);
        }
    }

    public function isEmpty(): bool
    {
        return count($this->strMap) == 0;
    }

    //append content
    public function append($str = null)
    {
        array_push($this->strMap, $str);
    }

    //append line
    public function appendLine($str = null)
    {
        $this->append($str . "\n");
    }

    //append line with tab symbol
    public function appendTab($str = null, $tabNum = 1)
    {
        $tab = '';
        for ($i = 0; $i < $tabNum; $i++) {
            $tab .= "\t";
        }
        $this->appendLine($tab . $str);
    }

    public function toString(): string
    {
        foreach ($this->strMap as $key => $value) {
            if (is_array($value)) {
                $this->strMap[$key] = implode('', $value);
            }
        }
        return implode('', $this->strMap);
    }
}
