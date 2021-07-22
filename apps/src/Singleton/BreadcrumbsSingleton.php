<?php

namespace Labstag\Singleton;

class BreadcrumbsSingleton
{

    protected array $data = [];

    protected static $instance = null;

    protected function __construct()
    {
    }

    public function add(array $breadcrumbs): void
    {
        foreach ($breadcrumbs as $key => $row) {
            $this->data[$key] = $row;
        }
    }

    public function addPosition(array $breadcrumbs, int $position): void
    {
        $newbreadcrumbs = [];
        $integer        = 0;
        foreach ($this->data as $key => $row) {
            $newbreadcrumbs[$key] = $row;
            if ($position === $integer) {
                foreach ($breadcrumbs as $newKey => $newRow) {
                    $newbreadcrumbs[$newKey] = $newRow;
                }
            }

            ++$integer;
        }

        $this->data = $newbreadcrumbs;
    }

    public function get(): array
    {
        return $this->data;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new BreadcrumbsSingleton();
        }

        return self::$instance;
    }

    public function set(array $breadcrumbs): void
    {
        $this->data = $breadcrumbs;
    }
}
