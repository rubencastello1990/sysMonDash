# sysMonDash — Fork Progress (CIBERSTORM)

## Estado del fork

| Campo | Valor |
|-------|-------|
| **Origen** | [irontec/sysMonDash](https://github.com/irontec/sysMonDash) |
| **Fork** | [rubencastello1990/sysMonDash](https://github.com/rubencastello1990/sysMonDash) |
| **Rama activa** | `master` |
| **Versión base** | 1.1 (abril 2017) |
| **Versión fork** | 2.0.0 |
| **Última actualización** | 2026-02-18 |

---

## Completado

### Backend — Compatibilidad Zabbix 7.2

| Cambio | Detalle |
|--------|---------|
| Cliente V720 nuevo | `inc/Exts/Zabbix/V720/` — cliente ligero ~200 líneas |
| Auth Bearer token | `Authorization: Bearer {token}` en header HTTP |
| API Token en UI | Campo en `config.php` para introducir API token |
| Fix `user.login` | Campo `username` en lugar de `user` (Zabbix 7.x) |
| `problem.get` | Uso de la API moderna para problemas activos |
| PHP mínimo 7.4 | Check actualizado desde PHP 5.3 |
| Columnas Proxy/Tag | Columnas configurables en el dashboard |
| `hostGet` / `proxyGet` | Nuevos métodos en el cliente V720 |
| Auth config mejorada | Contraseña + sesión para la página de configuración |
| `checkConfig.php` | Soporte API token en test de conexión |

### Backend — `checkConfig.php` compatibilidad API token (commit ee8e9dd)

### Versioning y release system (commit 77162df)

| Cambio | Detalle |
|--------|---------|
| Versión `v2.0.0` | `Util.class.php` actualizado |
| `appupdates` URL | Apunta a `rubencastello1990/sysMonDash/releases/latest` |
| README reescrito | Documentación completa de instalación y config |
| CHANGELOG | Entrada v2.0.0 documentada |

### UI — Dark/Light Mode Redesign (commit 68050c1)

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

### Archivos modificados / añadidos (UI commit)

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

## Pendiente / Por verificar

- [ ] Crear release `v2.0.0` en GitHub (`gh release create v2.0.0`) para activar el sistema de notificación de actualizaciones del footer
- [ ] Probar flujo completo con Zabbix 7.2 real (conexión, auth API token, visualización de alertas)
- [ ] Verificar columnas Proxy/Tag con instancia Zabbix real

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
- Zabbix 7.2 accesible por red
- API Token de Zabbix generado en: Zabbix → User settings → API tokens
