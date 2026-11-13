import type { Intervalo, NombreTotal } from '@/components/estadisticas-types';

type ChartSeries = {
    label: string;
    values: number[];
    color: string;
};

const seriesColors = ['#2C4A5E', '#39795B', '#A16A00', '#5B7080'];

export function TrendChart({
    intervals,
    values,
    label = 'Evolución',
    series,
}: {
    intervals: Intervalo[];
    values: number[];
    label?: string;
    series?: ChartSeries[];
}) {
    if (intervals.length === 0) {
        return (
            <div className="grid min-h-60 place-items-center rounded-md border border-dashed border-[#DCE3E6] bg-[#F8FAFA] px-5 text-center text-sm text-[#697586] dark:border-[#34424B] dark:bg-[#223039] dark:text-[#AFC2CC]">
                No hay datos para este período.
            </div>
        );
    }

    const chartSeries =
        series && series.length > 0
            ? series
            : [{ label, values, color: seriesColors[0] }];
    const width = 760;
    const height = 270;
    const padding = { top: 22, right: 24, bottom: 32, left: 42 };
    const chartHeight = height - padding.top - padding.bottom;
    const chartWidth = width - padding.left - padding.right;
    const maximum = Math.max(...chartSeries.flatMap((item) => item.values), 1);
    const step =
        intervals.length > 1 ? chartWidth / (intervals.length - 1) : chartWidth;
    const labelStep = Math.max(1, Math.ceil(intervals.length / 7));
    const valuesSummary = chartSeries
        .flatMap((item) =>
            intervals.map(
                (interval, index) =>
                    `${item.label}, ${interval.label}: ${item.values[index] ?? 0}`,
            ),
        )
        .join('. ');

    const point = (value: number, index: number): string => {
        const x =
            intervals.length > 1 ? padding.left + index * step : width / 2;
        const y = height - padding.bottom - (value / maximum) * chartHeight;

        return `${x},${y}`;
    };

    return (
        <div className="overflow-x-auto">
            <div className="mb-3 flex flex-wrap gap-x-5 gap-y-2 text-xs text-[#697586] dark:text-[#AFC2CC]">
                {chartSeries.map((item) => (
                    <span
                        key={item.label}
                        className="inline-flex items-center gap-2"
                    >
                        <span
                            className="size-2 rounded-full"
                            style={{ backgroundColor: item.color }}
                        />
                        {item.label}
                    </span>
                ))}
            </div>
            <svg
                viewBox={`0 0 ${width} ${height}`}
                className="h-auto w-full min-w-[620px]"
                role="img"
                aria-label={`${label}. ${valuesSummary}`}
            >
                {[0, 0.25, 0.5, 0.75, 1].map((fraction) => {
                    const y = height - padding.bottom - fraction * chartHeight;
                    const value = Math.round(maximum * fraction);

                    return (
                        <g key={fraction}>
                            <line
                                x1={padding.left}
                                x2={width - padding.right}
                                y1={y}
                                y2={y}
                                stroke="var(--stats-chart-grid)"
                                strokeDasharray="3 6"
                            />
                            <text
                                x={padding.left - 10}
                                y={y + 4}
                                textAnchor="end"
                                fill="var(--stats-chart-label)"
                                fontSize="10"
                            >
                                {value}
                            </text>
                        </g>
                    );
                })}

                {chartSeries.map((item) => {
                    const points = item.values
                        .map((value, index) => point(value, index))
                        .join(' ');

                    return (
                        <g key={item.label}>
                            <polyline
                                points={points}
                                fill="none"
                                stroke={item.color}
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2.5"
                            />
                            {item.values.map((value, index) => {
                                const [cx, cy] = point(value, index).split(',');

                                return (
                                    <circle
                                        key={`${item.label}-${intervals[index].key}`}
                                        cx={cx}
                                        cy={cy}
                                        r="3.5"
                                        fill="var(--stats-surface)"
                                        stroke={item.color}
                                        strokeWidth="2"
                                    >
                                        <title>
                                            {item.label}:{' '}
                                            {intervals[index].label} {value}
                                        </title>
                                    </circle>
                                );
                            })}
                        </g>
                    );
                })}

                {intervals.map((interval, index) => {
                    if (
                        index % labelStep !== 0 &&
                        index !== intervals.length - 1
                    ) {
                        return null;
                    }

                    const x =
                        intervals.length > 1
                            ? padding.left + index * step
                            : width / 2;

                    return (
                        <text
                            key={interval.key}
                            x={x}
                            y={height - 6}
                            textAnchor="middle"
                            fill="var(--stats-chart-label)"
                            fontSize="10"
                        >
                            {interval.label}
                        </text>
                    );
                })}
            </svg>
        </div>
    );
}

