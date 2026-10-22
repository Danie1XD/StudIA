<?php

namespace App\Http\Requests\Entrega;

use App\Models\Entrega;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('entregar', [Entrega::class, $this->route('tarea')]);
    }

    public function rules(): array
    {
        return [
            'contenido' => ['nullable', 'string', 'max:20000'],   // enlace o comentario
            'archivo' => [
                'nullable', 'file', 'max:20480',                  // 20 MB
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg',
            ],
        ];
    }

    /**
     * Al terminar, la entrega debe tener un archivo o un texto. Se cuenta lo que ya tenía:
     * quien vuelve a entregar puede cambiar solo el texto, o solo el archivo.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($this->hasFile('archivo')) {
                    return;
                }

                $anterior = $this->route('tarea')->entregas()->where('user_id', $this->user()->id)->first();

                $texto = $this->has('contenido') ? $this->input('contenido') : $anterior?->contenido;

                if (blank($texto) && blank($anterior?->archivo_path)) {
                    $validator->errors()->add(
                        'contenido',
                        'Adjunta un archivo o escribe un enlace o comentario para entregar.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.mimes' => 'Formato no permitido. Usa PDF, Word, PowerPoint, Excel, ZIP o imagen.',
            'archivo.max' => 'El archivo no puede pesar más de 20 MB.',
            'archivo.uploaded' => 'No se pudo subir el archivo: puede superar el límite del servidor.',
        ];
    }
}