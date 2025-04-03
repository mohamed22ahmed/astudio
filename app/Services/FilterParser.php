<?php

namespace App\Services;

class FilterParser
{
    public function ParseConditions(string $url)
    {
        preg_match('/\?filter=\((.*)\)/', $url);
        if ($url) {
            $filterString = $url;
            $result = [];

            $conditions = preg_split('/\s+AND\s+/i', $filterString);

            foreach ($conditions as $condition) {
                if (preg_match('/([a-zA-Z0-9:_\-]+)\s*(=|HAS_ANY|>=)\s*(.*)/', $condition, $match)) {
                    $field = $match[1];
                    $operator = $match[2];
                    $value = $match[3];

                    if ($operator === 'HAS_ANY') {
                        $valueArray = explode(',', trim($value, '()'));
                        $valueArray = array_map('trim', $valueArray);  // Clean up spaces
                        $result[] = [
                            'field' => $field,
                            'operator' => $operator,
                            'value' => $valueArray
                        ];
                    } else {
                        $result[] = [
                            'field' => $field,
                            'operator' => $operator,
                            'value' => $value
                        ];
                    }
                }
            }

            return $result;
        }

        return null;
    }
}
