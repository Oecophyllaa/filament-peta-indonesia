<x-filament-panels::page>
    {{ $this->form }}
    <div wire:ignore>
        <div id="container" style="width: 100%; height: 600px;"></div>
    </div>
    @push('scripts')
        <script>
            let chart;
            let currentMapType = @json($this->data['selectedMapType'] ?? 'user_count');

            function initializeMap() {
                const geojson = @json($geoJson);
                const data = @json($this->getMapData());

                chart = Highcharts.mapChart('container', getChartOptions(geojson, data, currentMapType));
            }

            function getChartOptions(geojson, data, mapType) {
                return {
                    chart: {
                        map: geojson
                    },
                    title: {
                        text: getTitle(mapType)
                    },
                    subTitle: {
                        text: ''
                    },
                    mapNavigation: {
                        enabled: true,
                        buttonOptions: {
                            verticalAlign: 'bottom'
                        }
                    },
                    colorAxis: getColorAxis(mapType),
                    series: [{
                        data: data,
                        joinBy: ['id', 'id'],
                        name: getName(mapType),
                        states: {
                            hover: {
                                color: '#a4edba'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}'
                        },
                        tooltip: {
                            pointFormat: getTooltipFormat(mapType)
                        }
                    }]
                }
            }

            function getTitle(mapType) {
                return mapType === 'user_count' ?
                    'User Count by Province in Indonesia' :
                    'Average User Age by Province in Indonesia';
            }

            function getName(mapType) {
                return mapType === 'user_count' ? 'User Count' : 'Average Age';
            }

            function getColorAxis(mapType) {
                return mapType === 'user_count' ? {
                    min: 0,
                    minColor: '#E6E7E8',
                    maxColor: '#005645',
                } : {
                    min: 0,
                    minColor: '#E6E7E8',
                    maxColor: '#9C1373FF',
                };
            }

            function getTooltipFormat(mapType) {
                return mapType === 'user_count' ?
                    '{point.name}: {point.value} users' :
                    '{point.name}: {point.value:.2f} years'
            }

            function updateMap(mapType) {
                if (currentMapType !== mapType) {
                    currentMapType = mapType;
                    @this.getMapData().then(data => {
                        const newOptions = getChartOptions(@json($geoJson), data, mapType);
                        chart.update(newOptions, true, true);
                    })
                }
            }

            document.addEventListener('livewire:init', () => {
                initializeMap();

                Livewire.on('updateMap', (data) => {
                    updateMap(data.mapType);
                })
            })
        </script>
    @endpush
</x-filament-panels::page>
