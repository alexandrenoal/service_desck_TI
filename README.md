# 🖥️ Service Desk T.I.

Sistema web moderno e eficiente desenvolvido para o gerenciamento, suporte e acompanhamento de chamados de TI em ambientes corporativos ou órgãos públicos.

---

## 🚀 Funcionalidades Principais

* **Dashboard Interativo**: Painel de controle com cartões de resumo dinâmicos para visualização rápida de chamados totais, novos, em andamento, abertos e concluídos.
* **Filtros Avançados e Compactos**: Sistema de filtragem por Mês, Ano, Secretaria e Assunto/Serviço perfeitamente alinhado em linha única.
* **Pesquisa Global Inteligente**: Barra de pesquisa integrada capaz de encontrar termos simultaneamente em títulos, descrições, protocolos, serviços, secretarias e setores.
* **Controle de Acesso por Perfil**: Gestão de permissões robusta separando acessos entre técnicos e administradores (`verificarPerfil`).
* **Priorização Automática**: Listagem ordenada por prioridade (urgente, alta, média, baixa) e data de abertura.

---

## 🛠️ Tecnologias Utilizadas

* **PHP** (com manipulação via PDO para segurança contra SQL Injection)
* **MySQL** (modelo relacional normalizado para chamados, serviços, secretarias e setores)
* **Bootstrap 5** (para um design responsivo, limpo e profissional)
* **JavaScript / HTML5 / CSS3**

---

## 📂 Estrutura do Banco de Tabela (Exemplo)

O sistema baseia-se em tabelas relacionais principais:
* `chamados`: Armazena os dados principais, status, prioridade, protocolo e chaves estrangeiras.
* `servicos`: Catálogo de tipos de atendimento disponíveis.
* `secretarias` & `setores`: Organização hierárquica dos solicitantes.
* `usuarios`: Gestão de acessos e perfis de técnicos/administradores.

---

## ⚙️ Como Executar o Projeto

1. Clone o repositório para o seu ambiente local ou servidor web (ex: XAMPP, WAMP ou Docker com PHP/MySQL):
   ```bash
   git clone [https://github.com/seu-usuario/nome-do-repositorio.git](https://github.com/seu-usuario/nome-do-repositorio.git)

   <img width="1311" height="591" alt="image" src="https://github.com/user-attachments/assets/a998048e-212b-4804-80d2-2658745e6e39" />
