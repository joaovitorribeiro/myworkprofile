# MyWorkProfile

Portfólio profissional de João Vitor Ribeiro Tim - Desenvolvedor Full Stack & Arquiteto Digital.

## 🚀 Tecnologias

- **Frontend**: React, TypeScript, Tailwind CSS, Inertia.js
- **Backend**: Laravel, PHP
- **Database**: MySQL
- **Ferramentas**: Vite, Docker, Git

## 📋 Funcionalidades

- Portfólio responsivo e moderno
- Seção sobre mim com foto
- Projetos destacados
- Formulário de contato
- Animações e efeitos visuais
- Design otimizado para mobile

## 🛠️ Instalação

### Desenvolvimento Local (sem Docker)

1. Clone o repositório:
```bash
git clone git@github.com:joaovitorribeiro/myworkprofile.git
cd myworkprofile
```

2. Instale as dependências:
```bash
composer install
npm install
```

3. Configure o ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

4. Execute o projeto:
```bash
npm run dev
```

### Produção (Coolify + Docker)

Para deploy em produção, o projeto utiliza Coolify com Docker. As variáveis de ambiente são configuradas diretamente no painel do Coolify:

- **APP_NAME**: MyWorkProfile
- **APP_ENV**: production
- **APP_KEY**: (gerada automaticamente)
- **APP_DEBUG**: false
- **APP_URL**: (URL do seu domínio)
- **DB_CONNECTION**: mysql
- **DB_HOST**: (host do banco fornecido pelo Coolify)
- **DB_PORT**: 3306
- **DB_DATABASE**: (nome do banco)
- **DB_USERNAME**: (usuário do banco)
- **DB_PASSWORD**: (senha do banco)

O Coolify gerencia automaticamente o build e deploy usando o Dockerfile incluído no projeto.

## 📱 Responsividade

O projeto foi desenvolvido com foco em responsividade, garantindo uma experiência otimizada em:
- Desktop
- Tablet
- Mobile

## 🎨 Design

- Tema azul moderno
- Partículas animadas no fundo (otimizadas para mobile)
- Gradientes e efeitos visuais
- Tipografia Orbitron para títulos

## 📞 Contato

João Vitor Ribeiro Tim
- GitHub: [@joaovitorribeiro](https://github.com/joaovitorribeiro)
- Email: joaovitor@solutionsites.com.br

---

Desenvolvido com ❤️ por João Vitor Ribeiro Tim