<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static ?int $sort = 3;


    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();
        return [
            'datasets' => [
                [
                    'label' => "Blog posts created",
                    'data' => $data['productsPerMonth'] + [0, 0, 0]
                ]
            ],
            'labels' => $data['months']
        ];
    }

    private function getProductsPerMonth(): array
    {
        $now = Carbon::now();
        $productsPerMonth = [];
        $range = range(1, 12);
        foreach ($range as $month) {
            $count = Product::whereMonth('created_at', Carbon::parse($now->month($month)->format('Y-m')))->count();
            array_push($productsPerMonth, $count);
        }
        $months = collect($range)->map(function ($month) use ($now) {
            return $now->month($month)->format('M');
        })->toArray();

        return compact('productsPerMonth', 'months');
    }

    protected function getType(): string
    {
        return 'line';
    }
}
