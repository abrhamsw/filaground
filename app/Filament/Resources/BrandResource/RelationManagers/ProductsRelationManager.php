<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Tabs::make('Products')->tabs([
                    Tab::make('Information')->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->unique()
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {

                                if ($operation !== 'create') {
                                    return;
                                }

                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            // ->disabled()
                            ->required(),
                        // ->unique(Product::class, 'slug', ignoreRecord:true),

                        MarkdownEditor::make('description')
                            ->columnSpanFull(),

                    ])->columns(2),

                    Tab::make('Pricing & Inventory')->schema([
                        TextInput::make('sku')
                            ->label('SKU (Stock Keeping Unit)')
                            ->unique()
                            ->required(),
                        TextInput::make('price')
                            ->numeric()
                            ->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')
                            ->required(),

                        TextInput::make('quantity')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),

                        Select::make('type')->options(
                            [
                                'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                                'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                            ]
                        )->required(),
                    ])->columns(2),

                    Tab::make('Additional Information')->schema([

                        Toggle::make('is_visible')->label('Visibility')->helperText('Enable or Disable Product visiblity')->default(true),
                        Toggle::make('is_featured')->label('Featured')->helperText('Enable or Disable Product featured status'),
                        DatePicker::make('published_at')->label('Avilabilty')->default(now()),
                        // Relation
                        // If there is no relation created on the model the error arise
                        // Use From The URL
                        // Select::make('brand_id')->relationship('brand', 'name')->required(),

                        Select::make('categories')->relationship('categories', 'name')->multiple()->required(),

                        FileUpload::make('image')->directory('form-attachments')->preserveFilenames()->image()->imageEditor()->columnSpanFull(),

                    ])->columns(2),
                ])->columnSpanFull(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                // COPY PASTE FROM ProductResource.php
                ImageColumn::make('image'),
                TextColumn::make("name")->searchable()->sortable(),
                // From Forignkey
                // TextColumn::make("brand.name")->searchable()->sortable()->toggleable(),
                IconColumn::make('is_visible')->boolean()->sortable()->toggleable()->label('Visibility'),
                TextColumn::make("price")->sortable()->toggleable(),
                TextColumn::make("quantity")->sortable()->toggleable(),
                TextColumn::make("published_at")->sortable()->date(),
                TextColumn::make("type"),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
