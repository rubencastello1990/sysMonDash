# sysMonDash — Fork Progress (CIBERSTORM)

## Estado del fork

| Campo | Valor |
|-------|-------|
| **Origen** | [irontec/sysMonDash](https://github.com/irontec/sysMonDash) |
| **Fork** | [rubencastello1990/sysMonDash](https://github.com/rubencastello1990/sysMonDash) |
| **Rama activa** | `master` |
| **Versión base** | 1.1 (abril 2017) |
| **Versión fork** | 2.0 (en desarrollo) |
| **Última actualización** | 2026-02-18 |

---

## Completado

### UI — Dark/Light Mode Redesign

- **Toggle fix:** el header en light mode ahora es blanco (`#ffffff`) vs dark (`#161b2e`), el cambio de tema es visualmente obvio en toda la ventana.
- **Logo dinámico:** `logo-15` (blanco, para fondo oscuro) en dark mode, `logo-16` (oscuro, para fondo blanco) en light mode. Intercambio CSS puro, compatible con anti-FOUC.
- **Tabla redesign** (`#tblBoard`) — CSS puro, sin cambios en PHP:
  - `border-radius` + `box-shadow` efecto card
  - Gradientes de fondo por severidad de alerta
  - `thead` sticky con `backdrop-filter: blur`
  - Primera celda estilo badge (uppercase, 0.7em, bold)
  - Animación `smd-pulse` para eventos `.new`
  - `#total` como barra de estado cierre de la card
  - `.statusinfo` con `ellipsis overflow` para mensajes largos
- **Botón toggle:** círculo 38px, estilizado dark-aware
- **config.php UI:** variables CSS (`--text`, `--border`, `--error-bg`, etc.) aplicadas a toda la página de configuración

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `index.php` | Logo dinámico (`#logo-dark` / `#logo-light`) |
| `config.php` | Logo dinámico (`#logo-dark` / `#logo-light`) |
| `css/styles.css` | Toggle fix + logo CSS + tabla redesign completo |
| `css/styles.min.css` | Versión minificada del anterior |
| `css/config.css` | Variables CSS dark/light para página de config |
| `css/config.min.css` | Versión minificada del anterior |
| `imgs/logo-ciberstom-15-trans_cropped.png` | Logo dark mode (blanco) |
| `imgs/logo-ciberstom-16-trans_cropped.png` | Logo light mode (oscuro) |

---

## Pendiente

### Backend — Compatibilidad Zabbix 7.2

El backend actual usa APIs de Zabbix obsoletas (incompatibles con Zabbix 7.x):

| Problema | Detalle |
|---------|---------|
| `user.login` usa campo `user` | Zabbix 7.x requiere `username` |
| Auth token en campo JSON-RPC `auth` | Zabbix 7.x requiere `Authorization: Bearer {token}` en header HTTP |
| Usa `trigger.get` para problemas activos | Zabbix 7.x prefiere `problem.get` |
| Librería API máxima V2.4.5 | Necesita nuevo cliente V7.2 (~200 líneas) |

**Plan de implementación:** documentado en observación claude-mem #413 (Feb 16, 2026).

Archivos a crear/modificar:
- `inc/Exts/Zabbix/V720/ZabbixApi.class.php` — nuevo cliente ligero
- `inc/Exts/Zabbix/V720/ZabbixApiAbstract.class.php` — Bearer token auth
- `inc/Exts/Zabbix/ZabbixApiLoader.class.php` — añadir versión 720
- `inc/SMD/Backend/Zabbix.class.php` — usar `username`, añadir soporte API token
- `inc/SMD/Core/ConfigBackendZabbix.class.php` — añadir campo `apiToken`
- `inc/SMD/Core/Init.class.php` — actualizar check PHP de 5.3 a 7.4+
- `config.php` — campo de input para API token en UI

---

## Desarrollo local

### Arrancar servidor PHP

```bash
php -S localhost:8888 -t /home/ciberstorm/sysMonDash
```

### Acceder

| Página | URL |
|--------|-----|
| Dashboard | http://localhost:8888/ |
| Configuración | http://localhost:8888/config.php |

### Requisitos

- PHP 7.4+
- Directorio `data/` con permisos de escritura (para `config.xml`)
- Zabbix accesible por red desde el servidor PHP
