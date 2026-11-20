import { createRoot } from 'react-dom/client';
import PasskeyVerify from '@/components/passkey-verify';

const element = document.querySelector<HTMLElement>('[data-biometric-login]');

if (element) {
    createRoot(element).render(
        <PasskeyVerify
            label="Ingresar con huella o rostro"
            loadingLabel="Esperando confirmación..."
            separator="O ingresá con tu contraseña"
            buttonClassName="border-[var(--line-strong)] bg-[var(--accent-soft)] text-[var(--accent-dark)] shadow-none hover:text-[var(--ink)] hover:brightness-95"
        />,
    );
}
