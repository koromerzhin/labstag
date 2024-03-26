<?php

namespace Labstag\Twig;

use Labstag\Lib\ExtensionLib;
use Twig\TwigFilter;

class DebugExtension extends ExtensionLib
{

    protected array $templates = [];

    public function debugBegin(array $data): string
    {
        if (0 == (is_countable($data) ? count($data) : 0)) {
            return '';
        }

        return $this->beginDebug($data);
    }

    public function debugBeginForm(mixed $class, string $state): string
    {
        $data = $this->getformClassData($class, $state);
        if (0 == (is_countable($data) ? count($data) : 0)) {
            return '';
        }

        return $this->beginDebug($data);
    }

    public function debugBeginPrototype(array $blockPrefixes, string $state): string
    {
        $data = $this->formPrototypeData($blockPrefixes, $state);
        if (0 == (is_countable($data) ? count($data) : 0)) {
            return '';
        }

        return $this->beginDebug($data);
    }

    public function debugEnd(array $data): string
    {
        if (0 == (is_countable($data) ? count($data) : 0)) {
            return '';
        }

        return $this->endDebug($data);
    }

    public function debugEndForm(mixed $class, string $state): string
    {
        $data = $this->getformClassData($class, $state);
        if (0 == (is_countable($data) ? count($data) : 0)) {
            return '';
        }

        return $this->endDebug($data);
    }

    public function debugEndPrototype(array $blockPrefixes, string $state): string
    {
        $data = $this->formPrototypeData($blockPrefixes, $state);
        if (0 == (is_countable($data) ? count($data) : 0)) {
            return '';
        }

        return $this->endDebug($data);
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'debug_begin_prototype',
                fn (array $blockPrefixes, $state): string => $this->debugBeginPrototype($blockPrefixes, $state),
                ['is_safe' => ['all']]
            ),
            new TwigFilter(
                'debug_end_prototype',
                fn (array $blockPrefixes, $state): string => $this->debugEndPrototype($blockPrefixes, $state),
                ['is_safe' => ['all']]
            ),
            new TwigFilter(
                'debug_begin_form',
                fn ($class, $state): string => $this->debugBeginForm($class, $state),
                ['is_safe' => ['all']]
            ),
            new TwigFilter(
                'debug_end_form',
                fn ($class, $state): string => $this->debugEndForm($class, $state),
                ['is_safe' => ['all']]
            ),
            new TwigFilter(
                'debug_begin',
                fn (array $data): string => $this->debugBegin($data),
                ['is_safe' => ['all']]
            ),
            new TwigFilter(
                'debug_end',
                fn (array $data): string => $this->debugEnd($data),
                ['is_safe' => ['all']]
            ),
        ];
    }

    private function beginDebug(array $data): string
    {
        $html = "<!--\n\tTHEME DEBUG\n";
        $html .= "\tTHEME HOOK : '".$data['hook']."'\n";
        if (0 != (is_countable($data['files']) ? count($data['files']) : 0)) {
            $html .= "\tFILE NAME SUGGESTIONS: \n";
            foreach ($data['files'] as $file) {
                $html .= str_repeat("\t", 2).(($data['view'] == $file) ? 'x' : '*').' '.$file."\n";
            }
        }

        return $html.("\tBEGIN OUTPUT from '".$data['view']."'\n-->\n");
    }

    private function endDebug(array $data): string
    {
        return "\n<!-- END OUTPUT from '".$data['view']."' -->\n";
    }
}
