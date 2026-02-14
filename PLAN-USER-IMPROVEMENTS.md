# Plano de Melhorias: Sistema de Usuários FFC

## Decisões de Arquitetura (Aprovadas)

1. **User Delete** → Anonimizar (user_id = NULL, dados sensíveis removidos)
2. **User Profiles** → Tabela dedicada `wp_ffc_user_profiles`
3. **Capabilities** → Implementar checks reais + padronizar nos constants
4. **LGPD/Privacy** → Implementar exporters e erasers agora

---

## Sprint 1: Fundação — Capabilities & Correções Estruturais
> **Escopo:** Corrigir inconsistências existentes sem alterar o schema do banco
> **Risco:** Baixo (refatoração interna, sem breaking changes)

### 1.1 Padronizar capabilities nos constants do UserManager
**Arquivo:** `includes/user-dashboard/class-ffc-user-manager.php`
- Adicionar `AUDIENCE_CAPABILITIES` array com `ffc_view_audience_bookings`
- Adicionar `ADMIN_CAPABILITIES` array com `ffc_scheduling_bypass`
- Adicionar `ALL_FFC_CAPABILITIES` que consolida todos os arrays
- Adicionar método `grant_audience_capabilities()`
- Atualizar `get_user_ffc_capabilities()` para incluir audience + admin caps
- Atualizar `set_user_capability()` para validar contra ALL_FFC_CAPABILITIES
- Adicionar `CONTEXT_AUDIENCE = 'audience'` constant

### 1.2 Implementar checks das capabilities não verificadas
**Arquivos afetados:**
- `includes/api/class-ffc-user-data-rest-controller.php`
  - GET /user/certificates: Verificar `download_own_certificates` ao gerar `pdf_url`/`magic_link` (retornar URL vazia se capability ausente)
  - GET /user/certificates: Verificar `view_certificate_history` para filtrar submissões (se desabilitado, mostrar apenas a mais recente por form_id)
- `includes/frontend/class-ffc-verification-handler.php`
  - Verificar `download_own_certificates` no acesso via dashboard (não afeta magic link público)

### 1.3 Corrigir CSV Importer — capabilities incompletas
**Arquivo:** `includes/audience/class-ffc-audience-csv-importer.php`
- Linhas 382-385: Substituir `add_cap` manual por `UserManager::grant_certificate_capabilities()`

### 1.4 Corrigir uninstall.php — capabilities faltantes
**Arquivo:** `uninstall.php`
- Linhas 128-135: Adicionar:
  - `ffc_scheduling_bypass`
  - `ffc_view_audience_bookings`
  - `ffc_reregistration`
  - `ffc_certificate_update`
- Idealmente: referenciar array centralizado (mas uninstall.php precisa ser standalone)

### 1.5 AdminUserCapabilities — usar constants centralizados
**Arquivo:** `includes/admin/class-ffc-admin-user-capabilities.php`
- `save_capability_fields()` linhas 258-273: Substituir lista hardcoded por referência ao UserManager::ALL_FFC_CAPABILITIES
- Garantir que novas capabilities futuras se propaguem automaticamente

---

## Sprint 2: Tabela ffc_user_profiles & Hook de Deleção
> **Escopo:** Criar infraestrutura nova de perfil + tratamento de deleção de usuário
> **Risco:** Médio (nova tabela, migração de dados, novo hook)

### 2.1 Criar tabela `wp_ffc_user_profiles`
**Modificar:** `includes/class-ffc-activator.php`
- Adicionar método `create_user_profiles_table()`

**Schema:**
```sql
CREATE TABLE wp_ffc_user_profiles (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    display_name VARCHAR(250) DEFAULT '',
    phone VARCHAR(50) DEFAULT '',
    department VARCHAR(250) DEFAULT '',
    organization VARCHAR(250) DEFAULT '',
    notes TEXT DEFAULT '',
    preferences JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY idx_user_id (user_id)
);
```

