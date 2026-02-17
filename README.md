# sysMonDash v2.0 — Systems Monitor Dashboard

**sysMonDash** es un panel de monitorización web para entornos con un alto número de elementos, mostrando solo los eventos que requieren atención. Soporta **Zabbix** (2.2 - 7.2), **Nagios**, **Icinga** y **Check_MK** como backends.

> Fork mantenido por [CIBERSTORM](https://github.com/rubencastello1990/sysMonDash) con soporte para **Zabbix 7.2**, autenticación por API Token y mejoras de seguridad.

---

## Requisitos

- **PHP 7.4** o superior (con extensión `curl`)
- Servidor web: **Apache** o **Nginx**
- Backend de monitorización: Zabbix, Nagios, Icinga o Check_MK
- Para Zabbix: acceso a la API JSON-RPC (puerto 80/443)

---

## Instalación paso a paso

### 1. Instalar PHP y servidor web

**Debian/Ubuntu:**
```bash
sudo apt update
sudo apt install php php-curl php-xml apache2 libapache2-mod-php
```

**RHEL/Rocky/Alma:**
```bash
sudo dnf install php php-curl php-xml httpd
sudo systemctl enable --now httpd
```

### 2. Descargar sysMonDash

**Opción A — Desde release (recomendado):**
```bash
cd /var/www/html
sudo wget https://github.com/rubencastello1990/sysMonDash/archive/refs/tags/v2.0.0.tar.gz
sudo tar xzf v2.0.0.tar.gz
sudo mv sysMonDash-2.0.0 sysMonDash
```

**Opción B — Clonar repositorio:**
```bash
cd /var/www/html
sudo git clone https://github.com/rubencastello1990/sysMonDash.git
```

### 3. Dar permisos

```bash
sudo chown -R www-data:www-data /var/www/html/sysMonDash
sudo chmod 660 /var/www/html/sysMonDash/config.xml
```

> En RHEL/Rocky el usuario del servidor web es `apache` en lugar de `www-data`.

### 4. Acceder a la configuración

Abre en tu navegador:

```
http://tu-servidor/sysMonDash/config.php
```

Desde ahí puedes añadir backends, configurar filtros y establecer una contraseña de acceso a la configuración.

### 5. Ver el dashboard

```
http://tu-servidor/sysMonDash/
```

---

## Configuración de Zabbix

### Crear un API Token en Zabbix (recomendado para Zabbix 5.4+)

1. En Zabbix, ir a **Administración → Tokens de API → Crear token de API**
2. Seleccionar un usuario con permisos de lectura sobre los hosts que quieres monitorizar
3. Opcionalmente, establecer una fecha de expiración
4. Hacer clic en **Añadir** y **copiar el token generado** (solo se muestra una vez)

### Configurar en sysMonDash

| Campo | Valor |
|-------|-------|
| **Tipo de backend** | Zabbix |
| **Versión** | 7.2 (para Zabbix 7.x) |
| **URL del servidor** | `http://zabbix.ejemplo.com/api_jsonrpc.php` |
| **API Token** | Pegar el token generado |
| **Usuario/Contraseña** | Dejar vacíos si se usa API Token |

### Compatibilidad de versiones

| Versión Zabbix | Versión API en sysMonDash | Autenticación |
|----------------|---------------------------|---------------|
| 2.2.x | V225 | Usuario/Contraseña |
| 2.4.x — 6.x | V245 | Usuario/Contraseña |
| **7.0 — 7.2** | **V720** | **API Token (Bearer)** o Usuario/Contraseña |

---

## Actualizar sysMonDash

Si ya tienes sysMonDash instalado, el propio dashboard comprueba automáticamente si hay nuevas versiones. Cuando hay una actualización disponible, aparece un icono de descarga en el pie de página.

**Para actualizar manualmente:**

```bash
cd /var/www/html/sysMonDash

# Hacer backup de la configuración
sudo cp config.xml config.xml.bak

# Descargar la nueva versión
sudo git pull origin master
# O descargar el nuevo release y sobreescribir los archivos

# Restaurar permisos
sudo chown -R www-data:www-data .
sudo chmod 660 config.xml
```

> Tu archivo `config.xml` se mantiene entre actualizaciones. No se sobreescribe.

---

## Despliegue con Nginx

```nginx
server {
    listen 80;
    server_name monitor.ejemplo.com;

    root /var/www/html/sysMonDash;
    index index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Proteger config.xml de acceso directo
    location ~ /config\.xml$ {
        deny all;
        return 404;
    }
}
```

---

## Despliegue con Apache

Si sysMonDash está en `/var/www/html/sysMonDash`, ya funciona con la configuración por defecto de Apache. Para proteger `config.xml`, crea un `.htaccess`:

```apache
# /var/www/html/sysMonDash/.htaccess
<Files "config.xml">
    Require all denied
</Files>
<Files "config.xml.bak">
    Require all denied
</Files>
```

Para un VirtualHost dedicado:

```apache
<VirtualHost *:80>
    ServerName monitor.ejemplo.com
    DocumentRoot /var/www/html/sysMonDash

    <Directory /var/www/html/sysMonDash>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## Seguridad

- **Protege `config.xml`**: Contiene credenciales de backends. Usa las reglas de Nginx/Apache anteriores para bloquear el acceso directo desde el navegador.
- **Usa HTTPS**: Configura un certificado SSL (Let's Encrypt es gratuito) para cifrar las comunicaciones, especialmente si el dashboard es accesible desde internet.
- **Contraseña de configuración**: Establece una contraseña en `config.php` para evitar cambios no autorizados.
- **API Token con permisos mínimos**: Crea un usuario de solo lectura en Zabbix y genera el API Token desde ese usuario.

---

## Funcionalidades

- Soporte para múltiples backends simultáneos (Zabbix, Nagios, Icinga, Check_MK)
- **Soporte Zabbix 7.2** con cliente API V720
- **Autenticación por API Token** (Bearer) para Zabbix 5.4+
- **Columnas configurables**: Proxy y Tags de host en la vista principal
- Filtrado de hosts y servicios
- Selección de elementos críticos (siempre visibles)
- Detección y visualización de paradas programadas (maintenance)
- Conexión a backends remotos mediante API JSON (modo proxy)
- Comprobación automática de actualizaciones
- Alarma sonora para nuevos eventos
- Soporte multiidioma (Español, Inglés, Alemán)
- Autenticación de configuración con contraseña y sesión

---

## Créditos

Fork de [sysMonDash](https://github.com/nuxsmin/sysMonDash) por [nuxsmin](http://cygnux.org).
Mantenido por [CIBERSTORM](https://github.com/rubencastello1990/sysMonDash) con soporte para Zabbix 7.2.

Licencia: [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html)
