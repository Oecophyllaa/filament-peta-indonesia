<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Maps extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.maps';

    public ?array $data = ['selectedMapType' => 'user_count'];
    public $geoJson;
    public $mapData;

    public function mount(): void
    {
        FilamentAsset::register([
            Js::make('highmaps', 'https://code.highcharts.com/maps/highmaps.js'),
            Js::make('highcharts-exporting', 'https://code.highcharts.com/maps/modules/exporting.js'),
        ]);

        $response = Http::get('https://jfraziz.github.io/idn/api/38-kemendagri.json');
        $this->geoJson = $response->json();
        $this->fetchMapData();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedMapType')
                    ->label('Select Map Type')
                    ->options([
                        'user_count' => 'User Count',
                        'avg_age' => 'Average Age',
                    ])
                    ->default('user_count')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->data['selectedMapType'] = $state;
                        $this->dispatch('updateMap', mapType: $state);
                    })
            ])
            ->statePath('data');
    }

    public function fetchMapData()
    {
        $this->mapData = User::join('provinces', 'users.province_id', '=', 'provinces.id')
            ->select(
                'provinces.code',
                DB::raw('count(*) as user_count'),
                DB::raw('ROUND(AVG(CASE WHEN age > 0 THEN age ELSE NULL END), 2) as avg_age')
            )
            ->groupBy('provinces.code')
            ->get()
            ->keyBy('code')
            ->toArray();
    }

    public function getMapData(): array
    {
        $selectedMapType = $this->data['selectedMapType'] ?? 'user_count';
        return collect($this->geoJson['features'])->map(function ($feature) use ($selectedMapType) {
            $provinceCode = $feature['properties']['id'];
            $provinceName = $feature['properties']['name'];

            $data = $this->mapData[$provinceCode] ?? null;

            $value = 0;
            if ($data) {
                if ($selectedMapType === 'user_count') {
                    $value = $data['user_count'] ?? 0;
                } else if ($selectedMapType === 'avg_age') {
                    $value = $data['avg_age'] ?? 0;
                }
            }

            return [
                'id' => $provinceCode,
                'name' => $provinceName,
                'value' => $value
            ];
        })->toArray();
    }
}
