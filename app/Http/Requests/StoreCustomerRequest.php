<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk melakukan request ini.
     */
    public function authorize(): bool
    {
        return true; // Ubah menjadi true agar bisa digunakan
    }

    /**
     * Aturan validasi yang akan diterapkan pada request ini.
     */
    public function rules(): array
    {
        return [
            'types_id'    => 'required',
            'name'        => 'required|string|max:255',
            'email'       => 'required',
            'telp'        => 'required|numeric|digits_between:10,15',
            'mac_address' => 'required|string',
            'routers_id'  => 'required',
            'hometowns_id' => 'required',
            'villages_id'  => 'required',
            'rts_id'       => 'required',
            'rws_id'       => 'required',
            'districts_id' => 'required',
            'regencies_id' => 'required',
            'vlans_id'     => 'required',
            'odcs_id'      => 'required',
            'odps_id'      => 'required',
            'olts_id'      => 'required',
            'status'       => "required"
        ];
    }

    /**
     * Pesan error kustom untuk setiap aturan.
     */
    public function messages(): array
    {
        return [
            'types_id.required'   => 'Type pelanggan wajib dipilih.',
            'name.required'       => 'Nama pelanggan wajib diisi.',
            'email.required'      => 'Email pelanggan wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email sudah terdaftar.',
            'telp.required'       => 'Nomor telepon wajib diisi.',
            'telp.numeric'        => 'Nomor telepon harus berupa angka.',
            'mac_address.regex'   => 'Format MAC Address tidak valid.',
            'routers_id.required' => 'Jenis router wajib dipilih.',
            'hometowns_id.required' => 'Kampung wajib dipilih.',
            'villages_id.required'  => 'Desa wajib dipilih.',
            'rts_id.required'       => 'RT wajib dipilih.',
            'rws_id.required'       => 'RW wajib dipilih.',
            'districts_id.required' => 'Kecamatan wajib dipilih.',
            'regencies_id.required' => 'Kabupaten/Kota wajib dipilih.',
        ];
    }
}
