<?php

namespace App\Http\Log;

class LogFile
{
    public function __construct(private readonly string $path)
    {}
    public function get(int $limit = 50): string
    {
        if(!file_exists($this->path)) {
            throw new \DomainException('Log file does not exist');
        }
        $content = file_get_contents($this->path);
        if(false === $content) {
            throw new \DomainException('Log file read error');
        }
        $allLines = explode("\n", $content);
        $nonEmptyLines = array_values(array_filter($allLines, function($line) {
            return trim($line) !== '';
        }));

        $lastLines = array_slice($nonEmptyLines, -$limit);
        $output = '';

        foreach($lastLines as $index => $line) {
            $lineNumber = count($nonEmptyLines) - $limit + $index + 1;
            $output .= sprintf("<div class='log-line'><span class='line-number'>%4d</span> %s</div>\n",
                $lineNumber, htmlspecialchars($line));
        }
        return $output;
    }
}
