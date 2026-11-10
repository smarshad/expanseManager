<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DatabaseBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\DatabaseBackup::class);
    }

    public function rules(): array
    {
        return [
            'tables'      => ['required', 'array', 'min:1'],
            'tables.*'    => ['string'],
            'from'        => ['nullable', 'date'],
            'to'          => ['nullable', 'date'],
            'created_by'  => ['nullable', 'integer'],
        ];
    }
}
