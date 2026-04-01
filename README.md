# One-for-All Backend

API Laravel do **One-for-All**: um sistema pensado para crescer indefinidamente em funcionalidades, módulos e features podem ser adicionados ao longo do tempo sem limitar a evolução do projeto.

## Frontend

Interface do projeto (repositório separado):

**[one-for-all-frontend](https://github.com/C0nanT/one-for-all-frontend)**

## Como rodar

Em breve de forma simplificada...

## Hooks Git (pré-commit e pre-push)

O projeto usa **Husky** e **lint-staged** para rodar checagens antes de cada commit e push:

- **Pre-commit** (apenas nos arquivos em stage):
  - Sintaxe PHP (`php -l`)
  - Bloqueio de código de debug (`dd`, `dump`, `ray`, `var_dump`)
  - Formatação com Laravel Pint (ajusta e re-adiciona ao stage)

- **Pre-push**: executa a suíte de testes (`php artisan test --compact`).

Para pular os hooks em caso de emergência (não recomendado):

```bash
git commit --no-verify   # pula pre-commit
git push --no-verify     # pula pre-push
```
