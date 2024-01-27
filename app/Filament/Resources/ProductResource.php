<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 2;


    protected static ?string $navigationGroup = 'Shop';

    protected static ?string $navigationLabel = 'Products';

    protected static int $globalSearchResultsLimit = 20;
    // protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {

        return ['name','slug','description'];
    }


    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            // 'Brand' => $record->brand->name,
            'Product' => $record->slug,
        ];
    }


    // public static function getGlobalSearchEloquentQuery(): Builder
    // {
    //     // return static::getEloquentQuery();
    //     // return parent::getGlobalSearchEloquentQuery()->with(['brand']);
    // }

    public static function getNavigationBadge(): ?string
    {
        //Return the model assosiated with this Class which is Product
        return static::getModel()::count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //First Grop
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur:true)
                                    ->unique()
                                    ->afterStateUpdated(function(string $operation, $state, Forms\Set $set){

                                        if($operation !== 'create'){
                                            return ;
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

                        // Second Section in the Same Group
                        Section::make('Pricing & Inventory')
                            ->schema([
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

                                Select::make('type')->options([
                                    'downloadable'=>ProductTypeEnum::DOWNLOADABLE->value,
                                    'deliverable'=>ProductTypeEnum::DELIVERABLE->value,
                                ]
                                )->required(),
                            ])->columns(2),

                    ]),


                //Second Grop
                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_visible')->label('Visibility')->helperText('Enable or Disable Product visiblity')->default(true),
                                Toggle::make('is_featured')->label('Featured')->helperText('Enable or Disable Product featured status'),
                                DatePicker::make('published_at')->label('Avilabilty')->default(now()),

                            ]),

                        // Second Section in the Same Group
                        Section::make('Image')
                            ->schema([
                                FileUpload::make('image')->directory('form-attachments')->preserveFilenames()->image()->imageEditor(),

                            ])->collapsible(),

                        // 3rd Section in the Same Group
                        Section::make('Associations')
                            ->schema([

                                // Relation
                                // If there is no relation created on the model the error arise
                                Select::make('brand_id')->relationship('brand', 'name')->required(),

                                Select::make('categories')->relationship('categories','name')->multiple()->required(),

                            ]),



                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                ImageColumn::make('image'),
                TextColumn::make("name")->searchable()->sortable(),
                // From Forignkey
                TextColumn::make("brand.name")->searchable()->sortable()->toggleable(),
                IconColumn::make('is_visible')->boolean()->sortable()->toggleable()->label('Visibility'),
                TextColumn::make("price")->sortable()->toggleable(),
                TextColumn::make("quantity")->sortable()->toggleable(),
                TextColumn::make("published_at")->sortable()->date(),
                TextColumn::make("type"),
            ])
            ->filters([
                //

                TernaryFilter::make('is_visible')->label('Visiblety')->boolean()->trueLabel('Only Visible Products')->falseLabel('Only Hidden Product')->native(false),

                SelectFilter::make('brand')->relationship('brand','name'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
