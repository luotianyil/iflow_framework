<?php

namespace iflow\console\Adapter;

class HasCommand extends Command {

    public function handle(array $event = []): bool {
        $content = "";
        if (empty($event[0]) || ($event[0] !== 'help' || count($event) > 1))
            $content .= "Unknown instruction: ". implode(' ', $this->Console -> input -> getUserCommand()) ."\r\n\r\n";
        foreach ($this->Console -> command as $key => $value) {
            $key = is_numeric($key) ? $value : $key;
            $content .= $key.PHP_EOL;
        }
        $this->Console -> outWrite($content);
        return true;
    }
}