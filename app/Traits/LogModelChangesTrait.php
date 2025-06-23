<?php

namespace App\Traits;

trait LogModelChangesTrait
{
    public function getChangedAttributesFromRequest($request)
    {
        // $data = collect($request->only($this->getFillable()));
        $data = $request instanceof \Illuminate\Http\Request
            ? collect($request->only($this->getFillable()))
            : collect($request); // assume array

        $changes = [];

        foreach ($data as $key => $value) {
            if ($this->$key != $value) {
                $changes[$key] = [
                    'old' => $this->$key,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }
}
