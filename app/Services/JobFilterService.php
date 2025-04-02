<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Str;

class JobFilterService
{
    protected $query;
    protected $filterParams;

    public function apply(Job $query, array $filterParams)
    {
        $this->query = $query;
        $this->filterParams = $filterParams;

        foreach ($this->filterParams as $filter) {
            $this->applyFilter($filter);
        }

        return $this->query;
    }

    protected function applyFilter(array $filter)
    {
        $field = $filter['field'];
        $operator = $filter['operator'];
        $value = $filter['value'];

        if (Str::startsWith($field, 'attribute:')) {
            $this->applyEavFilter($field, $operator, $value);
        } elseif (in_array($field, ['languages', 'locations', 'categories'])) {
            $this->applyRelationshipFilter($field, $operator, $value);
        } else {
            $this->applyStandardFilter($field, $operator, $value);
        }
    }

    protected function applyStandardFilter($field, $operator, $value)
    {
        switch ($operator) {
            case '=':
                $this->query->where($field, $value);
                break;
            case '!=':
                $this->query->where($field, '!=', $value);
                break;
            case '>':
                $this->query->where($field, '>', $value);
                break;
            case '<':
                $this->query->where($field, '<', $value);
                break;
            case '>=':
                $this->query->where($field, '>=', $value);
                break;
            case '<=':
                $this->query->where($field, '<=', $value);
                break;
            case 'LIKE':
                $this->query->where($field, 'LIKE', "%{$value}%");
                break;
            case 'IN':
                $this->query->whereIn($field, $value);
                break;
        }
    }

    protected function applyRelationshipFilter($relation, $operator, $value)
    {
        switch ($operator) {
            case 'HAS_ANY':
                $this->query->whereHas($relation, function($q) use ($value) {
                    $q->whereIn('name', $value);
                });
                break;
            case 'IS_ANY':
                $this->query->whereHas($relation, function($q) use ($value) {
                    $q->whereIn('id', $value);
                });
                break;
            case 'EXISTS':
                $this->query->whereHas($relation);
                break;
        }
    }

    protected function applyEavFilter($field, $operator, $value)
    {
        $attributeName = str_replace('attribute:', '', $field);

        $this->query->whereHas('attributeValues', function($q) use ($attributeName, $operator, $value) {
            $q->whereHas('attribute', function($q) use ($attributeName) {
                $q->where('name', $attributeName);
            });

            switch ($operator) {
                case '=':
                    $q->where('value', $value);
                    break;
                case '!=':
                    $q->where('value', '!=', $value);
                    break;
                case '>':
                    $q->where('value', '>', $value);
                    break;
                case '<':
                    $q->where('value', '<', $value);
                    break;
                case '>=':
                    $q->where('value', '>=', $value);
                    break;
                case '<=':
                    $q->where('value', '<=', $value);
                    break;
                case 'LIKE':
                    $q->where('value', 'LIKE', "%{$value}%");
                    break;
                case 'IN':
                    $q->whereIn('value', $value);
                    break;
            }
        });
    }

    public function parseFilterString(string $filterString): array
    {
        $filterString = trim($filterString);
        $filters = [];
        $currentPos = 0;
        $length = strlen($filterString);

        while ($currentPos < $length) {
            $char = $filterString[$currentPos];

            if ($char === '(') {
                $endPos = $this->findMatchingClosingParenthesis($filterString, $currentPos);
                $groupString = substr($filterString, $currentPos + 1, $endPos - $currentPos - 1);
                $groupFilters = $this->parseFilterString($groupString);

                $nextPart = substr($filterString, $endPos + 1);
                $logicalOperator = $this->extractLogicalOperator($nextPart);

                if ($logicalOperator) {
                    $groupFilters['logical'] = strtolower($logicalOperator);
                    $currentPos = $endPos + strlen($logicalOperator) + 1;
                } else {
                    $currentPos = $endPos + 1;
                }

                $filters[] = $groupFilters;
            } else {
                $condition = $this->extractCondition($filterString, $currentPos);
                if ($condition) {
                    $parsedCondition = $this->parseCondition($condition['condition']);

                    if (isset($condition['logical_operator'])) {
                        $parsedCondition['logical'] = strtolower($condition['logical_operator']);
                    }

                    $filters[] = $parsedCondition;
                    $currentPos += strlen($condition['full_match']);
                } else {
                    $currentPos++;
                }
            }
        }

        return $filters;
    }

    protected function findMatchingClosingParenthesis(string $string, int $startPos): int
    {
        $openCount = 1;
        $currentPos = $startPos + 1;
        $length = strlen($string);

        while ($currentPos < $length && $openCount > 0) {
            $char = $string[$currentPos];
            if ($char === '(') {
                $openCount++;
            } elseif ($char === ')') {
                $openCount--;
            }
            $currentPos++;
        }

        return $currentPos - 1;
    }

    protected function extractLogicalOperator(string $string): ?string
    {
        if (preg_match('/^\s*(AND|OR)\s*/i', $string, $matches)) {
            return strtoupper($matches[1]);
        }
        return null;
    }

    protected function extractCondition(string $string, int $startPos): ?array
    {
        $pattern = '/(?<condition>[^\s()]+(\s*[^\s()]+\s*[^\s()]+)?)(?:\s*(?<logical_operator>AND|OR)\s*|$)/i';

        if (preg_match($pattern, substr($string, $startPos), $matches)) {
            return [
                'condition' => trim($matches['condition']),
                'logical_operator' => isset($matches['logical_operator']) ? $matches['logical_operator'] : null,
                'full_match' => $matches[0]
            ];
        }

        return null;
    }

    protected function parseCondition(string $condition): array
    {
        if (preg_match('/^(languages|locations|categories)\s+(HAS_ANY|IS_ANY|EXISTS)\s*\(([^)]+)\)$/i', $condition, $matches)) {
            $values = array_map('trim', explode(',', $matches[3]));
            return [
                'field' => strtolower($matches[1]),
                'operator' => strtoupper($matches[2]),
                'value' => $values
            ];
        }

        if (preg_match('/^([^\s=><!]+)\s*(=|!=|>|<|>=|<=|LIKE|IN)\s*(.+)$/i', $condition, $matches)) {
            $field = $matches[1];
            $operator = strtoupper($matches[2]);
            $value = $matches[3];

            if ($operator === 'IN' && preg_match('/^\(([^)]+)\)$/', $value, $inMatches)) {
                $value = array_map('trim', explode(',', $inMatches[1]));
            }

            if (strpos($field, 'attribute:') === 0) {
                $field = 'attribute:' . substr($field, 10);
            }

            return [
                'field' => $field,
                'operator' => $operator,
                'value' => $value
            ];
        }

        throw new \InvalidArgumentException("Invalid condition format: {$condition}");
    }
}
