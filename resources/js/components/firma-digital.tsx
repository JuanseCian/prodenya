import { Eraser, PenLine } from 'lucide-react';
import { useEffect, useRef } from 'react';
import type { PointerEvent } from 'react';

interface Props {
    value: string;
    onChange: (value: string) => void;
    label: string;
}

export default function FirmaDigital({ value, onChange, label }: Props) {
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const dibujando = useRef(false);

    useEffect(() => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return;
        }

        const rect = canvas.getBoundingClientRect();
        const ratio = window.devicePixelRatio || 1;
        const contexto = canvas.getContext('2d');

        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        contexto?.scale(ratio, ratio);

        if (contexto) {
            contexto.strokeStyle = '#2C4A5E';
            contexto.lineWidth = 2;
            contexto.lineCap = 'round';
            contexto.lineJoin = 'round';
        }
    }, []);

    const posicion = (event: PointerEvent<HTMLCanvasElement>) => {
        const canvas = canvasRef.current;
        const rect = canvas?.getBoundingClientRect();

        if (!rect) {
            return null;
        }

        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top,
        };
    };

    const comenzar = (event: PointerEvent<HTMLCanvasElement>) => {
        const canvas = canvasRef.current;
        const contexto = canvas?.getContext('2d');
        const punto = posicion(event);

        if (!contexto || !punto) {
            return;
        }

        canvas?.setPointerCapture(event.pointerId);
        contexto.beginPath();
        contexto.moveTo(punto.x, punto.y);
        dibujando.current = true;
    };

    const dibujar = (event: PointerEvent<HTMLCanvasElement>) => {
        if (!dibujando.current) {
            return;
        }

        const contexto = canvasRef.current?.getContext('2d');
        const punto = posicion(event);

        if (!contexto || !punto) {
            return;
        }

        contexto.lineTo(punto.x, punto.y);
        contexto.stroke();
        onChange(canvasRef.current?.toDataURL('image/png') ?? '');
    };

    const limpiar = () => {
        const canvas = canvasRef.current;
        const contexto = canvas?.getContext('2d');

        if (!canvas || !contexto) {
            return;
        }

        contexto.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);
        onChange('');
    };

    return (
        <div>
            <div className="mb-2 flex items-center justify-between gap-3">
                <p className="flex items-center gap-1.5 text-xs font-semibold tracking-[0.08em] text-[#6B8F71] uppercase">
                    <PenLine size={14} />
                    {label}
                </p>
                <button
                    type="button"
                    onClick={limpiar}
                    className="inline-flex items-center gap-1 text-xs font-semibold text-[#697586] hover:text-[#2C4A5E]"
                >
                    <Eraser size={13} />
                    Limpiar
                </button>
            </div>
            <div className="overflow-hidden rounded-md border border-dashed border-[#B9C5BD] bg-[#FAFBFA]">
                <canvas
                    ref={canvasRef}
                    width={640}
                    height={160}
                    onPointerDown={comenzar}
                    onPointerMove={dibujar}
                    onPointerUp={() => {
                        dibujando.current = false;
                    }}
                    onPointerCancel={() => {
                        dibujando.current = false;
                    }}
                    className="block h-36 w-full touch-none cursor-crosshair"
                    aria-label={label}
                />
            </div>
            {value && (
                <p className="mt-1 text-xs text-[#267148]">
                    Firma dibujada lista para guardar.
                </p>
            )}
        </div>
    );
}