export function BarsChart({ items }: { items: NombreTotal[] }) {
    const visibleItems = items.slice(0, 10);
    const maximum = Math.max(...visibleItems.map((item) => item.total), 1);

    if (
        visibleItems.length === 0 ||
        visibleItems.every((item) => item.total === 0)
    ) {
        return (
            <p className="py-8 text-center text-sm text-[#697586] dark:text-[#AFC2CC]">
                No hay datos para este período.
            </p>
        );
    }

    return (
        <div
            className="space-y-3"
            role="list"
            aria-label="Distribución por categoría"
        >
            {visibleItems.map((item) => (
                <div key={item.nombre} role="listitem">
                    <div className="mb-1.5 flex justify-between gap-3 text-sm">
                        <span className="min-w-0 truncate font-medium text-[#2C4A5E] dark:text-[#D8E2E8]">
                            {item.nombre}
                        </span>
                        <strong className="shrink-0 text-[#39795B]">
                            {item.total.toLocaleString('es-AR')}
                        </strong>
                    </div>
                    <div className="h-2 overflow-hidden rounded-full bg-[#E7EFEC] dark:bg-[#30443A]">
                        <div
                            className="h-full rounded-full bg-[#39795B]"
                            style={{
                                width: `${(item.total / maximum) * 100}%`,
                            }}
                        />
                    </div>
                </div>
            ))}
        </div>
    );
}

export function DonutChart({ items }: { items: NombreTotal[] }) {
    const total = items.reduce((sum, item) => sum + item.total, 0);
    const colors = ['#2C4A5E', '#39795B', '#A16A00', '#5B7080', '#A8493C'];

    if (items.length === 0 || total === 0) {
        return (
            <p className="py-8 text-center text-sm text-[#697586] dark:text-[#AFC2CC]">
                No hay datos para este período.
            </p>
        );
    }

    return (
        <div className="flex flex-wrap items-center gap-6">
            <svg
                viewBox="0 0 42 42"
                className="size-32 -rotate-90"
                role="img"
                aria-label={`Distribución: ${items.map((item) => `${item.nombre} ${item.total}`).join(', ')}`}
            >
                <circle
                    cx="21"
                    cy="21"
                    r="15.9"
                    fill="none"
                    stroke="var(--stats-chart-grid)"
                    strokeWidth="6"
                />
                {items.map((item, index) => {
                    const length = (item.total / total) * 100;
                    const offset = items
                        .slice(0, index)
                        .reduce(
                            (sum, previous) =>
                                sum + (previous.total / total) * 100,
                            0,
                        );

                    return (
                        <circle
                            key={item.nombre}
                            cx="21"
                            cy="21"
                            r="15.9"
                            fill="none"
                            stroke={colors[index % colors.length]}
                            strokeWidth="6"
                            strokeDasharray={`${length} ${100 - length}`}
                            strokeDashoffset={-offset}
                        />
                    );
                })}
            </svg>
            <div className="min-w-[160px] flex-1 space-y-2 text-sm">
                {items.map((item, index) => (
                    <div
                        key={item.nombre}
                        className="flex items-center justify-between gap-4"
                    >
                        <span className="flex min-w-0 items-center gap-2">
                            <span
                                className="size-2.5 shrink-0 rounded-full"
                                style={{
                                    backgroundColor:
                                        colors[index % colors.length],
                                }}
                            />
                            <span className="truncate text-[#2C4A5E] dark:text-[#D8E2E8]">
                                {item.nombre}
                            </span>
                        </span>
                        <strong className="shrink-0 text-[#173247] dark:text-[#EDF6FB]">
                            {item.total.toLocaleString('es-AR')}
                        </strong>
                    </div>
                ))}
            </div>
        </div>
    );
}
