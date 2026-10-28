import { createRoot } from 'react-dom/client';
import PasskeyVerify from '@/components/passkey-verify';

const element = document.querySelector<HTMLElement>('[data-biometric-login]');

if (element) {
    createRoot(element).render(
        <PasskeyVerify
            label="Ingresar con huella o rostro"
            loadingLabel="Esperando confirmación..."
            separator="O ingresá con tu contraseña"
            buttonClassName="border-[#b7d8c4] bg-[#e7f1eb] text-[#23604f] shadow-none hover:bg-[#d8ebdf] hover:text-[#173247]"
        />,
    );
}
