<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_city' => $this->id_city,
            'id_club' => $this->id_club,
            'id_atg_members' => $this->id_atg_members, // HLV
            'ten' => $this->ten,
            'thoigian' => $this->thoigian,
            'gia' => $this->gia,
            'diachi' => $this->diachi,
            'dienthoai' => $this->dienthoai,
            'ten_club' => $this->ten_club,
        ];
    }
}
