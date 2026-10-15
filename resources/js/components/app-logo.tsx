export default function AppLogo() {
    return (
        <div className="ml-1 flex min-w-0 flex-1 items-center gap-2 text-left text-sm">
            <img
                src="/assets/img/SN.png"
                alt="San Nicolás"
                className="size-8 rounded-lg bg-white object-contain p-1 shadow-sm"
            />
            <span className="truncate leading-tight font-semibold tracking-wide">
                PRODENYA
            </span>
        </div>
    );
}
