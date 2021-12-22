<?php

namespace DimaBzz\LaravelConfigWriter;

use DimaBzz\LaravelConfigWriter\Events\WriteSuccess;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConfigWriter
{
    /**
     * @var string
     */
    private string $contents;

    /**
     * @var string
     */
    private string $cfg;

    /**
     * @var string
     */
    private string $type = 'all';

    /**
     * @var null|string
     */
    private ?string $nameCfg;

    public function __construct()
    {
        $this->nameCfg = config('config-writer.name');
    }

    /**
     * Set config name.
     *
     * @param string $config
     * @return self
     */
    public function setConfig(string $config): self
    {
        $this->nameCfg = $config;

        return $this;
    }

    /**
     * @param array $newValues
     * @return bool
     * @throws Exception
     */
    public function write(array $newValues): bool
    {
        return $this->setCfg()
            ->getContent()
            ->setContent($newValues)
            ->saveContent();
    }

    /**
     * @param array $newValues
     * @return $this
     * @throws Exception
     */
    private function setContent(array $newValues): self
    {
        $contents = $this->parseContent($newValues);

        $result = eval('?>'.$contents);

        foreach ($newValues as $key => $expectedValue) {
            $parts = explode('.', $key);

            $array = $result;
            foreach ($parts as $part) {
                if (! is_array($array) || ! array_key_exists($part, $array)) {
                    throw new Exception(sprintf('Unable to rewrite key %s in config, does it exist?', $key));
                }

                $array = $array[$part];
            }
            $actualValue = $array;

            if ($actualValue != $expectedValue) {
                throw new Exception(sprintf('Unable to rewrite key %s in config, rewrite failed.', $key));
            }
        }

        $this->contents = $contents;

        return $this;
    }

    /**
     * @param array $newValues
     * @return string
     */
    private function parseContent(array $newValues): string
    {
        $result = $this->contents;

        foreach ($newValues as $path => $value) {
            $result = $this->parseContentValue($result, $path, $value);
        }

        return $result;
    }

    /**
     * @param string $contents
     * @param string $path
     * @param $value
     * @return string
     */
    private function parseContentValue(string $contents, string $path, $value): string
    {
        $result = $contents;
        $items = explode('.', $path);
        $key = array_pop($items);
        $replaceValue = $this->writeValueToPhp($value, true);

        $count = 0;
        $patterns = $this->getPatterns($key, $items);

        $r = $this->updateContents($patterns, $result, $replaceValue, $count);

        if ($count === 0) {
            $this->type = 'all';
            $patterns = $this->getPatterns($key, $items);
            $r = $this->updateContents($patterns, $result, $replaceValue, $count);
        }

        return $r;
    }

    /**
     * @param array $patterns
     * @param string $result
     * @param string $replaceValue
     * @param $count
     * @return string
     */
    private function updateContents(array $patterns, string $result, string $replaceValue, &$count): string
    {
        foreach ($patterns as $pattern) {
            $result = preg_replace($pattern, '${1}${2}'.$replaceValue, $result, 1, $count);

            if ($count > 0) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param string $key
     * @param array $items
     * @return array
     */
    private function getPatterns(string $key, array $items = []): array
    {
        $patterns = [];

        if (in_array($this->type, ['string', 'all'])) {
            $patterns[] = $this->buildStringExpression($key, $items);
            $patterns[] = $this->buildStringExpression($key, $items, '"');
        }

        if (in_array($this->type, ['constant', 'all'])) {
            $patterns[] = $this->buildConstantExpression($key, $items);
        }

        if (in_array($this->type, ['array', 'all'])) {
            $patterns[] = $this->buildArrayExpression($key, $items);
        }

        return $patterns;
    }

    /**
     * @param mixed $value
     * @param bool $setType
     * @return string
     */
    private function writeValueToPhp($value, bool $setType = false): string
    {
        $type = 'all';

        if (is_string($value) && ! Str::contains($value, "'")) {
            $type = 'string';
            $replaceValue = "'".$value."'";
        } elseif (is_string($value) && ! Str::contains($value, '"') === false) {
            $type = 'string';
            $replaceValue = '"'.$value.'"';
        } elseif (is_bool($value)) {
            $type = 'constant';
            $replaceValue = ($value ? 'true' : 'false');
        } elseif (is_null($value)) {
            $type = 'constant';
            $replaceValue = 'null';
        } elseif (is_array($value) && count($value) === count($value, COUNT_RECURSIVE)) {
            $type = 'array';
            $replaceValue = $this->writeArrayToPhp($value);
        } else {
            $replaceValue = $value;
        }

        if ($setType) {
            $this->type = $type;
        }

        return str_replace('$', '\$', $replaceValue);
    }

    /**
     * @param array $array
     * @return string
     */
    private function writeArrayToPhp(array $array): string
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (! is_array($value)) {
                if (is_string($key)) {
                    $key = "'{$key}'";
                }

                $result[$key] = $this->writeValueToPhp($value);
            }
        }

        if (Arr::isAssoc($array)) {
            $result = array_map(function ($value, $key) {
                return "{$key} => {$value}";
            }, array_values($result), array_keys($result));
        }

        return '['.implode(', ', $result).']';
    }

    /**
     * @param string $targetKey
     * @param array $arrayItems
     * @param string $quoteChar
     * @return string
     */
    private function buildStringExpression(string $targetKey, array $arrayItems = [], string $quoteChar = "'"): string
    {
        $expression = [];

        // Opening expression for array items ($1)
        $expression[] = $this->buildArrayOpeningExpression($arrayItems);

        // The target key opening
        $expression[] = '([\'|"]'.$targetKey.'[\'|"]\s*=>\s*)(['.$quoteChar.']';

        // The target value to be replaced ($2)
        $expression[] = '([^'.$quoteChar.'].*)';

        // The target key closure
        $expression[] = '['.$quoteChar.']|';

        // The target key closure
        $expression[] = '['.$quoteChar.']['.$quoteChar.'])';

        return '/'.implode('', $expression).'/';
    }

    /**
     * Common constants only (true, false, null, integers).
     *
     * @param string $targetKey
     * @param array $arrayItems
     * @return string
     */
    private function buildConstantExpression(string $targetKey, array $arrayItems = []): string
    {
        $expression = [];

        // Opening expression for array items ($1)
        $expression[] = $this->buildArrayOpeningExpression($arrayItems);

        // The target key opening ($2)
        $expression[] = '([\'|"]'.$targetKey.'[\'|"]\s*=>\s*)';

        // The target value to be replaced ($3)
        $expression[] = '([tT][rR][uU][eE]|[fF][aA][lL][sS][eE]|[nN][uU][lL]{2}|[\d]+)';

        return '/'.implode('', $expression).'/';
    }

    /**
     * Single level arrays only.
     *
     * @param string $targetKey
     * @param array $arrayItems
     * @return string
     */
    private function buildArrayExpression(string $targetKey, array $arrayItems = []): string
    {
        $expression = [];

        // Opening expression for array items ($1)
        $expression[] = $this->buildArrayOpeningExpression($arrayItems);

        // The target key opening ($2)
        $expression[] = '([\'|"]'.$targetKey.'[\'|"]\s*=>\s*)';

        // The target value to be replaced ($3)
        $expression[] = '(?:[aA][rR]{2}[aA][yY]\(|[\[])([^\]|)]*)[\]|)]';

        return '/'.implode('', $expression).'/';
    }

    /**
     * @param array $arrayItems
     * @return string
     */
    private function buildArrayOpeningExpression(array $arrayItems): string
    {
        if (count($arrayItems)) {
            $itemOpen = [];
            foreach ($arrayItems as $item) {
                // The left hand array assignment
                $itemOpen[] = '[\'|"]'.$item.'[\'|"]\s*=>\s*(?:[aA][rR]{2}[aA][yY]\(|[\[])';
            }

            // Capture all opening array (non greedy)
            $result = '('.implode('[\s\S]*?', $itemOpen).'[\s\S]*?)';
        } else {
            // Gotta capture something for $1
            $result = '()';
        }

        return $result;
    }

    /**
     * @return bool
     */
    private function saveContent(): bool
    {
        $status = File::put($this->cfg, $this->contents, true);

        if ($status) {
            event(new WriteSuccess($this->nameCfg));
        }

        return $status;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function getContent(): self
    {
        if (! File::exists($this->cfg)) {
            throw new Exception(sprintf('Configuration file %s not found.', $this->nameCfg));
        }

        if (! File::isWritable($this->cfg)) {
            throw new Exception(sprintf('The config file %s does not support writing.', $this->nameCfg));
        }

        $this->contents = File::get($this->cfg);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function setCfg(): self
    {
        if (is_null($this->nameCfg)) {
            throw new Exception('Default file name not set.');
        }

        if (Str::endsWith($this->nameCfg, 'php')) {
            $this->cfg = config_path($this->nameCfg);
        } else {
            $this->cfg = config_path($this->nameCfg).'.php';
        }

        return $this;
    }
}
