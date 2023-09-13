<?php

namespace Extra\Src\Job;

trait JobVisorTrait
{
    protected function fragment(mixed $element): void {}

    protected function visor(int $processQty, array $listElements): void
    {
        $processElements = $this->visorSort($processQty, $listElements);
        foreach ($processElements as $process)
            self::visorDispatch($process);
    }

    private function visorSort(int $processQty, array $listElements): array
    {
        $list = [];
        $count = count($listElements);
        for ($i = 0; $i < $count; $i++)
            $list[$i % $processQty][] = $listElements[$i];
        return $list;
    }

    public final function visorStart(array $process): void
    {
        foreach ($process as $element)
            $this->fragment($element);
    }

    public final static function visorDispatch(mixed $data = null): int
    {
        if ($data) {
            $fileName = uniqid("jobCache-process-");
            $filePath = PATH_CACHE . '/' . $fileName;
            file_put_contents($filePath, serialize($data));
            chmod($filePath, 0777);
        }
        return exec(sprintf(
            'php -q ../box job:visorRun %s %s > %s 2>&1 & echo $!',
            str_replace('\\', '\\\\', static::class),
            (($data) ? $fileName:''),
            "/dev/null"
        ));
    }
}