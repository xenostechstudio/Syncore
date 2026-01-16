<div>
    {{-- Header Bar --}}
    <div class="sticky top-14 z-40 -mx-4 -mt-6 mb-6 flex min-h-[60px] items-center border-b border-zinc-200 bg-white px-4 py-2 sm:-mx-6 lg:-mx-8 lg:px-6 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="flex w-full items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-md font-light text-zinc-600 dark:text-zinc-400">Sales Reports</span>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="period" class="rounded-lg border-zinc-200 bg-white py-1.5 text-sm focus:border-zinc-400 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                    <option value="this_week">This Week</option>
                    <option value="last_week">Last Week</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_quarter">This Quarter</option>
                    <option value="this_year">This Year</option>
                </select>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Total Sales</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($summary['total_sales'] / 1000000, 1) }}M</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Total Orders</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['total_orders']) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Avg Order Value</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rp {{ number_format($summary['avg_order_value'] / 1000, 0) }}K</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Completed</p>
                <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($summary['completed_orders']) }}</p>
            </div>
        </div>

        {{-- Chart 1: Sales Trend (Line Chart) --}}
        <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Sales Trend</h2>
            </div>
            <div class="p-5">
                <div 
                    x-data="salesTrendChart(@js($chartData['salesTrend']))"
                    wire:ignore
                    class="relative h-72"
                >
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Chart 2: Order Status (Doughnut Chart) --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Order Status</h2>
                </div>
                <div class="p-5">
                    <div 
                        x-data="orderStatusChart(@js($chartData['orderStatus']))"
                        wire:ignore
                        class="relative h-64"
                    >
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>
            </div>

            {{-- Chart 3: Top Products (Bar Chart) --}}
            <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Top Products</h2>
                </div>
                <div class="p-5">
                    <div 
                        x-data="topProductsChart(@js($chartData['topProducts']))"
                        wire:ignore
                        class="relative h-64"
                    >
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    // Sales Trend Line Chart
    Alpine.data('salesTrendChart', (data) => ({
        chart: null,
        init() {
            const ctx = this.$refs.canvas.getContext('2d');
            const isDark = document.documentElement.classList.contains('dark');
            
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Sales',
                        data: data.values,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#10b981'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            grid: { color: isDark ? '#27272a' : '#f4f4f5' },
                            ticks: {
                                callback: (v) => v >= 1000000 ? 'Rp ' + (v/1000000).toFixed(1) + 'M' : 'Rp ' + (v/1000).toFixed(0) + 'K'
                            }
                        }
                    }
                }
            });
        }
    }));

    // Order Status Doughnut Chart
    Alpine.data('orderStatusChart', (data) => ({
        chart: null,
        init() {
            const ctx = this.$refs.canvas.getContext('2d');
            
            this.chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: ['#a1a1aa', '#3b82f6', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { boxWidth: 12, padding: 16 }
                        }
                    }
                }
            });
        }
    }));

    // Top Products Bar Chart
    Alpine.data('topProductsChart', (data) => ({
        chart: null,
        init() {
            const ctx = this.$refs.canvas.getContext('2d');
            const isDark = document.documentElement.classList.contains('dark');
            
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Sales',
                        data: data.values,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: isDark ? '#27272a' : '#f4f4f5' },
                            ticks: {
                                callback: (v) => v >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'K'
                            }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });
        }
    }));
</script>
@endscript
