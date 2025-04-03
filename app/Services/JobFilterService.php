<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class JobFilterService
{
    protected Builder $query;
    protected FilterParser $parser;

    public function __construct(FilterParser $parser)
    {
        $this->parser = $parser;
    }

    public function apply(Builder $query, array|string $filters): Builder
    {
        $this->query = $query;
        if (is_string($filters)) {
            $filters = $this->parser->ParseConditions($filters);
        }
        $this->applyFilters($filters);
        return $this->query;
    }

    protected function applyFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->applyCondition(
                $filter['field'],
                $filter['operator'],
                $filter['value']
            );
        }
    }

    protected function applyCondition(string $field, string $operator, $value)
    {
        if (Str::startsWith($field, 'attribute:')) {
            $this->applyAttributeFilter($field, $operator, $value);
        } elseif ($field === 'languages') {
            $this->applyLanguageFilter($operator, $value);
        } elseif ($field === 'locations') {
            $this->applyLocationFilter($operator, $value);
        } else {
            $this->applyDirectFilter($field, $operator, $value);
        }
    }

    protected function applyAttributeFilter(string $field, string $operator, $value)
    {
        $attributeName = substr($field, 10);
        $this->query->whereHas('attributeValues', function($q) use ($attributeName, $operator, $value) {
            $q->whereHas('attribute', function($q) use ($attributeName) {
                $q->where('name', $attributeName);
            });

            switch ($operator) {
                case '=': case '!=': case '>': case '<': case '>=': case '<=':
                $q->where('value', $operator, $value);
                break;
            }
        });
    }

    protected function applyLanguageFilter(string $operator, $value)
    {
        if ($operator === 'HAS_ANY') {
            $this->query->whereHas('languages', function($q) use ($value) {
                $q->whereIn('name', $value);
            });
        }
    }

    protected function applyLocationFilter(string $operator, $value)
    {
        if ($operator === 'HAS_ANY') {
            $this->query->whereHas('locations', function($q) use ($value) {
                $q->where(function($query) use ($value) {
                    foreach ($value as $location) {
                        if ($location === 'Remote') {
                            $query->orWhere('is_remote', true);
                        } else {
                            $query->orWhere('city', $location)
                                ->orWhere('state', $location);
                        }
                    }
                });
            });
        }
    }

    protected function applyDirectFilter(string $field, string $operator, $value)
    {
        switch ($operator) {
            case '=': case '!=': case '>': case '<': case '>=': case '<=':
            $this->query->where($field, $operator, $value);
            break;
        }
    }
}
