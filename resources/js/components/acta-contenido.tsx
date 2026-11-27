interface Seccion {
    titulo: string;
    campos: { label: string; valor: string }[];
    texto: boolean;
}

export interface ActaContenidoData {
    requiere_firma: boolean;
    firmas: { rol_label: string; nombre: string }[];
    secciones: Seccion[];
}

export default function ActaContenido({ acta }: { acta: ActaContenidoData }) {
    return (
        <div className="space-y-6 px-5 py-5 sm:px-6">
            {acta.requiere_firma && (
                <section>
                    <h3 className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                        Firmas
                    </h3>
                    <p className="mt-2 text-sm text-foreground">
                        {acta.firmas.length > 0
                            ? acta.firmas
                                  .map(
                                      (firma) =>
                                          `${firma.nombre} (${firma.rol_label})`,
                                  )
                                  .join(' · ')
                            : 'Sin firmar en el sistema.'}
                    </p>
                </section>
            )}

            {acta.secciones.length === 0 && (
                <p className="text-sm text-muted-foreground">
                    El acta se guardó sin datos completados.
                </p>
            )}

            {acta.secciones.map((seccion) => (
                <section key={seccion.titulo}>
                    <h3 className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                        {seccion.titulo}
                    </h3>
                    {seccion.texto ? (
                        <p className="mt-2 rounded-lg bg-surface-soft px-4 py-3 text-sm leading-6 whitespace-pre-wrap text-foreground">
                            {seccion.campos[0].valor}
                        </p>
                    ) : (
                        <dl className="mt-2 grid gap-x-8 sm:grid-cols-2">
                            {seccion.campos.map((campo) => (
                                <div
                                    key={campo.label}
                                    className="flex items-baseline justify-between gap-4 border-b border-border py-2 text-sm"
                                >
                                    <dt className="text-muted-foreground">
                                        {campo.label}
                                    </dt>
                                    <dd className="text-right font-medium break-words text-foreground">
                                        {campo.valor}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    )}
                </section>
            ))}
        </div>
    );
}
