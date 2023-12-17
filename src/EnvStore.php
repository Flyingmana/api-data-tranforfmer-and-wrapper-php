<?php

declare(strict_types=1);

namespace Flyingmana\ApiDataWrapper;

use Ayeo\Parser\Parser;

class EnvStore
{
    protected array $envVars = [];
    protected $parser;

    public function __construct(
        string $filePath
    )
    {
        $env_content = json_decode(file_get_contents($filePath), true);

        foreach ($env_content['variables'] as $entry)
        {
            $this->envVars[$entry['key']] = $entry['value'];
        }
        $this->parser = new Parser(
            open: "<<",
            close: ">>"
        );
    }

    public function getVar(string $name): string
    {
        return $this->envVars[$name];
    }

    public function setVar(string $name, string $value)
    {
        $this->envVars[$name] = $value;
        //var_dump($this->envVars);
    }

    public function resolveVarInString(string $string): string
    {
        $result = $string;
        $i = 1;
        while (str_contains($result,'<<')) {
            $i++;
            if($i > 20) {
                throw new \Exception("possible endless loop");
            }
            $result = $this->parser->parse($result, $this->envVars);
        }
        return $result;
    }

    public function __get(string $name)
    {
        $this->getVar($name);
    }
}
