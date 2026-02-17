@props(['title', 'labels' => [], 'data' => [], 'color' => 'indigo'])
<div class="bg-white rounded-lg shadow p-4 border-l-4 border-{{$color}}-500">
    <div class="font-semibold text-gray-800 mb-2">{{ $title }}</div>
    <canvas x-ref="chart"></canvas>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chartWidget', () => ({
                init() {
                    const ctx = this.$refs.chart.getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($labels),
                            datasets: [{
                                label: '{{ $title }}',
                                data: @json($data),
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99,102,241,0.1)',
                                fill: true,
                            }]
                        },
                        options: {responsive: true, plugins: {legend: {display: false}}}
                    });
                }
            }));
        });
    </script>
</div>