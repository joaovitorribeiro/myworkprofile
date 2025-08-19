export default function Footer() {
    return (
        <footer className="mt-10 border-t border-white/10 bg-black/20 py-6 text-center text-white/80">
            <p>
                © {new Date().getFullYear()} João Vitor Ribeiro Tim. Todos os
                direitos reservados.
            </p>
        </footer>
    );
}
