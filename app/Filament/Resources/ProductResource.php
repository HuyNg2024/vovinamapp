<?php

namespace App\Filament\Resources;

use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    
    protected static ?string $navigationLabel = 'Cửa Hàng (Sản Phẩm)';
    
    protected static ?string $pluralModelLabel = 'Sản Phẩm';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ProductName')
                    ->label('Tên Sản Phẩm')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('UnitPrice')
                    ->label('Giá Bán (VNĐ)')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('UnitsInStock')
                    ->label('Tồn Kho')
                    ->numeric()
                    ->required(),
                Forms\Components\FileUpload::make('link_image')
                    ->label('Hình Ảnh Sản Phẩm')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('link_image')
                    ->label('Hình Ảnh'),
                Tables\Columns\TextColumn::make('ProductName')
                    ->label('Tên Sản Phẩm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('UnitPrice')
                    ->label('Giá (VNĐ)')
                    ->money('VND')
                    ->sortable(),
                Tables\Columns\TextColumn::make('UnitsInStock')
                    ->label('Tồn Kho')
                    ->sortable(),
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
        return [];
    }
}
