<?php

namespace Labstag\Service;

use Symfony\Component\Finder\Finder;

class TemplatePageService
{
    public function getAll(string $namespace): array
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/../TemplatePage')->name('*.php');
        $plugins = [];
        foreach ($finder as $file) {
            $class_name = rtrim($namespace, '\\').'\\'.$file->getFilenameWithoutExtension();
            if (class_exists($class_name)) {
                $plugins[] = [
                    'name'    => $class_name,
                    'methods' => get_class_methods($class_name),
                ];
            }
        }

        return $plugins ?? [];
    }

    public function getChoices()
    {
        $namespace = 'Labstag\TemplatePage';
        $files     = $this->getAll($namespace);
        $choices   = [];
        foreach ($files as $row) {
            $name = $row['name'];
            foreach ($row['methods'] as $key) {
                if ('__construct' == $key) {
                    continue;
                }

                $code                                             = $name.'::'.$key.'()';
                $choices[str_replace($namespace.'\\', '', $code)] = $code;
            }
        }

        return $choices;
    }
}
