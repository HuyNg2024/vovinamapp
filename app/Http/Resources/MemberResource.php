<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'ten' => $this->ten,
            'dienthoai' => $this->dienthoai,
            'diachi' => $this->diachi,
            'ngaysinh' => $this->ngaysinh,
            'gioitinh' => $this->gioitinh,
            'hotengiamho' => $this->hotengiamho,
            'dienthoai_giamho' => $this->dienthoai_giamho,
            'chieucao' => $this->chieucao,
            'cannang' => $this->cannang,
            'id_club' => $this->id_club,
            'id_capdai' => $this->id_capdai,
            'deleted' => $this->deleted,
            'thietbi' => $this->thietbi,
            'hlv_flag' => $this->{'hlv flag'}, // it has a space in DB per docs?
        ];
    }
}
