# 🔐 SOLUÇÃO PARA ERRO "SENHA INCORRETA" - IPIL News

## ⚡ SOLUÇÃO RÁPIDA

### **Passo 1: Aceda ao Painel de Diagnóstico**
```
http://localhost/TLP-Projetos-corrigido/ipil-news/backend/painel-diagnostico.php
```

### **Passo 2: Clique em "Diagnosticar Problema"**
Este link vai abrir uma página que mostra:
- Todos os utilizadores na base de dados
- O hash exato da senha guardada
- Se a sua senha está correcta ou não

### **Passo 3: Se a Senha Estiver Incorreta**
Clique em "Resetar Senha" e defina uma nova senha conhecida.

### **Passo 4: Tente Login Novamente**
Use a nova senha para fazer login.

---

## 🔍 FERRAMENTAS DISPONÍVEIS

### **🎯 Painel Principal**
- **Painel de Diagnóstico:** [painel-diagnostico.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/painel-diagnostico.php)

### **🔐 Para Login**
1. **Diagnosticar Senha:** [diagnostico-senha.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/diagnostico-senha.php)
   - Ver hash guardado
   - Testar múltiplas variações de senha
   - Diagnosticar o problema

2. **Resetar Senha:** [resetar-senha.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/resetar-senha.php)
   - Mudar senha de qualquer utilizador
   - Testar login imediatamente depois

3. **Ver Logs:** [logs-login.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/logs-login.php)
   - Ver todas as tentativas
   - Diagnosticar patterns de erro

4. **Testar Auth:** [teste-auth.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/teste-auth.php)
   - Testar login com utilizador específico

### **📝 Para Registo**
1. **Gerenciar Códigos:** [gerenciar-codigos.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/gerenciar-codigos.php)
   - Criar novos códigos de validação
   - Ver códigos disponíveis

2. **Testar Registo:** [teste-registo.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/teste-registo.php)
   - Validar dados antes de submeter
   - Testar o fluxo completo

3. **Ver Logs:** [logs-registo.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/logs-registo.php)
   - Ver todas as tentativas de registo
   - Diagnosticar erros

---

## 📋 DADOS DE TESTE

### **Admin Padrão**
```
Email: admin@ipil.ao
Senha Padrão: Admin@IPIL2024
```

### **Códigos de Validação Disponíveis**
```
Alunos:
- 20240001
- 20240002

Professores:
- F-2024-001

Diretores:
- D-2024-001
```

---

## 🆘 TROUBLESHOOTING

### **"SENHA INCORRETA" no Login**

**Possíveis Causas:**
1. ✗ Você digitou a senha errada
2. ✗ A senha tem espaços no início/fim
3. ✗ CAPS LOCK está ligado
4. ✗ A conta foi resetada e você ainda usa a senha antiga

**Solução:**
1. Abra [diagnostico-senha.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/diagnostico-senha.php)
2. Teste a senha com o diagnóstico
3. Se for incorreta, abra [resetar-senha.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/resetar-senha.php)
4. Resetar para: `Teste@123`
5. Tente login novamente com esta senha

### **"CÓDIGO INVÁLIDO" no Registo**

**Possíveis Causas:**
1. ✗ O código não existe
2. ✗ O código já foi utilizado
3. ✗ O tipo de utilizador não corresponde

**Solução:**
1. Abra [gerenciar-codigos.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/gerenciar-codigos.php)
2. Copie um código de "Códigos Disponíveis"
3. Use esse código no registo

### **Preenchimento Automático Persiste**

**Solução:**
1. Limpe cache: `Ctrl+Shift+Delete`
2. Feche navegador completamente
3. Abra em modo anónimo/privado
4. Se persistir, execute no Console (F12):
   ```javascript
   localStorage.clear(); 
   sessionStorage.clear();
   ```

---

## 🔄 FLUXO COMPLETO DE TESTE

### **Teste 1: Login com Admin**
1. Aceda a [login.html](http://localhost/TLP-Projetos-corrigido/ipil-news/frontend/login.html)
2. Email: `admin@ipil.ao`
3. Senha: `Admin@IPIL2024`
4. Se receber erro, use [diagnostico-senha.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/diagnostico-senha.php)

### **Teste 2: Criar Nova Conta**
1. Aceda a [gerenciar-codigos.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/gerenciar-codigos.php)
2. Crie novo código: `TESTE-001` tipo "Aluno"
3. Aceda a [registar.html](http://localhost/TLP-Projetos-corrigido/ipil-news/frontend/registar.html)
4. Preencha com:
   - Nome: João Silva
   - Email: joao@ipil.ao
   - Tipo: Aluno
   - Código: TESTE-001
   - Senha: Teste@123
5. Clique "Criar Conta"

### **Teste 3: Login com Nova Conta**
1. Aceda a [login.html](http://localhost/TLP-Projetos-corrigido/ipil-news/frontend/login.html)
2. Email: `joao@ipil.ao`
3. Senha: `Teste@123`
4. Se receber erro, use [diagnostico-senha.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/diagnostico-senha.php)

---

## 📝 FICHEIROS CRIADOS/MODIFICADOS

```
✅ painel-diagnostico.php      - Novo: Painel principal de diagnóstico
✅ diagnostico-senha.php       - Novo: Diagnóstico completo de senha
✅ resetar-senha.php           - Novo: Resetar senha de utilizador
✅ frontend/login.html         - Modificado: Desativar autocomplete
✅ frontend/registar.html      - Modificado: Desativar autocomplete + validações
✅ backend/auth.php            - Modificado: Try-catch + logging
✅ backend/config.php          - Modificado: Tratamento de erros
✅ backend/logs-login.php      - Novo: Ver logs de login
✅ backend/logs-registo.php    - Novo: Ver logs de registo
✅ backend/gerenciar-codigos.php - Novo: Gerenciar códigos
✅ backend/teste-registo.php   - Novo: Testar registo
✅ backend/teste-auth.php      - Novo: Testar autenticação
✅ backend/debug-senha.php     - Novo: Debug de senha
```

---

## ✅ CHECKLIST FINAL

- [ ] Acedi a [painel-diagnostico.php](http://localhost/TLP-Projetos-corrigido/ipil-news/backend/painel-diagnostico.php)
- [ ] Usei o diagnóstico para testar a senha
- [ ] Se necessário, resetei a senha
- [ ] Consegui fazer login com sucesso
- [ ] Os campos de login não têm preenchimento automático
- [ ] Consegui criar uma nova conta
- [ ] Consegui fazer login com a nova conta

---

## 🎯 PRÓXIMOS PASSOS

1. Use o Painel de Diagnóstico para identificar o problema
2. Se a senha estiver errada, use a ferramenta de reset
3. Se tudo funcionar, os ficheiros de debug podem ser removidos em produção
4. Configure protecção de autenticação para as ferramentas de diagnóstico

---

**Data:** 15/05/2026
**Status:** ✅ Pronto para testar
