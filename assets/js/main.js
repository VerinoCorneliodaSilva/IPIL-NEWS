/**
 * main.js
 * Funções partilhadas por todas as páginas do IPIL News Portal
 * Carregado antes dos scripts específicos de cada página
 */

// ============================================================
// VERIFICAR AUTENTICAÇÃO
// Redireciona para login se não houver sessão activa
// ============================================================
function verificarAutenticacao() {
    const sessao = sessionStorage.getItem('ipil_user');

    if (!sessao) {
        // Sem sessão → forçar login
        window.location.href = 'login.html';
        // Retornar objecto vazio para evitar erros antes do redirect
        return { nome: '', role: '' };
    }

    try {
        return JSON.parse(sessao);
    } catch (e) {
        sessionStorage.removeItem('ipil_user');
        window.location.href = 'login.html';
        return { nome: '', role: '' };
    }
}

// ============================================================
// LOGOUT
// Chama o backend e limpa a sessão local
// ============================================================
async function logout() {
    try {
        const dados = new FormData();
        dados.append('acao', 'logout');
        await fetch('../backend/auth.php', { method: 'POST', body: dados });
    } catch (e) {
        // Mesmo com erro de rede, limpar sessão local
    } finally {
        sessionStorage.removeItem('ipil_user');
        window.location.href = 'login.html';
    }
}

// ============================================================
// FORMATAR DATA em português
// ============================================================
function formatarData(isoString) {
    if (!isoString) return '—';
    return new Date(isoString).toLocaleDateString('pt-PT', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

// ============================================================
// ESCAPAR HTML (protecção XSS no frontend)
// Usar sempre ao inserir dados do servidor no DOM via innerHTML
// ============================================================
function escaparHTML(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(str ?? '')));
    return div.innerHTML;
}
