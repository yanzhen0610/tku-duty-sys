<?php

namespace App\EditTable;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;

class EditTable implements Responsable
{
    public function __construct(iterable $rows, iterable $fields,
        bool $editable, bool $destroyable, string $primary_key,
        string $store_route_name, string $update_route_name,
        string $destroy_route_name)
    {
        $this->rows = $rows;
        $this->fields = $fields;
        $this->editable = $editable;
        $this->destroyable = $destroyable;
        $this->primary_key = $primary_key;
        $this->store_route_name = $store_route_name;
        $this->update_route_name = $update_route_name;
        $this->destroy_route_name = $destroy_route_name;
    }

    public $rows;
    public $fields;
    public $editable;
    public $destroyable;
    public $primary_key;
    public $store_route_name;
    public $update_route_name;
    public $destroy_route_name;

    public static function singleRow($row, iterable $fields,
        string $primary_key,
        string $update_route_name = null,
        string $destroy_route_name = null)
    {
        $array_row = array();

        if ($row instanceof Model)
            $array_row = array_merge($array_row, $row->toArray());
        else if (is_array($row))
            $array_row = array_merge($array_row, $row);

        foreach ($fields as $key => $value)
            switch ($value['type'])
            {
                case 'dropdown':
                    $array_row[$key] = [
                        'selected' => $row->$key->getRouteKey(),
                    ];
                    break;
                default:
                    $array_row[$key] = $row[$key];
                    break;
            }

        $array_row['key'] = $row[$primary_key];
        $array_row['update_url'] = $update_route_name != null ?
            route($update_route_name, $row) : null;
        $array_row['destroy_url'] = $destroy_route_name != null ?
            route($destroy_route_name, $row) : null;

        return $array_row;
    }

    private function prepareRow($row)
    {
        return static::singleRow(
            $row,
            $this->fields,
            $this->primary_key,
            $this->update_route_name,
            $this->destroy_route_name
        );
    }

    public function toArray()
    {
        $rows = array();

        foreach ($this->rows as $row)
            array_push($rows, $this->prepareRow($row));

        return [
            'editable' => $this->editable,
            'destroyable' => $this->destroyable,
            'primary_key' => $this->primary_key,
            'create_url' => $this->store_route_name == null ?
                null : route($this->store_route_name),
            'fields' => $this->fields,
            'rows' => $rows,
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return response()->json($this->toArray());
    }
}
