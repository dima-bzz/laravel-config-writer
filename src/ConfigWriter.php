<?php

namespace DimaBzz\LaravelConfigWriter;

use DimaBzz\LaravelConfigWriter\Events\WriteSuccess;
use DimaBzz\LaravelConfigWriter\Exceptions\ConfigWriterException;
use DimaBzz\LaravelConfigWriter\Traits\Patterns;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ConfigWriter
{
    use Patterns;

    /**
     * @var string
     */
    protected string $contents;

    /**
     * @var string
     */
    protected string $configPath;

    /**
     * @var string
     */
    protected string $type = 'all';

    /**
     * @var null|string
     */
    protected ?string $configFile;

    /**
     * @var bool
     */
    protected bool $strictMode;

    /**
     * @var array
     */
    protected array $newData;

    public function __construct()
    {
        $this->configFile = config('config-writer.config_file');
        $this->strictMode = config('config-writer.strict', true);
    }

    /**
     * Set configuration file name.
     *
     * @param string $configFile
     * @return self
     */
    public function configFile(string $configFile): self
    {
        $this->configFile = $configFile;

        return $this;
    }

    /**
     * Set strict mode.
     *
     * @param bool $strictMode
     * @return self
     */
    public function strictMode(bool $strictMode): self
    {
        $this->strictMode = $strictMode;

        return $this;
    }

    /**
     * @param array|null
     * @return bool
     * @throws Exception
     */
    public function write($newData = null): bool
    {
        if (is_array($newData)) {
            $this->of($newData);
        }

        if (! isset($this->newData) || empty($this->newData)) {
            throw ConfigWriterException::requiredParamArray();
        }

        return $this->setConfigPath()
            ->getContent()
            ->setContent()
            ->saveContent();
    }

    /**
     * @param array $newData
     * @return $this
     */
    public function of(array $newData): self
    {
        $this->newData = $newData;

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function setContent(): self
    {
        $contents = $this->parseContent($this->newData);

        $result = eval('?>'.$contents);

        foreach ($this->newData as $key => $expectedValue) {
            $parts = explode('.', $key);

            $array = $result;
            foreach ($parts as $part) {
                if (! is_array($array) || ! array_key_exists($part, $array)) {
                    throw ConfigWriterException::keyDoesNotExist($key);
                }

                $array = $array[$part];
            }
            $actualValue = $array;

            if ($actualValue != $expectedValue) {
                throw ConfigWriterException::writeFailed($key);
            }
        }

        $this->contents = $contents;

        return $this;
    }

    /**
     * @param array $newData
     * @return string
     */
    protected function parseContent(array $newData): string
    {
        $result = $this->contents;

        foreach ($newData as $path => $value) {
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
    protected function parseContentValue(string $contents, string $path, $value): string
    {
        $result = $contents;
        $items = explode('.', $path);
        $key = array_pop($items);
        $replaceValue = $this->writeValueToPhp($value, true);

        $count = 0;
        $patterns = $this->getPatterns($key, $items);

        $newContent = $this->updateContent($patterns, $result, $replaceValue, $count);

        if (! $this->strictMode && $count === 0) {
            $this->type = 'all';
            $patterns = $this->getPatterns($key, $items);
            $newContent = $this->updateContent($patterns, $result, $replaceValue, $count);
        }

        return $newContent;
    }

    /**
     * @param array $patterns
     * @param string $result
     * @param string $replaceValue
     * @param $count
     * @return string
     */
    protected function updateContent(array $patterns, string $result, string $replaceValue, &$count): string
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
     * @return bool
     */
    protected function saveContent(): bool
    {
        $status = File::put($this->configPath, $this->contents, true);

        if ($status) {
            event(new WriteSuccess($this->configFile));
        }

        return $status;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function getContent(): self
    {
        if (! File::exists($this->configPath)) {
            throw ConfigWriterException::fileNotFound($this->configFile);
        }

        if (! File::isWritable($this->configPath)) {
            throw ConfigWriterException::fileNotWritable($this->configFile);
        }

        $this->contents = File::get($this->configPath);

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function setConfigPath(): self
    {
        if (is_null($this->configFile)) {
            throw ConfigWriterException::requiredDefaultFileName();
        }

        $this->configPath = Str::finish(config_path($this->configFile), '.php');

        return $this;
    }
}
