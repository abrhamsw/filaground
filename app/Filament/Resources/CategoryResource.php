<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\RelationManagers\ProductsRelationManager;
use Filament\Forms;
use Filament\Tables;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\CategoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CategoryResource\RelationManagers;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationGroup = 'Shop';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                Group::make()->schema([
                    Section::make([

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
                ]),

                //

                Group::make()->schema([
                    Section::make('Status')->schema([
                        Toggle::make('is_visible')->label('Visibility')->helperText('Enable or Disable Catagory visiblity')->default(true),
                        Select::make('parent_id')->relationship('parent','name'),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('parenet.name')->label('Parenet')->searchable()->sortable(),
                IconColumn::make('is_visible')->label('Visibility')->boolean()->sortable(),
                TextColumn::make('updated_at')->label("Updated Date")->date()->sortable(),
            ])
            ->filters([
                //
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
            ProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
