<?php

namespace App\Filament\Resources;

use App\Models\Club;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClubResource extends Resource
{
    protected static ?string $model = Club::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'Câu Lạc Bộ';
    
    protected static ?string $pluralModelLabel = 'Câu Lạc Bộ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ten')
                    ->label('Tên Câu Lạc Bộ')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('diachi')
                    ->label('Địa Chỉ')
                    ->maxLength(255),
                Forms\Components\TextInput::make('dienthoai')
                    ->label('Điện Thoại')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('thoigianhoc')
                    ->label('Thời Gian Học')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('img')
                    ->label('Hình Ảnh CLB')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('img')
                    ->label('Hình Ảnh'),
                Tables\Columns\TextColumn::make('ten')
                    ->label('Tên CLB')
                    ->searchable(),
                Tables\Columns\TextColumn::make('diachi')
                    ->label('Địa Chỉ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dienthoai')
                    ->label('SĐT')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        // For a simple setup, we can use a basic page structure, 
        // but typically it requires List/Create/Edit page classes.
        // We will mock them if needed or Filament can auto-generate.
        return [];
    }
}