### 2.2 Migration: Popular profiles com dados existentes
**Novo arquivo:** `includes/migrations/class-ffc-migration-user-profiles.php`
- Para cada `ffc_user`:
  - Copiar `display_name` de wp_users
  - Copiar `ffc_registration_date` de wp_usermeta → `created_at`
  - Extrair nomes de submissions (campo `nome_completo` etc.)
- Batch processing com batch_size configurável
- Suporte a dry_run para preview

### 2.3 Refatorar UserManager para gravar em ffc_user_profiles
**Arquivo:** `includes/user-dashboard/class-ffc-user-manager.php`
- `create_ffc_user()`: Criar registro em ffc_user_profiles após wp_create_user
- `sync_user_metadata()`: Gravar em ffc_user_profiles + manter wp_users.display_name sincronizado
- Adicionar métodos: `get_profile()`, `update_profile()`

### 2.4 Atualizar REST API para ler de ffc_user_profiles
**Arquivo:** `includes/api/class-ffc-user-data-rest-controller.php`
- `get_user_profile()`: Fonte primária = ffc_user_profiles, fallback = wp_users
- Incluir novos campos (phone, department, organization) na resposta

### 2.5 Implementar hook `deleted_user` — Anonimização
**Novo arquivo:** `includes/user-dashboard/class-ffc-user-cleanup.php`

```php
class UserCleanup {
    public static function init(): void {
        add_action('deleted_user', [__CLASS__, 'anonymize_user_data']);
    }

    public static function anonymize_user_data(int $user_id): void {
        // ffc_submissions: SET user_id = NULL
        // ffc_self_scheduling_appointments: SET user_id = NULL
        // ffc_audience_members: DELETE
        // ffc_audience_booking_users: DELETE
        // ffc_audience_schedule_permissions: DELETE
        // ffc_user_profiles: DELETE
        // ffc_activity_log: SET user_id = NULL (manter audit trail)
        // Log: "User data anonymized"
    }
}
```

### 2.6 Registrar no Loader
**Arquivo:** `includes/class-ffc-loader.php`
- Adicionar `UserCleanup::init()` no boot do plugin

### 2.7 Atualizar uninstall.php
**Arquivo:** `uninstall.php`
- Adicionar DROP TABLE `wp_ffc_user_profiles`

---

## Sprint 3: LGPD/Privacy — Exporters & Erasers
> **Escopo:** Integração com WordPress Privacy Tools (Tools > Export/Erase Personal Data)
> **Risco:** Médio (decrypt em batch, volume de dados)
> **Depende de:** Sprint 2 (profiles table + cleanup logic)

### 3.1 Criar Privacy Handler
**Novo arquivo:** `includes/privacy/class-ffc-privacy-handler.php`

```php
class PrivacyHandler {
    public static function init(): void {
        add_filter('wp_privacy_personal_data_exporters', [__CLASS__, 'register_exporters']);
        add_filter('wp_privacy_personal_data_erasers', [__CLASS__, 'register_erasers']);
    }
}
```

### 3.2 Implementar Exporter
**Grupos de dados exportados:**

| Grupo | Campos |
|-------|--------|
| FFC Profile | display_name, email, phone, department, organization, member_since |
| FFC Certificates | form_title, submission_date, auth_code, consent_given |
| FFC Appointments | calendar_title, date, time, status, name, email, phone, notes |
| FFC Audience Groups | audience_name, joined_date |
| FFC Audience Bookings | environment, date, time, description, status |

**Lógica:**
- Localizar user_id pelo email
- Descriptografar dados para exportação
- Retornar formato `$export_items[]` do WordPress
- Paginação via `$page` parameter (50 items por batch)

### 3.3 Implementar Eraser
**Ações por tabela:**

| Tabela | Ação |
|--------|------|
| ffc_user_profiles | DELETE registro |
| ffc_submissions | SET user_id = NULL, limpar email_encrypted, cpf_rf_encrypted |
| ffc_self_scheduling_appointments | SET user_id = NULL, limpar email_encrypted, name, phone |
| ffc_audience_members | DELETE registros |
| ffc_audience_booking_users | DELETE registros |
| ffc_audience_schedule_permissions | DELETE registros |
| ffc_activity_log | SET user_id = NULL |

