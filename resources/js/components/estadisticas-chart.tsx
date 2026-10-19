import type { Intervalo } from '@/components/estadisticas-types';

export function TrendChart({
    intervals,
    values,
}: {
    intervals: Intervalo[];
    values: number[];
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
                aria-label="Evolución de creación de legajos"
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
