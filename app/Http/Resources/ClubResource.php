<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_city' => $this->id_city,
            'id_district' => $this->id_district,
            'ten' => $this->ten,
            'diachi' => $this->diachi,
            'dienthoai' => $this->dienthoai,
            'thoigianhoc' => $this->thoigianhoc,
            'map_lat' => $this->map_lat,
            'map_long' => $this->map_long,
            'img' => $this->img,
            'bank_qrcode' => $this->bank_qrcode,
            'image' => $this->image,
            'id_atg_members' => $this->id_atg_members, // HLV
            'tenkhongdau' => $this->tenkhongdau,
            'email' => $this->email,
            'hienthi' => $this->hienthi,
        ];
    }
}