**Importante:** Manter `auth_code`, `magic_token` e `cpf_rf_hash` nas submissions para que certificados já emitidos continuem verificáveis via link público.

### 3.4 Registrar no Loader
**Arquivo:** `includes/class-ffc-loader.php`
- Inicializar `PrivacyHandler::init()`

---

## Sprint 4: Dashboard Editável & UX
> **Escopo:** Permitir que o usuário edite seu próprio perfil + melhorias visuais
> **Risco:** Baixo-Médio (novos endpoints, formulário frontend)
> **Depende de:** Sprint 2 (profiles table)

### 4.1 Endpoint REST para atualizar perfil
**Arquivo:** `includes/api/class-ffc-user-data-rest-controller.php`
- Novo: `PUT /user/profile`
- Campos editáveis: `display_name`, `phone`, `department`, `organization`
- Permission: `is_user_logged_in`
- Sanitização: `sanitize_text_field()` para todos os campos
- Atualizar ffc_user_profiles + wp_users.display_name

### 4.2 Formulário de edição no dashboard
**Arquivo:** `assets/js/ffc-user-dashboard.js`
- Tab "Profile": Botão "Editar Perfil" → modo inline edit
- Campos: display_name (text), phone (tel), department (text), organization (text)
- Validação frontend + chamada REST
- Feedback visual (sucesso/erro)

### 4.3 Melhorar exibição do perfil
- Mostrar todos os campos de ffc_user_profiles
- CPFs/RFs e emails como tags read-only
- Grupos de audiência com badges coloridos
- Seção "Seus Acessos": listar capabilities ativas como ícones (certificados, agendamentos, etc.)

---

## Sprint 5: Robustez & Performance
> **Escopo:** Otimizações e centralização de lógica
> **Risco:** Baixo (otimizações internas)
> **Depende de:** Sprint 2-3

### 5.1 Cache de contagem na lista de usuários admin
**Arquivo:** `includes/admin/class-ffc-admin-user-columns.php`
- Batch query: `SELECT user_id, COUNT(*) FROM ffc_submissions GROUP BY user_id` (single query)
- Cache via transient (invalidar no save/delete de submissions/appointments)

### 5.2 Corrigir view-as + capability no REST
**Arquivo:** `includes/api/class-ffc-user-data-rest-controller.php`
- Quando admin usa view-as: verificar capabilities do usuário-alvo
- Admin vê exatamente o que o usuário veria

### 5.3 Criar UserService centralizado
**Novo arquivo:** `includes/services/class-ffc-user-service.php`

```php
class UserService {
    public static function get_full_profile(int $user_id): array { }
    public static function export_personal_data(int $user_id): array { }
    public static function anonymize_personal_data(int $user_id): array { }
    public static function get_user_statistics(int $user_id): array { }
}
```
- Usado por: REST controller, PrivacyHandler, UserCleanup
- Single point of truth para toda lógica de usuário

---

## Ordem de Dependências

```
Sprint 1 (Capabilities)
    │
    ▼
Sprint 2 (Profiles + Delete Hook)
    │
    ├──────────────────┐
    ▼                  ▼
Sprint 3 (LGPD)    Sprint 4 (Dashboard)
    │                  │
    └──────┬───────────┘
           ▼
    Sprint 5 (Robustez)
```

> Sprints 3 e 4 podem rodar em paralelo.

---

## Resumo de Impacto

| Sprint | Modificados | Novos | Complexidade |
|--------|-------------|-------|-------------|
| 1 | 5 arquivos | 0 | Baixa |
| 2 | 4 arquivos | 2 novos | Média |
| 3 | 1 arquivo | 1 novo | Média |
| 4 | 2 arquivos | 0-1 | Média |
| 5 | 3 arquivos | 1 novo | Baixa |
