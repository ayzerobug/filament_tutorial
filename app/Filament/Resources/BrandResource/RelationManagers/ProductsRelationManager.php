<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use App\Enums\ProductTypeEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Models\Product;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Products')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Information')->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->live(onBlur: true)
                                ->unique()
                                ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                    if ($operation !== 'create') {
                                        return;
                                    }
                                    $set('slug', Str::slug($state));
                                }),
                            Forms\Components\TextInput::make('slug')->required()->disabled()->dehydrated()->unique(Product::class, 'slug', ignoreRecord: true),
                            Forms\Components\MarkdownEditor::make('description')->required()->columnSpan('full'),
                        ])->columns(2),
                        Forms\Components\Tabs\Tab::make('Pricing & Inventory')->schema([
                            Forms\Components\TextInput::make('sku')->label('SKU (Stock Keeping Unit)')->required()->unique(),
                            Forms\Components\TextInput::make('price')->numeric()->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')->required(),
                            Forms\Components\TextInput::make('quantity')->numeric()->minValue(0)->maxValue(500)->required(),
                            Forms\Components\Select::make('type')->options([
                                'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                                'deliverable' =>  ProductTypeEnum::DELIVERABLE->value
                            ])->required(),
                        ])->columns(2),
                        Forms\Components\Tabs\Tab::make('Additional Information')->schema([
                            Forms\Components\Toggle::make('is_visible')->label('Visibility')->helperText("Enable or disable product visibility")->default(true),
                            Forms\Components\Toggle::make('is_featured')->label('Featured')->helperText("Enable or disable product featured status"),
                            Forms\Components\DatePicker::make('published_at')->label('Availability')->default(now()),

                            Forms\Components\Select::make('categories')->relationship('categories', 'name')
                                ->multiple()
                                ->required(),

                            Forms\Components\FileUpload::make('image')->directory('form-attachments')->preserveFilenames()->image()->imageEditor()
                                ->columnSpanFull(),
                        ])->columns(2),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_visible')->boolean()->sortable()->toggleable()->label('Visibility'),
                TextColumn::make('quantity')->sortable()->toggleable(),
                TextColumn::make('price')->sortable()->toggleable(),
                TextColumn::make('published_at')->date()->sortable(),
                TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }
}
