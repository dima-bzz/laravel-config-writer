<?php
namespace DimaBzz\LaravelConfigWriter\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Patterns
{
    /**
     * @param string $key
     * @param array $items
     * @return array
     */
    protected function getPatterns(string $key, array $items = []): array
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
    protected function writeValueToPhp($value, bool $setType = false): string
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
    protected function writeArrayToPhp(array $array): string
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
    protected function buildStringExpression(string $targetKey, array $arrayItems = [], string $quoteChar = "'"): string
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
    protected function buildConstantExpression(string $targetKey, array $arrayItems = []): string
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
    protected function buildArrayExpression(string $targetKey, array $arrayItems = []): string
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
    protected function buildArrayOpeningExpression(array $arrayItems): string
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
}