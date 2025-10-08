<?php

namespace App\Http\Requests;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Sesuaikan dengan logic otorisasi Anda. Untuk saat ini, kita set true.
        // Anda mungkin ingin memeriksa apakah user memiliki role 'SUPERADMIN' atau 'ADMIN' sekolah.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Mendapatkan ID sekolah yang sedang diupdate (dari model School::first())
        // Kita perlu memastikan name dan npsn unik, kecuali untuk ID sekolah ini sendiri.
        $schoolId = $this->route('school') ? $this->route('school')->id : School::first()->id;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:schools,name,' . $schoolId],
            'npsn' => ['nullable', 'string', 'max:255', 'unique:schools,npsn,' . $schoolId],
            'address' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'place_date_raport' => ['nullable', 'string', 'max:255'],
            'place_date_sts' => ['nullable', 'string', 'max:255'],
        ];
    }
}
