<?php
namespace App\Services;

class Queries {
    public static function queries($query, $request): void
    {
        foreach ($request as $filter) {
            if (isset($filter['logic']) && $filter['logic'] == 'AND'){
                if (isset($filter['column'])){
                    $query = $query->where($filter['column'], $filter['value']);
                }
            }
            if (isset($filter['logic']) && $filter['logic'] == 'OR'){
                if (isset($filter['column'])){
                    $query = $query->orWhere($filter['column'], $filter['value']);
                }
            }
            if (isset($filter['logic']) && $filter['logic'] == 'AND_QUERY') {
                $query = $query->where(function($query) use ($filter){
                    foreach ($filter[0] as $filter1){
                        Queries::queries($query, $filter1);
                    }
                });
            }
            if (isset($filter['logic']) && $filter['logic'] == 'OR_QUERY') {
                $query = $query->orWhere(function($query) use ($filter){
                    foreach ($filter[0] as $filter1){
                        Queries::queries($query, $filter1);
                    }
                });
            }
        }
    }
}
