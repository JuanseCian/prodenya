import type { Intervalo, NombreTotal } from '@/components/estadisticas-types';

export function TrendChart({
    intervals,
    values,
    label = 'Evolución',
}: {
    intervals: Intervalo[];
    values: number[];
    label?: string;
}) {
    if (intervals.length === 0) {
        return (
            <div className="grid min-h-64 place-items-center rounded-lg bg-[#F7F8FA] text-sm text-[#697586]">
                No hay datos para este período.
            </div>
        );
    }

    const width = 760;
    const height = 250;
    const paddingX = 28;
    const paddingY = 24;
    const chartHeight = height - paddingY * 2;
    const chartWidth = width - paddingX * 2;
    const max = Math.max(...values, 1);
    const step =
        intervals.length > 1 ? chartWidth / (intervals.length - 1) : chartWidth;
    const points = values
        .map((value, index) => {
            const x =
                intervals.length > 1 ? paddingX + index * step : width / 2;
            const y = height - paddingY - (value / max) * chartHeight;

            return `${x},${y}`;
        })
        .join(' ');
    const areaPoints = `${paddingX},${height - paddingY} ${points} ${width - paddingX},${height - paddingY}`;
    const labelStep = Math.max(1, Math.ceil(intervals.length / 7));

    return (
        <div className="overflow-x-auto">
            <svg
                viewBox={`0 0 ${width} ${height}`}
                className="h-auto w-full min-w-[620px]"
                role="img"
                aria-label={label}
            >
                {[0, 0.25, 0.5, 0.75, 1].map((fraction) => {
                    const y = height - paddingY - fraction * chartHeight;

                    return (
                        <line
                            key={fraction}
                            x1={paddingX}
                            x2={width - paddingX}
                            y1={y}
                            y2={y}
                            stroke="var(--stats-chart-grid)"
                            strokeDasharray="4 6"
                        />
                    );
                })}
                <polygon
                    points={areaPoints}
                    fill="#5B559E"
                    fillOpacity="0.07"
                />
                <polyline
                    points={points}
                    fill="none"
                    stroke="#5B559E"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="3"
                />
                {values.map((value, index) => {
                    const x =
                        intervals.length > 1
                            ? paddingX + index * step
                            : width / 2;
                    const y = height - paddingY - (value / max) * chartHeight;

                    return (
                        <circle
                            key={intervals[index].key}
                            cx={x}
                            cy={y}
                            r="4.5"
                            fill="var(--stats-surface)"
                            stroke="#5B559E"
                            strokeWidth="3"
                        />
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
                            ? paddingX + index * step
                            : width / 2;

                    return (
                        <text
                            key={interval.key}
                            x={x}
                            y={height - 3}
                            textAnchor="middle"
                            fill="var(--stats-chart-label)"
                            fontSize="11"
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
    const max = Math.max(...items.map((item) => item.total), 1);

    return (
        <div className="space-y-3">
            {items.length === 0 ? (
                <p className="py-8 text-center text-sm text-[#697586]">
                    No hay datos para este período.
                </p>
            ) : (
                items.slice(0, 10).map((item) => (
                    <div key={item.nombre}>
                        <div className="mb-1 flex justify-between gap-3 text-sm">
                            <span className="truncate font-semibold text-[#2C4A5E] dark:text-[#D8E2E8]">
                                {item.nombre}
                            </span>
                            <strong className="text-[#5B559E]">
                                {item.total.toLocaleString('es-AR')}
                            </strong>
                        </div>
                        <div className="h-2 overflow-hidden rounded-full bg-[#EEEAF9] dark:bg-[#38385A]">
                            <div
                                className="h-full rounded-full bg-[#5B559E]"
                                style={{
                                    width: `${(item.total / max) * 100}%`,
                                }}
                            />
                        </div>
                    </div>
                ))
            )}
        </div>
    );
}

export function DonutChart({ items }: { items: NombreTotal[] }) {
    const total = items.reduce((sum, item) => sum + item.total, 0);
    const colors = ['#5B559E', '#23845B', '#C66A10', '#1670AD', '#7652C7'];

    return (
        <div className="flex flex-wrap items-center gap-6">
            <svg
                viewBox="0 0 42 42"
                className="size-36 -rotate-90"
                role="img"
                aria-label="Distribución"
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
                    const length = total ? (item.total / total) * 100 : 0;
                    const offset = items
                        .slice(0, index)
                        .reduce(
                            (sum, previous) =>
                                sum +
                                (total ? (previous.total / total) * 100 : 0),
                            0,
                        );
                    const circle = (
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

                    return circle;
                })}
            </svg>
            <div className="min-w-[150px] space-y-2 text-sm">
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
                        <strong>{item.total.toLocaleString('es-AR')}</strong>
                    </div>
                ))}
            </div>
        </div>
    );
}
