import L from 'leaflet';
import { useEffect, useRef, useState } from 'react';
import 'leaflet/dist/leaflet.css';

import type { EstadisticasData, Problematica } from '@/components/estadisticas-types';

type Props = {
    data: EstadisticasData['mapaBarrios'];
    problematicas: Problematica[];
};

const SAN_NICOLAS_CENTER: L.LatLngExpression = [-33.334, -60.225];
const COLORS = ['#5B559E', '#397461', '#C66A10', '#2C7A9B', '#A8493C', '#7B5EA7'];

function escapeHtml(value: string): string {
    return value.replace(
        /[&<>'"]/g,
        (character) =>
            ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#039;',
                '"': '&quot;',
            })[character] ?? character,
    );
}

export default function EstadisticasMapa({ data, problematicas }: Props) {
    const mapContainer = useRef<HTMLDivElement>(null);
    const map = useRef<L.Map | null>(null);
    const layers = useRef<L.LayerGroup | null>(null);
    const availableIds = problematicas
        .filter((problematica) => problematica.total > 0)
        .map((problematica) => problematica.id);
    const [selectedIds, setSelectedIds] = useState<number[] | null>(null);
    const visibleIds = selectedIds ?? availableIds;

    useEffect(() => {
        if (!data.disponible || !mapContainer.current || map.current) {
            return;
        }

        map.current = L.map(mapContainer.current, {
            center: SAN_NICOLAS_CENTER,
            zoom: 13,
            scrollWheelZoom: false,
            zoomControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map.current);

        return () => {
            map.current?.remove();
            map.current = null;
            layers.current = null;
        };
    }, [data.disponible]);

    useEffect(() => {
        if (!map.current || !data.disponible) {
            return;
        }

        layers.current?.remove();
        layers.current = L.layerGroup().addTo(map.current);
        const activeIds = new Set(visibleIds);
        const bounds: L.LatLngTuple[] = [];

        data.zonas.forEach((zona) => {
            const zoneProblems = zona.problematicas.filter((problematica) =>
                activeIds.has(problematica.id),
            );

            if (zoneProblems.length === 0) {
                return;
            }

            zoneProblems.forEach((problematica) => {
                const color = COLORS[problematica.id % COLORS.length];
                const circle = L.circleMarker([zona.latitud, zona.longitud], {
                    color,
                    fillColor: color,
                    fillOpacity: 0.45,
                    opacity: 0.85,
                    radius: Math.min(26, 8 + problematica.total * 2),
                    weight: 2,
                });

                circle.bindPopup(
                    `<div class="stats-map-popup"><strong>${escapeHtml(problematica.nombre)}</strong><span>${problematica.total} casos agregados</span><small>Zona protegida: no se muestran domicilios ni personas.</small></div>`,
                );
                circle.addTo(layers.current as L.LayerGroup);
                bounds.push([zona.latitud, zona.longitud]);
            });
        });

        if (bounds.length > 0) {
            map.current.fitBounds(bounds, { padding: [24, 24], maxZoom: 14 });
        }
    }, [data, visibleIds]);

    if (!data.disponible) {
        return (
            <div className="flex min-h-72 items-center justify-center rounded-xl border border-dashed border-[#DCE3DF] bg-[#F8FAF9] p-6 text-center text-sm text-[#697586] dark:border-[#3B4552] dark:bg-[#2C333E] dark:text-[#B7C9D4]">
                {data.mensaje}
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-center gap-2">
                {problematicas
                    .filter((problematica) => problematica.total > 0)
                    .map((problematica) => {
                        const active = visibleIds.includes(problematica.id);
                        const color = COLORS[problematica.id % COLORS.length];

                        return (
                            <label
                                key={problematica.id}
                                className={`inline-flex cursor-pointer items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition ${active ? 'border-[#D4D0ED] bg-[#F4F2FC] text-[#514B9F] dark:border-[#625CAA] dark:bg-[#38385A] dark:text-[#D8D3FF]' : 'border-[#DFE4EA] bg-white text-[#8A96A3] dark:border-[#3B4552] dark:bg-[#2C333E]'}`}
                            >
                                <input
                                    type="checkbox"
                                    checked={active}
                                    onChange={() =>
                                        setSelectedIds((current) => {
                                            const selected = current ?? availableIds;

                                            return active
                                                ? selected.filter((id) => id !== problematica.id)
                                                : [...selected, problematica.id];
                                        })
                                    }
                                    className="sr-only"
                                />
                                <span
                                    className="size-2.5 rounded-full"
                                    style={{ backgroundColor: color }}
                                />
                                {problematica.nombre}
                            </label>
                        );
                    })}
            </div>
            <div className="relative overflow-hidden rounded-xl border border-[#DCE3DF] dark:border-[#3B4552]">
                <div ref={mapContainer} className="h-[440px] w-full" />
                <div className="pointer-events-none absolute bottom-3 left-3 rounded-lg bg-white/90 px-3 py-2 text-[0.68rem] text-[#697586] shadow-sm dark:bg-[#20262F]/90 dark:text-[#B7C9D4]">
                    {data.mensaje}
                </div>
            </div>
        </div>
    );
}
